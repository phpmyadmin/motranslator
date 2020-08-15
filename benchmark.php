<?php

declare(strict_types=1);

require './vendor/autoload.php';

$files = [
    './tests/data/big.mo',
    './tests/data/little.mo',
];

$start = microtime(true);

for ($i = 0; $i < 2000; ++$i) {
    foreach ($files as $filename) {
        $parser = new PhpMyAdmin\MoTranslator\Translator($filename);
        $parser->gettext('Column');
    }
}

$end = microtime(true);

$diff = $end - $start;

echo 'Execution took ' . $diff . ' seconds' . "\n";
