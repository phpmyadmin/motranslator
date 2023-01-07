<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Cache;

use PhpMyAdmin\MoTranslator\MoParser;
use Psr\Cache\CacheItemPoolInterface;

use function array_combine;
use function array_keys;
use function array_map;
use function is_string;
use function md5;

final class Psr6Cache implements CacheInterface
{
    public const LOADED_KEY = '__TRANSLATIONS_LOADED__';

    /** @var CacheItemPoolInterface */
    private $psr6Cache;

    /** @var MoParser */
    private $parser;

    /** @var string */
    private $locale;

    /** @var string */
    private $domain;

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
        MoParser $parser,
        string $locale,
        string $domain,
        int $ttl = 0,
        bool $reloadOnMiss = true,
        string $prefix = 'mo_',
        string $separator = '.'
    ) {
        $this->psr6Cache = $psr6Cache;
        $this->parser = $parser;
        $this->locale = $locale;
        $this->domain = $domain;
        $this->ttl = $ttl;
        $this->reloadOnMiss = $reloadOnMiss;
        $this->prefix = $prefix;
        $this->separator = $separator;

        $this->ensureTranslationsLoaded();
    }

    public function get(string $msgid): string
    {
        $cacheItem = $this->psr6Cache->getItem($this->getKey($msgid));
        $cacheItemValue = $cacheItem->isHit() ? $cacheItem->get() : null;
        if (is_string($cacheItemValue)) {
            return $cacheItemValue;
        }

        if (! $this->reloadOnMiss) {
            return $msgid;
        }

        $cacheItem->set($msgid);
        if ($this->ttl > 0) {
            $cacheItem->expiresAfter($this->ttl);
        }

        $this->psr6Cache->save($cacheItem);

        // reload .mo file, in case entry has been evicted
        $this->parser->parseIntoCache($this);

        $cacheItem = $this->psr6Cache->getItem($this->getKey($msgid));
        $cacheItemValue = $cacheItem->isHit() ? $cacheItem->get() : null;

        return is_string($cacheItemValue)
            ? $cacheItemValue
            : $msgid;
    }

    public function set(string $msgid, string $msgstr): void
    {
        $cacheItem = $this->psr6Cache->getItem($this->getKey($msgid));
        $cacheItem->set($msgstr);
        if ($this->ttl > 0) {
            $cacheItem->expiresAfter($this->ttl);
        }

        $this->psr6Cache->save($cacheItem);
    }

    public function has(string $msgid): bool
    {
        return $this->psr6Cache->hasItem($this->getKey($msgid));
    }

    public function setAll(array $translations): void
    {
        $keys = array_map(function (string $msgid): string {
            return $this->getKey($msgid);
        }, array_keys($translations));
        $translations = array_combine($keys, $translations);

        foreach ($this->psr6Cache->getItems($keys) as $cacheItem) {
            $cacheItem->set($translations[$cacheItem->getKey()]);
            if ($this->ttl > 0) {
                $cacheItem->expiresAfter($this->ttl);
            }

            $this->psr6Cache->saveDeferred($cacheItem);
        }

        $this->psr6Cache->commit();
    }

    private function getKey(string $msgid): string
    {
        // Hash the message ID to avoid using restricted characters in various cache adapters.
        return $this->prefix . $this->locale . $this->separator . $this->domain . $this->separator . md5($msgid);
    }

    private function ensureTranslationsLoaded(): void
    {
        // Try to prevent cache slam if multiple processes are trying to load translations. There is still a race
        // between the exists check and creating the entry, but at least it's small
        $cacheItem = $this->psr6Cache->getItem($this->getKey(self::LOADED_KEY));
        if ($cacheItem->isHit()) {
            return;
        }

        $this->parser->parseIntoCache($this);

        $cacheItem->set(1);
        if ($this->ttl > 0) {
            $cacheItem->expiresAfter($this->ttl);
        }

        $this->psr6Cache->save($cacheItem);
    }
}
