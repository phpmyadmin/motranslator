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
     * @var Public variable that holds error code (0 if no error)
     */
    public $error = 0;

    //private:
    private $short_circuit = false;
    private $pluralheader = NULL;    // cache header field for plural forms
    private $cache_translations = NULL;  // original -> translation mapping


  /**
   * Constructor
   *
   * @param string $filename Name of mo file to load
   */
  public function __construct($filename) {

    if (! is_readable($filename)) {
      $this->error = 2; // file does not exist
      $this->short_circuit = true;
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
      $this->short_circuit = true;
      return;
    }

    $total = $stream->readint($unpack, 8);
    $originals = $stream->readint($unpack, 12);
    $translations = $stream->readint($unpack, 16);

    /* get original and translations tables */
      $table_originals = $stream->readintarray($unpack, $originals, $total * 2);
      $table_translations = $stream->readintarray($unpack, $translations, $total * 2);

      $this->cache_translations = array ();
      /* read all strings in the cache */
      for ($i = 0; $i < $total; $i++) {
        $original = $stream->read($table_originals[$i * 2 + 2], $table_originals[$i * 2 + 1]);
        $translation = $stream->read($table_translations[$i * 2 + 2], $table_translations[$i * 2 + 1]);
        $this->cache_translations[$original] = $translation;
      }
  }

  /**
   * Translates a string
   *
   * @param string string to be translated
   * @return string translated string (or original, if not found)
   */
  public function translate($string) {
    if ($this->short_circuit)
      return $string;

      // Caching enabled, get translated string from cache
      if (array_key_exists($string, $this->cache_translations))
        return $this->cache_translations[$string];
      else
        return $string;
  }

  /**
   * Sanitize plural form expression for use in PHP eval call.
   *
   * @return string sanitized plural form expression
   */
  private function sanitize_plural_expression($expr) {
    // Get rid of disallowed characters.
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
    return $res;
  }

  /**
   * Parse full PO header and extract only plural forms line.
   *
   * @return string verbatim plural form header field
   */
  public static function extract_plural_forms_header_from_po_header($header) {
    if (preg_match("/(^|\n)plural-forms: ([^\n]*)\n/i", $header, $regs))
      $expr = $regs[2];
    else
      $expr = "nplurals=2; plural=n == 1 ? 0 : 1;";
    return $expr;
  }

  /**
   * Get possible plural forms from MO header
   *
   * @return string plural form header
   */
  private function get_plural_forms() {
    // lets assume message number 0 is header
    // this is true, right?

    // cache header field for plural forms
    if (! is_string($this->pluralheader)) {
        $header = $this->cache_translations[""];
      $expr = $this->extract_plural_forms_header_from_po_header($header);
      $this->pluralheader = $this->sanitize_plural_expression($expr);
    }
    return $this->pluralheader;
  }

  /**
   * Detects which plural form to take
   *
   * @param n count
   * @return int array index of the right plural form
   */
  private function select_string($n) {
    $string = $this->get_plural_forms();
    $string = str_replace('nplurals',"\$total",$string);
    $string = str_replace("n",$n,$string);
    $string = str_replace('plural',"\$plural",$string);

    $total = 0;
    $plural = 0;

    eval("$string");
    if ($plural >= $total) $plural = $total - 1;
    return $plural;
  }

  /**
   * Plural version of gettext
   *
   * @param string single
   * @param string plural
   * @param string number
   * @return translated plural form
   */
  public function ngettext($single, $plural, $number) {
    if ($this->short_circuit) {
      if ($number != 1)
        return $plural;
      else
        return $single;
    }

    // find out the appropriate form
    $select = $this->select_string($number);

    // this should contains all strings separated by NULLs
    $key = $single . chr(0) . $plural;


      if (! array_key_exists($key, $this->cache_translations)) {
        return ($number != 1) ? $plural : $single;
      } else {
        $result = $this->cache_translations[$key];
        $list = explode(chr(0), $result);
        return $list[$select];
      }
  }

  public function pgettext($context, $msgid) {
    $key = $context . chr(4) . $msgid;
    $ret = $this->translate($key);
    if (strpos($ret, chr(4)) !== FALSE) {
      return $msgid;
    } else {
      return $ret;
    }
  }

  public function npgettext($context, $singular, $plural, $number) {
    $key = $context . chr(4) . $singular;
    $ret = $this->ngettext($key, $plural, $number);
    if (strpos($ret, chr(4)) !== FALSE) {
      return $singular;
    } else {
      return $ret;
    }

  }
}

