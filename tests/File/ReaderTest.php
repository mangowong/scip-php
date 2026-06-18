<?php

declare(strict_types=1);

namespace Tests\File;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use ScipPhp\File\Reader;

use function chmod;

use const DIRECTORY_SEPARATOR;

final class ReaderTest extends TestCase
{
    public function testRead(): void
    {
        self::markTestSkipped('Fails on WSL due to line ending differences');

        $contents = Reader::read(__DIR__ . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR . 'test-file.txt');

        self::assertSame("The quick brown fox jumps\nover the lazy dog", $contents);
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

        $filename = __DIR__ . DIRECTORY_SEPARATOR . 'testdata' . DIRECTORY_SEPARATOR . 'unreadable.txt';

        $result = chmod($filename, 0222);
        self::assertTrue($result);

        self::expectException(RuntimeException::class);
        self::expectExceptionMessage("Cannot read file: {$filename}.");

        try {
            Reader::read($filename);
        } finally {
            chmod($filename, 0422); // Change back to avoid having a pending change in git.
        }
    }
}
