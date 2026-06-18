<?php

declare(strict_types=1);

namespace ScipPhp\Composer;

use RuntimeException;

use function array_merge;
use function count;
use function dirname;
use function is_array;
use function is_file;
use function realpath;
use function str_replace;
use function strlen;
use function strncmp;
use function substr;

final class ClassMap
{
    /** @var array<non-empty-string, non-empty-string> */
    private array $classMap;

    /** @var array<non-empty-string, list<non-empty-string>> */
    private readonly array $psr4;

    /** @var array<non-empty-string, list<non-empty-string>> */
    private readonly array $psr0;

    /** @param non-empty-string $vendorDir */
    public function __construct(string $vendorDir)
    {
        $composerDir = $vendorDir . '/composer';

        $classMap = require $composerDir . '/autoload_classmap.php';
        $this->classMap = is_array($classMap) ? $classMap : [];

        $psr4 = require $composerDir . '/autoload_psr4.php';
        $this->psr4 = is_array($psr4) ? $psr4 : [];

        $psr0 = require $composerDir . '/autoload_namespaces.php';
        $this->psr0 = is_array($psr0) ? $psr0 : [];
    }

    /** @param non-empty-string $class */
    public function findFile(string $class): ?string
    {
        if (isset($this->classMap[$class])) {
            $f = $this->classMap[$class];
            return is_file($f) ? $f : null;
        }

        $f = $this->findPsr4($class);
        if ($f !== null) {
            return $f;
        }

        return $this->findPsr0($class);
    }

    /** @param array<non-empty-string, non-empty-string> $map */
    public function addClassMap(array $map): void
    {
        $this->classMap = array_merge($this->classMap, $map);
    }

    /** @param non-empty-string $class */
    private function findPsr4(string $class): ?string
    {
        $logicalPath = str_replace('\\', '/', $class) . '.php';

        foreach ($this->psr4 as $prefix => $dirs) {
            $len = strlen($prefix);
            if (strncmp($prefix, $class, $len) !== 0) {
                continue;
            }

            $relativePath = substr($logicalPath, $len);
            if ($relativePath === false || $relativePath === '') {
                continue;
            }

            // Strip the first slash since the prefix includes the trailing separator
            if ($relativePath[0] === '/') {
                $relativePath = substr($relativePath, 1);
            }

            foreach ($dirs as $dir) {
                $resolved = $this->resolveBaseDir($dir);
                $f = $resolved . '/' . $relativePath;
                if (is_file($f)) {
                    return $f;
                }
            }
        }

        return null;
    }

    /** @param non-empty-string $class */
    private function findPsr0(string $class): ?string
    {
        $logicalPath = str_replace('_', '/', $class) . '.php';

        foreach ($this->psr0 as $prefix => $dirs) {
            if ($prefix === '') {
                foreach ($dirs as $dir) {
                    $resolved = $this->resolveBaseDir($dir);
                    $f = $resolved . '/' . $logicalPath;
                    if (is_file($f)) {
                        return $f;
                    }
                }
                continue;
            }

            if (strncmp($prefix, $class, strlen($prefix)) !== 0) {
                continue;
            }

            $relativePath = substr($logicalPath, strlen($prefix));
            if ($relativePath === false || $relativePath === '') {
                continue;
            }

            if ($relativePath[0] === '/') {
                $relativePath = substr($relativePath, 1);
            }

            foreach ($dirs as $dir) {
                $resolved = $this->resolveBaseDir($dir);
                $f = $resolved . '/' . $prefix . '/' . $relativePath;
                if (is_file($f)) {
                    return $f;
                }
            }
        }

        return null;
    }

    /** @param non-empty-string $dir */
    private function resolveBaseDir(string $dir): string
    {
        if (strncmp('phar://', $dir, 7) === 0) {
            return $dir;
        }
        $resolved = realpath($dir);
        if ($resolved === false) {
            throw new RuntimeException("Cannot resolve base directory: {$dir}.");
        }
        return $resolved;
    }
}
