<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests;

use PhpMyAdmin\MoTranslator\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Test for gettext parsing.
 */
class PluralTest extends TestCase
{
    /**
     * Test for npgettext.
     *
     * @param int    $number   Number
     * @param string $expected Expected output
     *
     * @dataProvider providerTestNpgettext
     */
    public function testNpgettext(int $number, string $expected): void
    {
        $parser = new Translator('');
        $result = $parser->npgettext(
            'context',
            "%d pig went to the market\n",
            "%d pigs went to the market\n",
            $number
        );
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_npgettext.
     */
    public static function providerTestNpgettext(): array
    {
        return [
            [
                1,
                "%d pig went to the market\n",
            ],
            [
                2,
                "%d pigs went to the market\n",
            ],
        ];
    }
}
