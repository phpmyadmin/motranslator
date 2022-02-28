<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Cache;

use PhpMyAdmin\MoTranslator\CacheException;
use PhpMyAdmin\MoTranslator\MoParser;

use function apcu_enabled;
use function apcu_entry;
use function apcu_exists;
use function apcu_fetch;
use function apcu_store;
use function array_combine;
use function array_keys;
use function array_map;
use function assert;
use function function_exists;
use function is_array;

final class ApcuCache implements CacheInterface
{
    public const LOADED_KEY = '__TRANSLATIONS_LOADED__';

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

    public function __construct(
        MoParser $parser,
        string $locale,
        string $domain,
        int $ttl = 0,
        bool $reloadOnMiss = true,
        string $prefix = 'mo_'
    ) {
        // @codeCoverageIgnoreStart
        if (! (function_exists('apcu_enabled') && apcu_enabled())) {
            throw new CacheException('ACPu extension must be installed and enabled');
        }

        // @codeCoverageIgnoreEnd

        $this->parser = $parser;
        $this->locale = $locale;
        $this->domain = $domain;
        $this->ttl = $ttl;
        $this->reloadOnMiss = $reloadOnMiss;
        $this->prefix = $prefix;

        $this->ensureTranslationsLoaded();
    }

    public function get(string $msgid): string
    {
        $msgstr = apcu_fetch($this->getKey($msgid), $success);
        if ($success) {
            return $msgstr;
        }

        if (! $this->reloadOnMiss) {
            return $msgid;
        }

        // store original in case translation is not present
        apcu_store($msgid, $msgid);
        // reload .mo file, in case entry has been evicted
        $this->parser->parseIntoCache($this);

        $msgstr = apcu_fetch($this->getKey($msgid), $success);

        return $success ? $msgstr : $msgid;
    }

    public function set(string $msgid, string $msgstr): void
    {
        apcu_store($this->getKey($msgid), $msgstr, $this->ttl);
    }

    public function has(string $msgid): bool
    {
        return apcu_exists($this->getKey($msgid));
    }

    public function setAll(array $translations): void
    {
        $keys = array_map(function (string $msgid): string {
            return $this->getKey($msgid);
        }, array_keys($translations));
        $translations = array_combine($keys, $translations);
        assert(is_array($translations));

        apcu_store($translations, null, $this->ttl);
    }

    private function getKey(string $msgid): string
    {
        return $this->prefix . $this->locale . '.' . $this->domain . '.' . $msgid;
    }

    private function ensureTranslationsLoaded(): void
    {
        // Try to prevent cache slam if multiple processes are trying to load translations. There is still a race
        // between the exists check and creating the entry, but at least it's small
        $key = $this->getKey(self::LOADED_KEY);
        $loaded = apcu_exists($key) || apcu_entry($key, static function (): int {
            return 0;
        });
        if ($loaded) {
            return;
        }

        $this->parser->parseIntoCache($this);
        apcu_store($this->getKey(self::LOADED_KEY), 1, $this->ttl);
    }
}