<?php

declare(strict_types=1);

namespace ScipPhp\Composer;

use Composer\ClassMapGenerator\ClassMapGenerator;

use function array_merge;
use function array_unique;
use function array_values;
use function count;
use function implode;
use function is_array;
use function is_string;
use function realpath;
use function rtrim;
use function str_starts_with;

use const DIRECTORY_SEPARATOR;

final class ProjectFiles
{
    /** @var list<non-empty-string> */
    private readonly array $projectFiles;

    /**
     * @param  non-empty-string  $projectRoot
     * @param  array<array-key, mixed>  $autoload
     * @param  array<array-key, mixed>  $autoloadDev
     * @param  array<array-key, mixed>  $bin
     */
    public function __construct(
        private readonly string $projectRoot,
        array $autoload,
        array $autoloadDev,
        array $bin,
    ) {
        $binFiles = $this->collectPaths($bin);
        $this->projectFiles = array_merge(
            $binFiles,
            $this->loadProjectFiles($autoload),
            $this->loadProjectFiles($autoloadDev),
        );
    }

    /** @return list<non-empty-string> */
    public function projectFiles(): array
    {
        return $this->projectFiles;
    }

    /**
     * @param  array<array-key, mixed>  $autoload
     * @return array<int, non-empty-string>
     */
    private function loadProjectFiles(array $autoload): array
    {
        $generator = new ClassMapGenerator();
        $exclusionRegex = null;
        if (is_array($autoload['exclude-from-classmap'] ?? null) && count($autoload['exclude-from-classmap']) > 0) {
            $exclusions = [];
            foreach ($autoload['exclude-from-classmap'] as $e) {
                if (is_string($e) && $e !== '') {
                    $exclusions[] = $e;
                }
            }
            $exclusionRegex = '{(' . implode('|', $exclusions) . ')}';
        }
        if (is_array($autoload['classmap'] ?? null)) {
            foreach ($autoload['classmap'] as $path) {
                if (!is_string($path) || $path === '') {
                    continue;
                }
                $p = $this->join($this->projectRoot, $path);
                $generator->scanPaths($p, $exclusionRegex);
            }
        }
        foreach (['psr-4', 'psr-0'] as $t) {
            if (!is_array($autoload[$t] ?? null)) {
                continue;
            }
            foreach ($autoload[$t] as $ns => $paths) {
                if (!is_string($ns) || $ns === '' || (!is_array($paths) && !is_string($paths))) {
                    continue;
                }
                $paths = is_string($paths) ? [$paths] : $paths;
                foreach ($paths as $path) {
                    if (!is_string($path) || $path === '') {
                        continue;
                    }
                    $p = $this->join($this->projectRoot, $path);
                    $p = rtrim($p, DIRECTORY_SEPARATOR);
                    $generator->scanPaths($p, $exclusionRegex, $t, $ns);
                }
            }
        }

        $map = $generator->getClassMap();
        $map->sort();
        $classFiles = array_unique(array_values($map->getMap()));

        if (!is_array($autoload['files'] ?? null)) {
            return $classFiles;
        }
        $files = $this->collectPaths($autoload['files']);
        return array_merge($files, $classFiles);
    }

    /**
     * @param  non-empty-string  $elem
     * @param  non-empty-string  $elems
     * @return non-empty-string
     */
    private function join(string $elem, string ...$elems): string
    {
        $parts = [$elem, ...$elems];
        return implode(DIRECTORY_SEPARATOR, $parts);
    }

    /**
     * @param  array<array-key, mixed>  $paths
     * @return list<non-empty-string>
     */
    private function collectPaths(array $paths): array
    {
        $files = [];
        foreach ($paths as $p) {
            if (!is_string($p) || $p === '') {
                continue;
            }
            $p = $this->join($this->projectRoot, $p);
            $p = str_starts_with($p, 'phar://') ? $p : realpath($p);
            if ($p !== false) {
                $files[] = $p;
            }
        }
        return $files;
    }
}
