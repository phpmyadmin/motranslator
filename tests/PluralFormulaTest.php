<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */

use PHPUnit\Framework\TestCase;

/**
 * Test for gettext parsing.
 */
class PluralFormlulaTest extends TestCase
{
    /**
     * Test for extractPluralsForms.
     *
     *
     * @dataProvider pluralExtractionData
     *
     * @param mixed $header
     * @param mixed $expected
     */
    public function testExtractPluralsForms($header, $expected)
    {
        $this->assertEquals(
            $expected,
            PhpMyAdmin\MoTranslator\Translator::extractPluralsForms($header)
        );
    }

    public function pluralExtractionData()
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

    /**
     * @dataProvider pluralCounts
     *
     * @param mixed $expr
     * @param mixed $expected
     */
    public function testPluralCounts($expr, $expected)
    {
        $this->assertEquals(
            $expected,
            PhpMyAdmin\MoTranslator\Translator::extractPluralCount($expr)
        );
    }

    public function pluralCounts()
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

    /**
     * @dataProvider pluralExpressions
     *
     * @param mixed $expr
     * @param mixed $expected
     */
    public function testPluralExpression($expr, $expected)
    {
        $this->assertEquals(
            $expected,
            PhpMyAdmin\MoTranslator\Translator::sanitizePluralExpression($expr)
        );
    }

    public function pluralExpressions()
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
