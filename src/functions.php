<?php
/*
    Copyright (c) 2005 Steven Armstrong <sa at c-area dot ch>
    Copyright (c) 2009 Danilo Segan <danilo@kvota.net>
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

use MoTranslator\MoLoader;

/**
 * Sets a requested locale, if needed emulates it.
 */
function _setlocale($category, $locale)
{
    return MoLoader::getInstance()->setlocale($locale);
}

/**
 * Sets the path for a domain.
 */
function _bindtextdomain($domain, $path)
{
    MoLoader::getInstance()->bindtextdomain($domain, $path);
}

/**
 * Specify the character encoding in which the messages from the DOMAIN message catalog will be returned.
 */
function _bind_textdomain_codeset($domain, $codeset) {
    return;
}

/**
 * Sets the default domain.
 */
function _textdomain($domain)
{
    MoLoader::getInstance()->textdomain($domain);
}

/**
 * Translates a string
 *
 * @param string $msgid String to be translated
 *
 * @return string translated string (or original, if not found)
 */
function _gettext($msgid)
{
    return MoLoader::getInstance()->get_translator()->gettext(
        $msgid
    );
}

/**
 * Translates a string, alias for _gettext
 *
 * @param string $msgid String to be translated
 *
 * @return string translated string (or original, if not found)
 */
function __($msgid)
{
    return MoLoader::getInstance()->get_translator()->gettext(
        $msgid
    );
}

/**
 * Plural version of gettext
 *
 * @param string $msgid        Single form
 * @param string $msgid_plural Plural form
 * @param string $number       Number of objects
 *
 * @return string translated plural form
 */
function _ngettext($msgid, $msgid_plural, $number)
{
    return MoLoader::getInstance()->get_translator()->ngettext(
        $msgid, $msgid_plural, $number
    );
}

/**
 * Translate with context
 *
 * @param string $msgctxt      Context
 * @param string $msgid        String to be translated
 *
 * @return string translated plural form
 */
function _pgettext($msgctxt, $msgid)
{
    return MoLoader::getInstance()->get_translator()->pgettext(
        $msgctxt, $msgid
    );
}

/**
 * Plural version of pgettext
 *
 * @param string $msgctxt      Context
 * @param string $msgid        Single form
 * @param string $msgid_plural Plural form
 * @param string $number       Number of objects
 *
 * @return string translated plural form
 */
function _npgettext($msgctxt, $msgid, $msgid_plural, $number)
{
    return MoLoader::getInstance()->get_translator()->npgettext(
        $msgctxt, $msgid, $msgid_plural, $number
    );
}

/**
 * Translates a string
 *
 * @param string $domain Domain to use
 * @param string $msgid  String to be translated
 *
 * @return string translated string (or original, if not found)
 */
function _dgettext($domain, $msgid)
{
    return MoLoader::getInstance()->get_translator($domain)->gettext(
        $msgid
    );
}

/**
 * Plural version of gettext
 *
 * @param string $domain       Domain to use
 * @param string $msgid        Single form
 * @param string $msgid_plural Plural form
 * @param string $number       Number of objects
 *
 * @return string translated plural form
 */
function _dngettext($domain, $msgid, $msgid_plural, $number)
{
    return MoLoader::getInstance()->get_translator($domain)->ngettext(
        $msgid, $msgid_plural, $number
    );
}

/**
 * Translate with context
 *
 * @param string $domain  Domain to use
 * @param string $msgctxt Context
 * @param string $msgid   String to be translated
 *
 * @return string translated plural form
 */
function _dpgettext($domain, $msgctxt, $msgid)
{
    return MoLoader::getInstance()->get_translator($domain)->pgettext(
        $msgctxt, $msgid
    );
}

/**
 * Plural version of pgettext
 *
 * @param string $domain       Domain to use
 * @param string $msgctxt      Context
 * @param string $msgid        Single form
 * @param string $msgid_plural Plural form
 * @param string $number       Number of objects
 *
 * @return string translated plural form
 */
function _dnpgettext($domain, $msgctxt, $msgid, $msgid_plural, $number)
{
    return MoLoader::getInstance()->get_translator($domain)->npgettext(
        $msgctxt, $msgid, $msgid_plural, $number
    );
}
