# motranslator

PHP Gettext mo files translator

[![Build Status](https://travis-ci.org/phpmyadmin/motranslator.svg?branch=master)](https://travis-ci.org/phpmyadmin/motranslator)
[![codecov.io](https://codecov.io/github/phpmyadmin/motranslator/coverage.svg?branch=master)](https://codecov.io/github/phpmyadmin/motranslator?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/phpmyadmin/motranslator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/phpmyadmin/motranslator/?branch=master)

## Usage

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

## Performance

This library was tweaked for best performance for single use - translating
application with many strings using mo file.

Current benchmarks show it's about four times faster than original php-gettext.

The performance improvements based on individual changes in the code:

| Stage          | Seconds         |
| -------------- | --------------- |
| Original code  | 4.7929680347443 |
| Remove nocache | 4.6308250427246 |
| Direct endian  | 4.5883052349091 |
| Remove attribs | 4.5297479629517 |
| String reader  | 1.8148958683014 |
| No offset      | 1.2436759471893 |
| Less attribs   | 1.1722540855408 |
| Remove shift   | 1.0970499515533 |
| Magic order    | 1.0868430137634 |
