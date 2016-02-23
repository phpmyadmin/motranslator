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
            array(
                'cs_CZ',
                array('cs_CZ', 'cs')
            ),
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
            // For a locale containing country code, we prefer
            // full locale name, but if that's not found, fall back
            // to the language only locale name.
            array(
                'sr_RS',
                array('sr_RS', 'sr'),
            ),
            // If language code is used, it's the only thing returned.
            array(
                'sr',
                array('sr'),
            ),
            // There is support for language and charset only.
            array(
                'sr.UTF-8',
                array('sr.UTF-8', 'sr'),
            ),

            // It can also split out character set from the full locale name.
            array(
                'sr_RS.UTF-8',
                array('sr_RS.UTF-8', 'sr_RS', 'sr'),
            ),

            // There is support for @modifier in locale names as well.
            array(
                'sr_RS.UTF-8@latin',
                array(
                    'sr_RS.UTF-8@latin', 'sr_RS@latin', 'sr@latin',
                    'sr_RS.UTF-8', 'sr_RS', 'sr'
                ),
            ),
            array(
                'sr.UTF-8@latin',
                array(
                    'sr.UTF-8@latin', 'sr@latin', 'sr.UTF-8', 'sr',
                ),
            ),

            // We can pass in only language and modifier.
            array(
                'sr@latin',
                array('sr@latin', 'sr'),
            ),

            // If locale name is not following the regular POSIX pattern,
            // it's used verbatim.
            array(
                'something',
                array('something'),
            ),

            // Passing in an empty string returns an empty array.
            array(
                '',
                array(),
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
