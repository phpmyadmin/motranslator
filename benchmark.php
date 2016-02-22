<?php

require './vendor/autoload.php';
require_once 'src/streams.php';

$files = glob('./tests/data/*.mo');

$start = microtime(true);

for ($i = 0; $i < 200; $i++) {
    foreach ($files as $filename) {
        $reader = new FileReader($filename);
        $parser = new MoTranslator\MoTranslator($reader);
        $parser->load_tables();
        $parser->translate('Column');
    }
}

$end = microtime(true);

$diff = $end - $start;

echo "Execution took $diff seconds\n";
