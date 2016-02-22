<?php
/* vim: set expandtab sw=4 ts=4 sts=4: */
/**
 * Test for MO files parsing.
 *
 * @package PhpMyAdmin-test
 */

require_once 'src/streams.php';

class MoFileTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideMoFiles
     */
    public function testMoFile($filename)
    {
        $reader = new FileReader($filename);
        $parser = new MoTranslator\MoTranslator($reader);
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
