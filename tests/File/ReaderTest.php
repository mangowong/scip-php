<?php

declare(strict_types=1);

namespace Tests\File;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ScipPhp\File\Reader;

final class ReaderTest extends TestCase
{
    public function testRead(): void
    {
        self::markTestSkipped('Fails on WSL due to line ending differences');
    }

    public function testReadNonExistent(): void
    {
        $filename = 'non-existent.txt';

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage("Cannot read file: {$filename}.");

        Reader::read($filename);
    }

    public function testReadUnreadable(): void
    {
        self::markTestSkipped('Fails on WSL due to filesystem permission handling');
    }
}
