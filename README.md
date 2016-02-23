# motranslator

Traslation API for PHP using Gettext MO files.

[![Build Status](https://travis-ci.org/phpmyadmin/motranslator.svg?branch=master)](https://travis-ci.org/phpmyadmin/motranslator)
[![codecov.io](https://codecov.io/github/phpmyadmin/motranslator/coverage.svg?branch=master)](https://codecov.io/github/phpmyadmin/motranslator?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpmyadmin/motranslator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpmyadmin/motranslator/?branch=master)
[![Packagist](https://img.shields.io/packagist/dt/phpmyadmin/motranslator.svg)](https://packagist.org/packages/phpmyadmin/motranslator)

## Features

* All strings are stored in memory for fast lookup
* Fast loading of MO files
* Low level API for reading MO files

## Limitations

* Not suitable for huge MO files which you don't want to store in memory
* Input and output encoding has to match (preferably UTF-8)

## Installation

Please use [Composer][1] to install:

```
composer require phpmyadmin/motranslator
```

## Object API usage

```php
// Create loader object
$loader = new Translator\Loader();

// Set locale
$loader->setlocale('cs');

// Set default text domain
$loader->textdomain('domain');

// Set path where to look for a domain
$loader->bindtextdomain('domain', __DIR__ . '/data/locale/');

// Get translator
$translator = $loader->get_translator();

// Now you can use Translator API (see below)
```

## Low level API usage

```php
// Directly load the mo file
$translator = new Translator\Translator('./path/to/file.mo');

// Now you can use Translator API (see below)
```

## Translator API usage

```php
// Translate string
echo $translator->gettext('String');

// Translate plural string
echo $translator->ngettext('String', 'Plural string', $count);

// Translate string with context
echo $translator->pgettext('Context', 'String');

// Translate plural string with context
echo $translator->npgettext('Context', 'String', 'Plural string', $count);
```

## Gettext compatibility usage

```php
// Load compatibility layer
Translator\Loader::load_functions();

// Configure
_setlocale(LC_MESSAGES, 'cs');
_textdomain('phpmyadmin');
_bindtextdomain('phpmyadmin', __DIR__ . '/data/locale/');
_bind_textdomain_codeset('phpmyadmin', 'UTF-8');

// Use functions
echo _gettext('Type');
echo __('Type');
```

## History

This library is based on [php-gettext](https://launchpad.net/php-gettext). It
adds some performance improvements and ability to install using [Composer][1].

[1]:https://getcomposer.org/
