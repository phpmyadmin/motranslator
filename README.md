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

## Low level API usage

```php
// Load the mo file
$translator = new MoTranslator\MoTranslator('./path/to/file.mo');

// Translate string
echo $translator->gettext('String');

// Translate plural string
echo $translator->ngettext('String', 'Plural string', $count);

// Translate string with context
echo $translator->pgettext('Context', 'String');

// Translate plural string with context
echo $translator->npgettext('Context', 'String', 'Plural string', $count);
```

## History

This library is based on [php-gettext](https://launchpad.net/php-gettext). It
adds some performance improvements and ability to install using [Composer][1].

[1]:https://getcomposer.org/
