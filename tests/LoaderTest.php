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

    private function get_loader($domain, $locale)
    {
        $loader = new MoTranslator\MoLoader();
        $loader->setlocale($locale);
        $loader->textdomain($domain);
        $loader->bindtextdomain($domain, __DIR__ . '/data/locale/');
        return $loader;
    }

    /**
     * @dataProvider translator_data
     */
    public function test_get_translator($domain, $locale, $otherdomain, $expected)
    {
        $loader = $this->get_loader($domain, $locale);
        $translator = $loader->get_translator($otherdomain);
        $this->assertEquals(
            $expected,
            $translator->gettext('Type')
        );
    }

    public function translator_data()
    {
        return array(
            array(
                'phpmyadmin',
                'cs',
                '',
                'Typ',
            ),
            array(
                'phpmyadmin',
                'cs_CZ',
                '',
                'Typ',
            ),
            array(
                'phpmyadmin',
                'be_BY',
                '',
                'Тып',
            ),
            array(
                'phpmyadmin',
                'be@latin',
                '',
                'Typ',
            ),
            array(
                'phpmyadmin',
                'cs',
                'other',
                'Type',
            ),
            array(
                'other',
                'cs',
                'phpmyadmin',
                'Type',
            ),
        );
    }

    public function test_instance()
    {
        $loader = MoTranslator\MoLoader::getInstance();
        $loader->setlocale('cs');
        $loader->textdomain('phpmyadmin');
        $loader->bindtextdomain('phpmyadmin', __DIR__ . '/data/locale/');

        $translator = $loader->get_translator();
        $this->assertEquals(
            'Typ',
            $translator->gettext('Type')
        );

        /* Ensure the object survives */
        $loader = MoTranslator\MoLoader::getInstance();
        $translator = $loader->get_translator();
        $this->assertEquals(
            'Typ',
            $translator->gettext('Type')
        );
    }
}
