<?php

declare(strict_types=1);

namespace ScipPhp\Composer;

use Composer\ClassMapGenerator\PhpFileParser;
use JetBrains\PHPStormStub\PhpStormStubsMap;
use ReflectionClass;
use ReflectionFunction;
use RuntimeException;
use ScipPhp\File\Reader;

use function array_keys;
use function array_key_exists;
use function class_exists;
use function enum_exists;
use function explode;
use function function_exists;
use function get_defined_constants;
use function get_included_files;
use function getcwd;
use function implode;
use function interface_exists;
use function is_array;
use function is_dir;
use function is_file;
use function is_string;
use function json_decode;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function realpath;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function trait_exists;
use function trim;

use const DIRECTORY_SEPARATOR;
use const JSON_THROW_ON_ERROR;
use const PHP_VERSION;

final class Composer
{
    /** @var non-empty-string */
    private readonly string $pkgName;

    /** @var non-empty-string */
    private readonly string $pkgVersion;

    /** @var non-empty-string */
    private readonly string $vendorDir;

    /** @var non-empty-string */
    private readonly string $scipPhpVendorDir;

    private readonly ClassMap $classMap;

    /** @var non-empty-array<non-empty-string, array{name: non-empty-string, version: non-empty-string}> */
    private array $pkgsByPaths;

    /** @var array<non-empty-string, scalar> */
    private readonly array $userConsts;

    /**
     * @param  non-empty-string  $projectRoot
     * @param  list<non-empty-string>  $projectFiles
     */
    public function __construct(
        private readonly string $projectRoot,
        array $projectFiles,
    ) {
        $scipPhpVendorDir = self::join(__DIR__, '..', '..', 'vendor');
        if (!is_dir($scipPhpVendorDir)) {
            $cwd = getcwd();
            if ($cwd === false) {
                throw new RuntimeException("Cannot get the current working directory.");
            }
            $scipPhpVendorDir = self::join($cwd, 'vendor');
            if (!is_dir($scipPhpVendorDir)) {
                throw new RuntimeException("Invalid scip-php vendor directory: {$scipPhpVendorDir}.");
            }
        }
        $this->scipPhpVendorDir = self::resolvePath($scipPhpVendorDir);

        $json = $this->parseJson('composer.json');
        $vendorDir = 'vendor';
        if (
            is_array($json['config'] ?? null)
            && is_string($json['config']['vendor-dir'] ?? null)
        ) {
            $dir = trim($json['config']['vendor-dir'], '/');
            if ($dir !== '') {
                $vendorDir = $dir;
            }
        }
        $this->vendorDir = self::join($projectRoot, $vendorDir);

        $this->classMap = new ClassMap($this->vendorDir);

        $additionalClasses = [];
        foreach ($projectFiles as $f) {
            $classes = PhpFileParser::findClasses($f);
            foreach ($classes as $c) {
                if ($this->classMap->findFile($c) === null) {
                    $additionalClasses[$c] = $f;
                }
            }
        }
        if (count($additionalClasses) > 0) {
            $this->classMap->addClassMap($additionalClasses);
        }

        $installed = require self::join($this->vendorDir, 'composer', 'installed.php');

        if (!is_array($installed) || !is_array($installed['root'])) {
            throw new RuntimeException("Cannot get root element from installed.php.");
        }

        $pkgName = $installed['root']['name'];
        if (!is_string($pkgName) || $pkgName === '') {
            throw new RuntimeException("Cannot get package name.");
        }
        $this->pkgName = $pkgName;

        $pkgVersion = $installed['root']['reference'] ?? $installed['root']['version'];
        if (!is_string($pkgVersion) || $pkgVersion === '') {
            throw new RuntimeException("Cannot get package version.");
        }
        $this->pkgVersion = $pkgVersion;

        $pkgsByPaths = [];
        if (is_array($installed['versions'])) {
            foreach ($installed['versions'] as $name => $info) {
                if (!is_string($name) || $name === '') {
                    continue;
                }
                if (!is_array($info) || !is_string($info['install_path'] ?? null) || $info['install_path'] === '') {
                    continue;
                }
                $path = self::resolvePath($info['install_path']);
                if ($name !== $this->pkgName && is_string($info['reference']) && $info['reference'] !== '') {
                    $pkgsByPaths[$path] = ['name' => $name, 'version' => $info['reference']];
                    // Also add the vendor directory path for files found via ClassMap
                    $vendorPath = self::join($this->vendorDir, ...explode('/', $name));
                    if (is_dir($vendorPath) && !isset($pkgsByPaths[$vendorPath])) {
                        $pkgsByPaths[$vendorPath] = ['name' => $name, 'version' => $info['reference']];
                    }
                }
            }
        }

        $composerPath = self::join($this->vendorDir, 'composer');
        $pkgsByPaths[$composerPath] = ['name' => 'composer', 'version' => 'dev'];
        $this->pkgsByPaths = $pkgsByPaths;

        if (is_file(self::join($this->projectRoot, 'composer.lock'))) {
            $lock = $this->parseJson('composer.lock');
            if (is_array($lock['packages'] ?? null)) {
                foreach ($lock['packages'] as $pkg) {
                    if (
                        !is_array($pkg)
                        || !is_array($pkg['autoload'] ?? null)
                        || !is_array($pkg['autoload']['files'] ?? null)
                        || !is_string($pkg['name'] ?? null)
                        || $pkg['name'] === ''
                    ) {
                        continue;
                    }
                    foreach ($pkg['autoload']['files'] as $f) {
                        if (!is_string($f) || $f === '') {
                            continue;
                        }
                        $f = self::join($this->vendorDir, $pkg['name'], $f);
                        $classes = PhpFileParser::findClasses($f);
                        foreach ($classes as $c) {
                            if ($this->classMap->findFile($c) === null) {
                                $additionalClasses[$c] = $f;
                            }
                        }
                    }
                }
            }
        }
        if (count($additionalClasses) > 0) {
            $this->classMap->addClassMap($additionalClasses);
        }

        // Include files autoload entries so project functions/constants
        // are discoverable via function_exists() and get_defined_constants().
        // This replaces the old ClassLoader registration without side effects.
        $composerDir = $this->vendorDir . '/composer';
        $autoloadFiles = $composerDir . '/autoload_files.php';
        if (is_file($autoloadFiles)) {
            $files = require $autoloadFiles;
            if (is_array($files)) {
                foreach ($files as $f) {
                    if (is_string($f) && $f !== '' && is_file($f)) {
                        include_once $f;
                    }
                }
            }
        }

        $this->userConsts = get_defined_constants(categorize: true)['user'] ?? []; // @phpstan-ignore-line
    }

    /**
     * @param  non-empty-string  $elem
     * @param  non-empty-string  $elems
     * @return non-empty-string
     */
    private static function join(string $elem, string ...$elems): string
    {
        return implode(DIRECTORY_SEPARATOR, [$elem, ...$elems]);
    }

    /**
     * @param  non-empty-string  $path
     * @return non-empty-string
     */
    private static function resolvePath(string $path): string
    {
        if (str_starts_with($path, 'phar://')) {
            if (!is_dir($path)) {
                throw new RuntimeException("Invalid path: {$path}.");
            }
            return $path;
        }
        $resolved = realpath($path);
        if ($resolved === false) {
            throw new RuntimeException("Cannot resolve path: {$path}.");
        }
        return $resolved;
    }

    /** @param  non-empty-string  $filename */
    private function parseJson(string $filename): array
    {
        $content = Reader::read(self::join($this->projectRoot, $filename));
        $json = json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
        if (!is_array($json)) {
            throw new RuntimeException("Cannot parse {$filename}.");
        }
        return $json;
    }

    /** @param  non-empty-string  $ident */
    public function isDependency(string $ident): bool
    {
        return !$this->isFromProject($ident);
    }

    /** @param  non-empty-string  $c */
    public function isConst(string $c): bool
    {
        return isset(PhpStormStubsMap::CONSTANTS[$c]) || isset($this->userConsts[$c]);
    }

    /** @param  non-empty-string  $c */
    public function isClassLike(string $c): bool
    {
        return isset(PhpStormStubsMap::CLASSES[$c])
            || str_contains($c, 'anon-class-')
            || (str_starts_with($c, 'Composer\\Autoload\\') && class_exists($c))
            || class_exists($c, false)
            || interface_exists($c, false)
            || trait_exists($c, false)
            || enum_exists($c, false)
            || (
                $this->classMap->findFile($c) !== null && (
                    !function_exists($c)
                    || class_exists($c) || interface_exists($c) || trait_exists($c) || enum_exists($c)
                )
            );
    }

    /** @param  non-empty-string  $f */
    public function isFunc(string $f): bool
    {
        return function_exists($f) || isset(PhpStormStubsMap::FUNCTIONS[$f]) || str_contains($f, 'anon-func-');
    }

    /**
     * @param  non-empty-string  $ident
     * @return ?non-empty-string
     */
    public function findFile(string $ident): ?string
    {
        $stub = $this->stub($ident);
        if ($stub !== null) {
            $f = self::join($this->scipPhpVendorDir, 'jetbrains', 'phpstorm-stubs', $stub);
            if (str_starts_with($f, 'phar://')) {
                if (!is_file($f)) {
                    throw new RuntimeException("Invalid path to stub file: {$stub}.");
                }
                return $f;
            }
            $f = realpath($f);
            if ($f === false) {
                throw new RuntimeException("Invalid path to stub file: {$stub}.");
            }
            return $f;
        }

        $f = $this->classMap->findFile($ident);
        if ($f !== null) {
            return $f;
        }

        if (function_exists($ident)) {
            $func = new ReflectionFunction($ident);
            $f = $func->getFileName();
            if ($f !== false && $f !== '') {
                if (!str_contains($f, $this->scipPhpVendorDir)) {
                    return $f;
                }
                $vendorFile = str_replace($this->scipPhpVendorDir, $this->vendorDir, $f);
                if (is_file($vendorFile)) {
                    return $vendorFile;
                }
                $f = str_replace($this->scipPhpVendorDir . DIRECTORY_SEPARATOR, '', $f);
                $f = preg_replace('/^\w+\/\w+\//', '', $f, limit: 1);
                if ($f === null || $f === '') {
                    throw new RuntimeException("Invalid path to function file: {$func->getFileName()}.");
                }
                return self::join($this->projectRoot, $f);
            }
        }

        if (class_exists($ident, false) || interface_exists($ident, false) || trait_exists($ident, false) || enum_exists($ident, false)) {
            $class = new ReflectionClass($ident);
            $f = $class->getFileName();
            if ($f !== false && $f !== '') {
                if (!str_contains($f, $this->scipPhpVendorDir)) {
                    return $f;
                }
                $vendorFile = str_replace($this->scipPhpVendorDir, $this->vendorDir, $f);
                if (is_file($vendorFile)) {
                    return $vendorFile;
                }
                $f = str_replace($this->scipPhpVendorDir . DIRECTORY_SEPARATOR, '', $f);
                $f = preg_replace('/^\w+\/\w+\//', '', $f, limit: 1);
                if ($f === null || $f === '') {
                    throw new RuntimeException("Invalid path to class file: {$class->getFileName()}.");
                }
                return self::join($this->projectRoot, $f);
            }
        }

        if (str_starts_with($ident, 'Composer\\Autoload\\') && class_exists($ident)) {
            $class = new ReflectionClass($ident);
            $f = $class->getFileName();
            if ($f !== false && $f !== '') {
                return str_replace($this->scipPhpVendorDir, $this->vendorDir, $f);
            }
        }
        return $this->findConstFile($ident);
    }

    /**
     * @param  non-empty-string  $ident
     * @return ?array{name: non-empty-string, version: non-empty-string}
     */
    public function pkg(string $ident): ?array
    {
        if ($this->isStub($ident)) {
            return ['name' => 'php', 'version' => PHP_VERSION];
        }
        if ($this->isFromProject($ident)) {
            return ['name' => $this->pkgName, 'version' => $this->pkgVersion];
        }
        $f = $this->findFile($ident);
        if ($f === null) {
            return null;
        }
        foreach ($this->pkgsByPaths as $path => $info) {
            if (str_starts_with($f, $path)) {
                return $info;
            }
        }
        throw new RuntimeException("Cannot find package for identifier {$ident} in file {$f}.");
    }

    /** @param  non-empty-string  $ident */
    private function isFromProject(string $ident): bool
    {
        if (str_contains($ident, 'anon-class-') || str_contains($ident, 'anon-func-')) {
            return true;
        }
        if ($this->isStub($ident)) {
            return false;
        }
        $f = $this->findFile($ident);
        if ($f === null) {
            return false;
        }
        foreach (array_keys($this->pkgsByPaths) as $path) {
            if (str_starts_with($f, $path)) {
                return false;
            }
        }
        return !str_starts_with($f, $this->vendorDir);
    }

    /** @param  non-empty-string  $ident */
    private function isStub(string $ident): bool
    {
        return $this->stub($ident) !== null || $ident === 'IntBackedEnum' || $ident === 'StringBackedEnum';
    }

    /**
     * @param  non-empty-string  $ident
     * @return ?non-empty-string
     */
    private function stub(string $ident): ?string
    {
        return PhpStormStubsMap::CLASSES[$ident]
            ?? PhpStormStubsMap::FUNCTIONS[$ident]
            ?? PhpStormStubsMap::CONSTANTS[$ident]
            ?? null;
    }

    /**
     * @param  non-empty-string  $c
     * @return ?non-empty-string
     */
    private function findConstFile(string $c): ?string
    {
        if (!isset($this->userConsts[$c])) {
            return null;
        }

        $parts = explode('\\', $c);
        $last = count($parts) - 1;
        $hasNs = $last > 0;
        $ns = implode('\\', array_slice($parts, 0, $last));
        $const = $parts[$last];
        $ns = preg_quote($ns);
        $qualifiedConst = str_replace('\\', '\\\\', $c);
        $qualifiedConst = preg_quote($qualifiedConst);

        $defineConstPattern = "/^\s*define\s*\(\s*['\"]{$qualifiedConst}['\"]\s*,/m";
        $assignConstPattern = "/^\s*const\s+{$const}\s*=/m";
        $nsPattern = "/^\s*namespace\s+{$ns};/m";
        $anyNsPattern = '/^\s*namespace\s+.+;/m';

        $files = get_included_files();
        foreach ($files as $f) {
            if ($f === '') {
                continue;
            }
            if (!str_starts_with($f, 'phar://')) {
                $f = realpath($f);
            }
            if ($f === false) {
                continue;
            }

            $content = Reader::read($f);
            if (preg_match($defineConstPattern, $content) === 1) {
                return $f;
            }
            if (preg_match($assignConstPattern, $content) !== 1) {
                continue;
            }
            if ($hasNs && preg_match($nsPattern, $content) === 1) {
                return $f;
            }
            if (!$hasNs && preg_match($anyNsPattern, $content) === 0) {
                return $f;
            }
        }
        return null;
    }
}
