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
        $translator = new PhpMyAdmin\MoTranslator\Translator(
            new PhpMyAdmin\MoTranslator\Cache\InMemoryCache(new PhpMyAdmin\MoTranslator\MoParser($filename))
        );
        $translator->gettext('Column');
    }
}

$end = microtime(true);

$diff = $end - $start;

echo 'Execution took ' . $diff . ' seconds' . "\n";
