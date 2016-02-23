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

namespace MoTranslator;

class MoLoader {
    /**
     * Figure out all possible locale names and start with the most
     * specific ones.  I.e. for sr_CS.UTF-8@latin, look through all of
     * sr_CS.UTF-8@latin, sr_CS@latin, sr@latin, sr_CS.UTF-8, sr_CS, sr.
     *
     * @param string $locale Locale code
     *
     * @return array list of locales to try for any POSIX-style locale specification.
     */
    public function list_locales($locale) {
        $locale_names = array();

        $lang = NULL;
        $country = NULL;
        $charset = NULL;
        $modifier = NULL;

        if ($locale) {
            if (preg_match("/^(?P<lang>[a-z]{2,3})"              // language code
                ."(?:_(?P<country>[A-Z]{2}))?"           // country code
                ."(?:\.(?P<charset>[-A-Za-z0-9_]+))?"    // charset
                ."(?:@(?P<modifier>[-A-Za-z0-9_]+))?$/",  // @ modifier
                $locale, $matches)) {

                extract($matches);

                if ($modifier) {
                    if ($country) {
                        if ($charset)
                            array_push($locale_names, "${lang}_$country.$charset@$modifier");
                        array_push($locale_names, "${lang}_$country@$modifier");
                    } elseif ($charset)
                        array_push($locale_names, "${lang}.$charset@$modifier");
                        array_push($locale_names, "$lang@$modifier");
                    }
                if ($country) {
                    if ($charset)
                        array_push($locale_names, "${lang}_$country.$charset");
                    array_push($locale_names, "${lang}_$country");
                } elseif ($charset)
                    array_push($locale_names, "${lang}.$charset");
                array_push($locale_names, $lang);
            }

            // If the locale name doesn't match POSIX style, just include it as-is.
            if (!in_array($locale, $locale_names))
            array_push($locale_names, $locale);
        }
        return $locale_names;
    }
}
