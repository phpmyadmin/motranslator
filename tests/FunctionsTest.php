<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for functions.
 *
 * @package PhpMyAdmin-test
 */


class FunctionsTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        MoTranslator\MoLoader::load_functions();

        _setlocale(LC_MESSAGES, 'cs');
        _textdomain('phpmyadmin');
        _bindtextdomain('phpmyadmin', __DIR__ . '/data/locale/');
    }

    public function test_gettext()
    {
        $this->assertEquals(
            'Typ',
            _gettext('Type')
        );

        $this->assertEquals(
            'Typ',
            __('Type')
        );

        $this->assertEquals(
            '%d sekundy',
            _ngettext(
                '%d second',
                '%d seconds',
                2
            )
        );

        $this->assertEquals(
            '%d seconds',
            _npgettext(
                'context',
                '%d second',
                '%d seconds',
                2
            )
        );

        $this->assertEquals(
            'Tabulka',
            _pgettext(
                'Display format',
                'Table'
            )
        );
    }
}
