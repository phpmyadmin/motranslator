<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for gettext locales.
 *
 * @package PhpMyAdmin-test
 */
class LocalesTest extends PHPUnit_Framework_TestCase
{

    public function test_setlocale_system()
    {
        putenv("LC_ALL=");
        // For an existing locale, it never needs emulation.
        putenv("LANG=C");
        _setlocale(LC_MESSAGES, "");
        $this->assertEquals(0, locale_emulation());
    }

    public function test_setlocale_emulation()
    {
        putenv("LC_ALL=");
        // If we set it to a non-existent locale, it still works, but uses
        // emulation.
        _setlocale(LC_MESSAGES, "xxx_XXX");
        $this->assertEquals('xxx_XXX', _setlocale(LC_MESSAGES, 0));
        $this->assertEquals(1, locale_emulation());
    }
}

