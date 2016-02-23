<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for gettext parsing.
 *
 * @package PhpMyAdmin-test
 */

class PluralTest extends PHPUnit_Framework_TestCase
{
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
        $parser = new MoTranslator\Translator(null);
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
