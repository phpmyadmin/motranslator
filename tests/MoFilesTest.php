<?php

/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for MO files parsing.
 */
class MoFilesTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideMoFiles
     *
     * @param mixed $filename
     */
    public function testMoFileTranslate($filename)
    {
        $parser = new PhpMyAdmin\MoTranslator\Translator($filename);
        $this->assertEquals(
            'Pole',
            $parser->gettext('Column')
        );
        // Non existing string
        $this->assertEquals(
            'Column parser',
            $parser->gettext('Column parser')
        );
    }

    /**
     * @dataProvider provideMoFiles
     *
     * @param mixed $filename
     */
    public function testMoFilePlurals($filename)
    {
        $parser = new PhpMyAdmin\MoTranslator\Translator($filename);
        if (strpos($filename, 'plurals.mo') !== false) {
            $expected = '%d sekundy';
        } else {
            $expected = '%d sekund';
        }
        $this->assertEquals(
            $expected,
            $parser->ngettext(
                '%d second',
                '%d seconds',
                0
            )
        );
        $this->assertEquals(
            '%d sekunda',
            $parser->ngettext(
                '%d second',
                '%d seconds',
                1
            )
        );
        $this->assertEquals(
            '%d sekundy',
            $parser->ngettext(
                '%d second',
                '%d seconds',
                2
            )
        );
        $this->assertEquals(
            $expected,
            $parser->ngettext(
                '%d second',
                '%d seconds',
                5
            )
        );
        $this->assertEquals(
            $expected,
            $parser->ngettext(
                '%d second',
                '%d seconds',
                10
            )
        );
        // Non existing string
        $this->assertEquals(
            '"%d" seconds',
            $parser->ngettext(
                '"%d" second',
                '"%d" seconds',
                10
            )
        );
    }

    /**
     * @dataProvider provideMoFiles
     *
     * @param mixed $filename
     */
    public function testMoFileContext($filename)
    {
        $parser = new PhpMyAdmin\MoTranslator\Translator($filename);
        $this->assertEquals(
            'Tabulka',
            $parser->pgettext(
                'Display format',
                'Table'
            )
        );
    }

    public function provideMoFiles()
    {
        $result = array();
        foreach (glob('./tests/data/*.mo') as $file) {
            $result[] = array($file);
        }

        return $result;
    }

    public function provideErrorMoFiles()
    {
        $result = array();
        foreach (glob('./tests/data/error/*.mo') as $file) {
            $result[] = array($file);
        }

        return $result;
    }

    /**
     * @dataProvider provideErrorMoFiles
     *
     * @param mixed $file
     */
    public function testEmptyMoFile($file)
    {
        $parser = new PhpMyAdmin\MoTranslator\Translator($file);
        if (basename($file) === 'magic.mo') {
            $this->assertEquals(PhpMyAdmin\MoTranslator\Translator::ERROR_BAD_MAGIC, $parser->error);
        } else {
            $this->assertEquals(PhpMyAdmin\MoTranslator\Translator::ERROR_READING, $parser->error);
        }
        $this->assertEquals(
            'Table',
            $parser->pgettext(
                'Display format',
                'Table'
            )
        );
        $this->assertEquals(
            '"%d" seconds',
            $parser->ngettext(
                '"%d" second',
                '"%d" seconds',
                10
            )
        );
    }
}
