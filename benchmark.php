<?php

require './vendor/autoload.php';

$files = array('./tests/data/big.mo', './tests/data/little.mo');

$start = microtime(true);

for ($i = 0; $i < 200; $i++) {
    foreach ($files as $filename) {
        $parser = new MoTranslator\MoTranslator($filename);
        $parser->gettext('Column');
    }
}

$end = microtime(true);

$diff = $end - $start;

echo "Execution took $diff seconds\n";
