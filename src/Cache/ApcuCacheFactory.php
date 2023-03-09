<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Cache;

use PhpMyAdmin\MoTranslator\MoParser;

final class ApcuCacheFactory implements CacheFactoryInterface
{
    public function __construct(private int $ttl = 0, private bool $reloadOnMiss = true, private string $prefix = 'mo_')
    {
    }

    public function getInstance(MoParser $parser, string $locale, string $domain): CacheInterface
    {
        return new ApcuCache($parser, $locale, $domain, $this->ttl, $this->reloadOnMiss, $this->prefix);
    }
}
