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

    /**
     * Test for ngettext
     *
     * @see https://github.com/phpmyadmin/motranslator/issues/37
     */
    public function testNgettextSelectString(): void
    {
        $parser = new Translator('');
        $parser->setTranslation(
            '',
            "Project-Id-Version: phpMyAdmin 5.1.0-dev\n"
            . "Report-Msgid-Bugs-To: translators@phpmyadmin.net\n"
            . "PO-Revision-Date: 2020-09-01 09:12+0000\n"
            . "Last-Translator: William Desportes <williamdes@wdes.fr>\n"
            . 'Language-Team: English (United Kingdom) '
            . "<https:\/\/hosted.weblate.org\/projects\/phpmyadmin\/master\/en_GB\/>\n"
            . "Language: en_GB\n"
            . "MIME-Version: 1.0\n"
            . "Content-Type: text\/plain; charset=UTF-8\n"
            . "Content-Transfer-Encoding: 8bit\n"
            . "Plural-Forms: nplurals=2; plural=n != 1;\n"
            . "X-Generator: Weblate 4.2.1-dev\n"
            . ''
        );
        $translationKey = implode(chr(0), ["%d pig went to the market\n", "%d pigs went to the market\n"]);
        $parser->setTranslation($translationKey, 'ok');
        $result = $parser->ngettext(
            "%d pig went to the market\n",
            "%d pigs went to the market\n",
            1
        );
        $this->assertSame('ok', $result);
    }
}
