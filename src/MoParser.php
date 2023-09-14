<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator;

use PhpMyAdmin\MoTranslator\Cache\CacheInterface;

use function is_readable;

final class MoParser
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

    public function __construct(private readonly string|null $filename = null)
    {
    }

    /**
     * Parses .mo file and stores results to `$cache`
     */
    public function parseIntoCache(CacheInterface $cache): void
    {
        if ($this->filename === null) {
            return;
        }

        if (! is_readable($this->filename)) {
            $this->error = self::ERROR_DOES_NOT_EXIST;

            return;
        }

        $stream = new StringReader($this->filename);

        try {
            $magic = $stream->read(0, 4);
            if ($magic === self::MAGIC_LE) {
                $unpack = 'V';
            } elseif ($magic === self::MAGIC_BE) {
                $unpack = 'N';
            } else {
                $this->error = self::ERROR_BAD_MAGIC;

                return;
            }

            /* Parse header */
            $total = $stream->readint($unpack, 8);
            $originals = $stream->readint($unpack, 12);
            $translations = $stream->readint($unpack, 16);

            /* get original and translations tables */
            $totalTimesTwo = (int) ($total * 2);// Fix for issue #36 on ARM
            $tableOriginals = $stream->readintarray($unpack, $originals, $totalTimesTwo);
            $tableTranslations = $stream->readintarray($unpack, $translations, $totalTimesTwo);

            /* read all strings to the cache */
            for ($i = 0; $i < $total; ++$i) {
                $iTimesTwo = $i * 2;
                $iPlusOne = $iTimesTwo + 1;
                $iPlusTwo = $iTimesTwo + 2;
                $original = $stream->read($tableOriginals[$iPlusTwo], $tableOriginals[$iPlusOne]);
                $translation = $stream->read($tableTranslations[$iPlusTwo], $tableTranslations[$iPlusOne]);
                $cache->set($original, $translation);
            }
        } catch (ReaderException) {
            $this->error = self::ERROR_READING;

            return;
        }
    }
}
