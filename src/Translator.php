<?php

declare(strict_types=1);

/*
    Copyright (c) 2003, 2009 Danilo Segan <danilo@kvota.net>.
    Copyright (c) 2005 Nico Kaiser <nico@siriux.net>
    Copyright (c) 2016 Michal Čihař <michal@cihar.com>

    This file is part of MoTranslator.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License along
    with this program; if not, write to the Free Software Foundation, Inc.,
    51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
*/

namespace PhpMyAdmin\MoTranslator;

use PhpMyAdmin\MoTranslator\Cache\CacheInterface;
use PhpMyAdmin\MoTranslator\Cache\GetAllInterface;
use PhpMyAdmin\MoTranslator\Cache\InMemoryCache;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Throwable;

use function array_key_exists;
use function count;
use function explode;
use function is_numeric;
use function ltrim;
use function preg_replace;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_starts_with;
use function stripos;
use function strtolower;
use function substr;
use function trim;

/**
 * Provides a simple gettext replacement that works independently from
 * the system's gettext abilities.
 * It can read MO files and use them for translating strings.
 *
 * It caches ll strings and translations to speed up the string lookup.
 */
class Translator
{
    /**
     * None error.
     */
    public const ERROR_NONE = 0;

    /**
     * File does not exist.
     */
    public const ERROR_DOES_NOT_EXIST = 1;

    /**
     * File has bad magic number.
     */
    public const ERROR_BAD_MAGIC = 2;

    /**
     * Error while reading file, probably too short.
     */
    public const ERROR_READING = 3;

    /**
     * Big endian mo file magic bytes.
     */
    public const MAGIC_BE = "\x95\x04\x12\xde";

    /**
     * Little endian mo file magic bytes.
     */
    public const MAGIC_LE = "\xde\x12\x04\x95";

    /**
     * Parse error code (0 if no error).
     */
    public int $error = self::ERROR_NONE;

    /**
     * Cache header field for plural forms.
     */
    private string|null $pluralEquation = null;

    /**
     * Evaluator for plurals
     */
    private ExpressionLanguage|null $pluralExpression = null;

    /**
     * number of plurals
     */
    private int|null $pluralCount = null;

    private CacheInterface $cache;

    /** @param CacheInterface|string|null $cache Mo file to load (null for no file) or a CacheInterface implementation */
    public function __construct(CacheInterface|string|null $cache)
    {
        if (! $cache instanceof CacheInterface) {
            $cache = new InMemoryCache(new MoParser($cache));
        }

        $this->cache = $cache;
    }

    /**
     * Translates a string.
     *
     * @param string $msgid String to be translated
     *
     * @return string translated string (or original, if not found)
     */
    public function gettext(string $msgid): string
    {
        return $this->cache->get($msgid);
    }

    /**
     * Check if a string is translated.
     *
     * @param string $msgid String to be checked
     */
    public function exists(string $msgid): bool
    {
        return $this->cache->has($msgid);
    }

    /**
     * Sanitize plural form expression for use in ExpressionLanguage.
     *
     * @param string $expr Expression to sanitize
     *
     * @return string sanitized plural form expression
     */
    public static function sanitizePluralExpression(string $expr): string
    {
        // Parse equation
        $expr = explode(';', $expr);
        $expr = count($expr) >= 2 ? $expr[1] : $expr[0];

        $expr = trim(strtolower($expr));
        // Strip plural prefix
        if (str_starts_with($expr, 'plural')) {
            $expr = ltrim(substr($expr, 6));
        }

        // Strip equals
        if (str_starts_with($expr, '=')) {
            $expr = ltrim(substr($expr, 1));
        }

        // Cleanup from unwanted chars
        $expr = preg_replace('@[^n0-9:\(\)\?=!<>/%&| ]@', '', $expr);

        return (string) $expr;
    }

    /**
     * Extracts number of plurals from plurals form expression.
     *
     * @param string $expr Expression to process
     *
     * @return int Total number of plurals
     */
    public static function extractPluralCount(string $expr): int
    {
        $parts = explode(';', $expr, 2);
        $nplurals = explode('=', trim($parts[0]), 2);
        if (strtolower(rtrim($nplurals[0])) !== 'nplurals') {
            return 1;
        }

        if (count($nplurals) === 1) {
            return 1;
        }

        return (int) $nplurals[1];
    }

    /**
     * Parse full PO header and extract only plural forms line.
     *
     * @param string $header Gettext header
     *
     * @return string verbatim plural form header field
     */
    public static function extractPluralsForms(string $header): string
    {
        $headers = explode("\n", $header);
        $expr = 'nplurals=2; plural=n == 1 ? 0 : 1;';
        foreach ($headers as $header) {
            if (stripos($header, 'Plural-Forms:') !== 0) {
                continue;
            }

            $expr = substr($header, 13);
        }

        return $expr;
    }

    /**
     * Get possible plural forms from MO header.
     *
     * @return string plural form header
     */
    private function getPluralForms(): string
    {
        // lets assume message number 0 is header
        // this is true, right?

        // cache header field for plural forms
        if ($this->pluralEquation === null) {
            $header = $this->cache->get('');

            $expr = self::extractPluralsForms($header);
            $this->pluralEquation = self::sanitizePluralExpression($expr);
            $this->pluralCount = self::extractPluralCount($expr);
        }

        return $this->pluralEquation;
    }

    /**
     * Detects which plural form to take.
     *
     * @param int $n count of objects
     *
     * @return int array index of the right plural form
     */
    private function selectString(int $n): int
    {
        if ($this->pluralExpression === null) {
            $this->pluralExpression = new ExpressionLanguage();
        }

        try {
            $evaluatedPlural = $this->pluralExpression->evaluate($this->getPluralForms(), ['n' => $n]);
            $plural = is_numeric($evaluatedPlural) ? (int) $evaluatedPlural : 0;
        } catch (Throwable) {
            $plural = 0;
        }

        if ($plural >= $this->pluralCount) {
            $plural = $this->pluralCount - 1;
        }

        return $plural;
    }

    /**
     * Plural version of gettext.
     *
     * @param string $msgid       Single form
     * @param string $msgidPlural Plural form
     * @param int    $number      Number of objects
     *
     * @return string translated plural form
     */
    public function ngettext(string $msgid, string $msgidPlural, int $number): string
    {
        // this should contains all strings separated by NULLs
        $key = $msgid . "\u{0}" . $msgidPlural;
        if (! $this->cache->has($key)) {
            return $number !== 1 ? $msgidPlural : $msgid;
        }

        $result = $this->cache->get($key);

        // find out the appropriate form
        $select = $this->selectString($number);

        $list = explode("\u{0}", $result);

        if (array_key_exists($select, $list)) {
            return $list[$select];
        }

        return $list[0];
    }

    /**
     * Translate with context.
     *
     * @param string $msgctxt Context
     * @param string $msgid   String to be translated
     *
     * @return string translated plural form
     */
    public function pgettext(string $msgctxt, string $msgid): string
    {
        $key = $msgctxt . "\u{4}" . $msgid;
        $ret = $this->gettext($key);
        if ($ret === $key) {
            return $msgid;
        }

        return $ret;
    }

    /**
     * Plural version of pgettext.
     *
     * @param string $msgctxt     Context
     * @param string $msgid       Single form
     * @param string $msgidPlural Plural form
     * @param int    $number      Number of objects
     *
     * @return string translated plural form
     */
    public function npgettext(string $msgctxt, string $msgid, string $msgidPlural, int $number): string
    {
        $key = $msgctxt . "\u{4}" . $msgid;
        $ret = $this->ngettext($key, $msgidPlural, $number);
        if (str_contains($ret, "\u{4}")) {
            return $msgid;
        }

        return $ret;
    }

    /**
     * Set translation in place
     *
     * @param string $msgid  String to be set
     * @param string $msgstr Translation
     */
    public function setTranslation(string $msgid, string $msgstr): void
    {
        $this->cache->set($msgid, $msgstr);
    }

    /**
     * Set the translations
     *
     * @param array<string,string> $translations The translations "key => value" array
     */
    public function setTranslations(array $translations): void
    {
        $this->cache->setAll($translations);
    }

    /**
     * Get the translations
     *
     * @return array<string,string> The translations "key => value" array
     */
    public function getTranslations(): array
    {
        if ($this->cache instanceof GetAllInterface) {
            return $this->cache->getAll();
        }

        throw new CacheException(sprintf(
            "Cache '%s' does not support getting translations",
            $this->cache::class,
        ));
    }
}
