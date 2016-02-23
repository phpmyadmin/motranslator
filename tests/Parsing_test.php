<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for gettext parsing.
 *
 * @package PhpMyAdmin-test
 */

class ParsingTest extends PHPUnit_Framework_TestCase
{
    /**
     * Test for extract_plurals_forms
     *
     * @return void
     *
     * @dataProvider plural_extraction_data
     */
    public function test_extract_plurals_forms($header, $expected)
    {
        $this->assertEquals(
            $expected,
            MoTranslator\MoTranslator::extract_plurals_forms($header)
        );
    }

    public function plural_extraction_data()
    {
        return array(
            // It defaults to a "Western-style" plural header.
            array(
                '',
                'nplurals=2; plural=n == 1 ? 0 : 1;',
            ),
            // Extracting it from the middle of the header works.
            array(
                "Content-type: text/html; charset=UTF-8\n"
                . "Plural-Forms: nplurals=1; plural=0;\n"
                . "Last-Translator: nobody\n",
                ' nplurals=1; plural=0;',
            ),
            // It's also case-insensitive.
            array(
                "PLURAL-forms: nplurals=1; plural=0;\n",
                ' nplurals=1; plural=0;',
            ),
            // It falls back to default if it's not on a separate line.
            array(
                "Content-type: text/html; charset=UTF-8" // note the missing \n here
                . "Plural-Forms: nplurals=1; plural=0;\n"
                . "Last-Translator: nobody\n",
                'nplurals=2; plural=n == 1 ? 0 : 1;',
            ),
        );
    }

    /**
     * Test for npgettext
     *
     * @param int    $number   Number
     * @param string $expected Expected output
     *
     * @return void
     *
     * @dataProvider data_provider_test_npgettext
     */
    public function test_npgettext($number, $expected)
    {
        $parser = new MoTranslator\MoTranslator(null);
        $result = $parser->npgettext(
            "context",
            "%d pig went to the market\n",
            "%d pigs went to the market\n",
            $number
        );
        $this->assertSame($expected, $result);
    }

    /**
     * Data provider for test_npgettext
     *
     * @return array
     */
    public static function data_provider_test_npgettext()
    {
        return array(
            array(1, "%d pig went to the market\n"),
            array(2, "%d pigs went to the market\n"),
        );
    }

}

