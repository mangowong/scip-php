<?php

declare(strict_types=1);

namespace Tests\Parser;

use PHPUnit\Framework\TestCase;

final class ParserTest extends TestCase
{
    public function testEmptyFile(): void
    {
        self::markTestSkipped('Fails on WSL due to filesystem handling');
    }
}
