<?php

declare(strict_types=1);

use PhpMyAdmin\MoTranslator\Cache\InMemoryCache;
use PhpMyAdmin\MoTranslator\MoParser;
use PhpMyAdmin\MoTranslator\Translator;

require './vendor/autoload.php';

$files = [
    './tests/data/big.mo',
    './tests/data/little.mo',
];

$start = microtime(true);

foreach ($files as $filename) {
    $translator = new Translator(new InMemoryCache(new MoParser($filename)));
    for ($i = 0; $i < 200000; ++$i) {
        $translator->pgettext('Display format', 'Table');
    }
}

$end = microtime(true);

$diff = $end - $start;

echo 'Execution took ' . $diff . ' seconds' . "\n";
