<?php

declare(strict_types=1);

use PhpMyAdmin\MoTranslator\Cache\ApcuCache;
use PhpMyAdmin\MoTranslator\MoParser;
use PhpMyAdmin\MoTranslator\Translator;

require './vendor/autoload.php';

$files = [
    'big' => './tests/data/big.mo',
    'little' => './tests/data/little.mo',
];

$start = microtime(true);

for ($i = 0; $i < 2000; ++$i) {
    foreach ($files as $domain => $filename) {
        $translator = new Translator(new ApcuCache(new MoParser($filename), 'foo', $domain));
        $translator->gettext('Column');
    }
}

$end = microtime(true);

$diff = $end - $start;

echo 'Execution took ' . $diff . ' seconds' . "\n";
