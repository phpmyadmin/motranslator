# motranslator

PHP Gettext mo files translator

[![Build Status](https://travis-ci.org/nijel/motranslator.svg?branch=master)](https://travis-ci.org/nijel/motranslator)
[![codecov.io](https://codecov.io/github/nijel/motranslator/coverage.svg?branch=master)](https://codecov.io/github/nijel/motranslator?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/nijel/motranslator/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/nijel/motranslator/?branch=master)

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
