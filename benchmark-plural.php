<?php

declare(strict_types=1);

require './vendor/autoload.php';

$files = [
    './tests/data/big.mo',
    './tests/data/little.mo',
];

$start = microtime(true);

foreach ($files as $filename) {
    $parser = new PhpMyAdmin\MoTranslator\Translator($filename);
    for ($i = 0; $i < 20000; ++$i) {
        $parser->ngettext(
            '%d second',
            '%d seconds',
            10
        );
        $parser->ngettext(
            '%d second',
            '%d seconds',
            1
        );
    }
}

$end = microtime(true);

$diff = $end - $start;

echo 'Execution took ' . $diff . ' seconds' . "\n";
