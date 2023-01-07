<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Cache;

use PhpMyAdmin\MoTranslator\MoParser;
use Psr\Cache\CacheItemPoolInterface;

final class Psr6CacheFactory implements CacheFactoryInterface
{
    /** @var CacheItemPoolInterface */
    private $psr6Cache;

    /** @var int */
    private $ttl;

    /** @var bool */
    private $reloadOnMiss;

    /** @var string */
    private $prefix;

    /** @var string */
    private $separator;

    public function __construct(
        CacheItemPoolInterface $psr6Cache,
        int $ttl = 0,
        bool $reloadOnMiss = true,
        string $prefix = 'mo_',
        string $separator = '.'
    ) {
        $this->psr6Cache = $psr6Cache;
        $this->ttl = $ttl;
        $this->reloadOnMiss = $reloadOnMiss;
        $this->prefix = $prefix;
        $this->separator = $separator;
    }

    public function getInstance(MoParser $parser, string $locale, string $domain): CacheInterface
    {
        return new Psr6Cache(
            $this->psr6Cache,
            $parser,
            $locale,
            $domain,
            $this->ttl,
            $this->reloadOnMiss,
            $this->prefix,
            $this->separator
        );
    }
}
