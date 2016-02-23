<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for mo loading.
 *
 * @package PhpMyAdmin-test
 */

class LoaderTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider locale_list
     */
    public function test_list_locales($locale, $expected)
    {
        $this->assertEquals(
            $expected,
            MoTranslator\MoLoader::list_locales($locale)
        );
    }

    public function locale_list()
    {
        return array(
            array('cs_CZ', array('cs_CZ', 'cs')),
            array(
                'sr_CS.UTF-8@latin',
                array(
                    'sr_CS.UTF-8@latin',
                    'sr_CS@latin',
                    'sr@latin',
                    'sr_CS.UTF-8',
                    'sr_CS',
                    'sr',
                )
            ),
        );
    }

    public function test_get_translator()
    {
        $loader = new MoTranslator\MoLoader();
        $this->assertEquals(
            '',
            $loader->get_translator()
        );
    }
}
