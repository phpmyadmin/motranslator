<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for MO files parsing.
 *
 * @package PhpMyAdmin-test
 */

class MoFileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideMoFiles
     */
    public function testMoFile($filename)
    {
        $parser = new MoTranslator\MoTranslator($filename);
        $this->assertEquals(
            $parser->translate('Column'),
            'Pole'
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
}
