<?php
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

namespace MoTranslator;

define('MO_MAGIC_BE', "\x95\x04\x12\xde");
define('MO_MAGIC_LE', "\xde\x12\x04\x95");

/**
 * Provides a simple gettext replacement that works independently from
 * the system's gettext abilities.
 * It can read MO files and use them for translating strings.
 *
 * It caches ll strings and translations to speed up the string lookup.
 */
class MoTranslator {
    /**
     * @var int Parse error code (0 if no error)
     */
    public $error = 0;

    /**
     * @var string|null cache header field for plural forms
     */
    private $pluralheader = NULL;
    /**
     * @var int|null number of plurals
     */
    private $pluralcount = NULL;
    /**
     * @var array Array with original -> translation mapping
     */
    private $cache_translations = array();

    /**
     * Constructor
     *
     * @param string $filename Name of mo file to load
     */
    public function __construct($filename)
    {
        if (! is_readable($filename)) {
            $this->error = 2; // file does not exist
            return;
        }

        $stream = new StringReader($filename);

        $magic = $stream->read(0, 4);
        if (strcmp($magic, MO_MAGIC_LE) == 0) {
            $unpack = 'V';
        } elseif (strcmp($magic, MO_MAGIC_BE) == 0) {
            $unpack = 'N';
        } else {
            $this->error = 1; // not MO file
            return;
        }

        /* Parse header */
        $total = $stream->readint($unpack, 8);
        $originals = $stream->readint($unpack, 12);
        $translations = $stream->readint($unpack, 16);

        /* get original and translations tables */
        $table_originals = $stream->readintarray($unpack, $originals, $total * 2);
        $table_translations = $stream->readintarray($unpack, $translations, $total * 2);

        /* read all strings to the cache */
        for ($i = 0; $i < $total; $i++) {
            $original = $stream->read($table_originals[$i * 2 + 2], $table_originals[$i * 2 + 1]);
            $translation = $stream->read($table_translations[$i * 2 + 2], $table_translations[$i * 2 + 1]);
            $this->cache_translations[$original] = $translation;
        }
    }

    /**
     * Translates a string
     *
     * @param string $msgid String to be translated
     *
     * @return string translated string (or original, if not found)
     */
    public function gettext($msgid)
    {
        if (array_key_exists($msgid, $this->cache_translations)) {
            return $this->cache_translations[$msgid];
        } else {
            return $msgid;
        }
    }

    /**
     * Sanitize plural form expression for use in PHP eval call.
     *
     * @param string $expr Expression to sanitize
     *
     * @return string sanitized plural form expression
     */
    public static function sanitize_plural_expression($expr)
    {
        // Get rid of disallowed characters.
        $expr = explode(';', $expr, 2);
        if (count($expr) == 2) {
            $expr = $expr[1];
        } else{
            $expr = $expr[0];
        }
        $expr = preg_replace('@[^a-zA-Z0-9_:;\(\)\?\|\&=!<>+*/\%-]@', '', $expr);

        // Add parenthesis for tertiary '?' operator.
        $expr .= ';';
        $res = '';
        $p = 0;
        for ($i = 0; $i < strlen($expr); $i++) {
            $ch = $expr[$i];
            switch ($ch) {
                case '?':
                    $res .= ' ? (';
                    $p++;
                    break;
                case ':':
                    $res .= ') : (';
                    break;
                case ';':
                    $res .= str_repeat( ')', $p) . ';';
                    $p = 0;
                    break;
                default:
                    $res .= $ch;
            }
        }
        $res = str_replace('n','$n',$res);
        $res = str_replace('plural','$plural',$res);
        return $res;
    }

    /**
     * Extracts number of plurals from plurals form expression
     *
     * @param string $expr Expression to process
     *
     * @return int Total number of plurals
     */
    public static function extract_plural_count($expr)
    {
        $parts = explode(';', $expr, 2);
        $nplurals = explode('=', trim($parts[0]), 2);
        if (strtolower(trim($nplurals[0])) != 'nplurals') {
            return 1;
        }
        return intval($nplurals[1]);
    }

    /**
     * Parse full PO header and extract only plural forms line.
     *
     * @param string $header Gettext header
     *
     * @return string verbatim plural form header field
     */
    public static function extract_plurals_forms($header)
    {
        $headers = explode("\n", $header);
        $expr = "nplurals=2; plural=n == 1 ? 0 : 1;";
        foreach ($headers as $header) {
            if (stripos($header, 'Plural-Forms:') === 0) {
                $expr = substr($header, 13);
            }
        }
        return $expr;
    }

    /**
     * Get possible plural forms from MO header
     *
     * @return string plural form header
     */
    private function get_plural_forms()
    {
        // lets assume message number 0 is header
        // this is true, right?

        // cache header field for plural forms
        if (is_null($this->pluralheader)) {
            $header = $this->cache_translations[""];
            $expr = $this->extract_plurals_forms($header);
            $this->pluralheader = $this->sanitize_plural_expression($expr);
            $this->pluralcount = $this->extract_plural_count($expr);
        }
        return $this->pluralheader;
    }

    /**
     * Detects which plural form to take
     *
     * @param int $n count of objects
     *
     * @return int array index of the right plural form
     */
    private function select_string($n)
    {
        $string = $this->get_plural_forms();

        $plural = 0;

        eval("$string");
        if ($plural >= $this->pluralcount) {
            $plural = $this->pluralcount - 1;
        }
        return $plural;
    }

    /**
     * Plural version of gettext
     *
     * @param string $single Single form
     * @param string $plural Plural form
     * @param string $number Number of objects
     *
     * @return string translated plural form
     */
    public function ngettext($single, $plural, $number)
    {
        // this should contains all strings separated by NULLs
        $key = implode(chr(0), array($single, $plural));
        if (! array_key_exists($key, $this->cache_translations)) {
            return ($number != 1) ? $plural : $single;
        }

        // find out the appropriate form
        $select = $this->select_string($number);

        $result = $this->cache_translations[$key];
        $list = explode(chr(0), $result);
        return $list[$select];
    }

    /**
     * Translate with context
     *
     * @param string $context Context
     * @param string $msgid   String to be translated
     *
     * @return string translated plural form
     */
    public function pgettext($context, $msgid)
    {
        $key = implode(chr(4), array($context, $msgid));
        $ret = $this->gettext($key);
        if (strpos($ret, chr(4)) !== false) {
            return $msgid;
        } else {
            return $ret;
        }
    }

    /**
     * Plural version of pgettext
     *
     * @param string $context Context
     * @param string $single  Single form
     * @param string $plural  Plural form
     * @param string $number  Number of objects
     *
     * @return string translated plural form
     */
    public function npgettext($context, $singular, $plural, $number)
    {
        $key = implode(chr(4), array($context, $singular));
        $ret = $this->ngettext($key, $plural, $number);
        if (strpos($ret, chr(4)) !== false) {
            return $singular;
        } else {
            return $ret;
        }
    }
}
