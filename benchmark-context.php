<?php

require './vendor/autoload.php';

$files = array('./tests/data/big.mo', './tests/data/little.mo');

$start = microtime(true);

foreach ($files as $filename) {
    $parser = new MoTranslator\Translator($filename);
    for ($i = 0; $i < 200000; $i++) {
        $parser->pgettext(
            'Display format',
            'Table'
        );
    }
}

$end = microtime(true);

$diff = $end - $start;

echo "Execution took $diff seconds\n";
