<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests;

use PhpMyAdmin\MoTranslator\StringReader;
use PHPUnit\Framework\TestCase;

class StringReaderTest extends TestCase
{
    public function testReadFails(): void
    {
        $tempFile = (string) tempnam(sys_get_temp_dir(), 'phpMyAdmin_StringReaderTest');
        $this->assertFileExists($tempFile);
        $stringReader = new StringReader($tempFile);
        $actual = $stringReader->read(-1, -1);
        $this->assertSame('', $actual);
        unlink($tempFile);
    }
}
