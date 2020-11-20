<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests;

use PhpMyAdmin\MoTranslator\Translator;
use PHPUnit\Framework\TestCase;
use function implode;
use function chr;

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
     *
     * @return array[]
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

    /**
     * Test for ngettext
     */
    public function testNgettext(): void
    {
        $parser = new Translator('');
        $translationKey = implode(chr(0), ["%d pig went to the market\n", "%d pigs went to the market\n"]);
        $parser->setTranslation($translationKey, '');
        $result = $parser->ngettext(
            "%d pig went to the market\n",
            "%d pigs went to the market\n",
            1
        );
        $this->assertSame('', $result);
    }
}
