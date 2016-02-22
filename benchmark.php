<?php

require './vendor/autoload.php';

$files = glob('./tests/data/*.mo');

$start = microtime(true);

for ($i = 0; $i < 200; $i++) {
    foreach ($files as $filename) {
        $parser = new MoTranslator\MoTranslator($filename);
        $parser->translate('Column');
    }
}

$end = microtime(true);

$diff = $end - $start;

echo "Execution took $diff seconds\n";
