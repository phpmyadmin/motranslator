<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests;

use PhpMyAdmin\MoTranslator\Translator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * Test for gettext parsing.
 */
class PluralFormulaTest extends TestCase
{
    /**
     * Test for extractPluralsForms.
     */
    #[DataProvider('pluralExtractionData')]
    public function testExtractPluralsForms(string $header, string $expected): void
    {
        self::assertSame(
            $expected,
            Translator::extractPluralsForms($header),
        );
    }

    /** @return list<array{string, string}> */
    public static function pluralExtractionData(): array
    {
        return [
            // It defaults to a "Western-style" plural header.
            [
                '',
                'nplurals=2; plural=n == 1 ? 0 : 1;',
            ],
            // Extracting it from the middle of the header works.
            [
                "Content-type: text/html; charset=UTF-8\n"
                . "Plural-Forms: nplurals=1; plural=0;\n"
                . "Last-Translator: nobody\n",
                ' nplurals=1; plural=0;',
            ],
            // It's also case-insensitive.
            [
                "PLURAL-forms: nplurals=1; plural=0;\n",
                ' nplurals=1; plural=0;',
            ],
            // It falls back to default if it's not on a separate line.
            [
                'Content-type: text/html; charset=UTF-8' // note the missing \n here
                . "Plural-Forms: nplurals=1; plural=0;\n"
                . "Last-Translator: nobody\n",
                'nplurals=2; plural=n == 1 ? 0 : 1;',
            ],
        ];
    }

    #[DataProvider('pluralCounts')]
    public function testPluralCounts(string $expr, int $expected): void
    {
        self::assertSame(
            $expected,
            Translator::extractPluralCount($expr),
        );
    }

    /** @return list<array{string, int}> */
    public static function pluralCounts(): array
    {
        return [
            [
                '',
                1,
            ],
            [
                'foo=2; expr',
                1,
            ],
            [
                'nplurals=2; epxr',
                2,
            ],
            [
                ' nplurals = 3 ; epxr',
                3,
            ],
            [
                ' nplurals = 4 ; epxr ; ',
                4,
            ],
            [
                'nplurals',
                1,
            ],
        ];
    }

    #[DataProvider('pluralExpressions')]
    public function testPluralExpression(string $expr, string $expected): void
    {
        self::assertSame(
            $expected,
            Translator::sanitizePluralExpression($expr),
        );
    }

    /** @return list<array{string, string}> */
    public static function pluralExpressions(): array
    {
        return [
            [
                '',
                '',
            ],
            [
                'nplurals=2; plural=n == 1 ? 0 : 1;',
                'n == 1 ? 0 : 1',
            ],
            [
                ' nplurals=1; plural=0;',
                '0',
            ],
            [
                "nplurals=6; plural=n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 ? 4 : 5;\n",
                'n==0 ? 0 : n==1 ? 1 : n==2 ? 2 : n%100>=3 && n%100<=10 ? 3 : n%100>=11 ? 4 : 5',
            ],
            [
                ' nplurals=1; plural=baz(n);',
                '(n)',
            ],
            [
                ' plural=n',
                'n',
            ],
            [
                'nplurals',
                'n',
            ],
        ];
    }
}
