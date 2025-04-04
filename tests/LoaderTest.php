<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests;

use PhpMyAdmin\MoTranslator\Cache\CacheFactoryInterface;
use PhpMyAdmin\MoTranslator\Cache\CacheInterface;
use PhpMyAdmin\MoTranslator\Loader;
use PhpMyAdmin\MoTranslator\MoParser;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

use function getenv;
use function putenv;

/**
 * Test for mo loading.
 */
class LoaderTest extends TestCase
{
    /** @param list<string> $expected */
    #[DataProvider('localeList')]
    public function testListLocales(string $locale, array $expected): void
    {
        self::assertSame($expected, Loader::listLocales($locale));
    }

    /** @return list<array{string, list<string>}> */
    public static function localeList(): array
    {
        return [
            [
                'cs_CZ',
                [
                    'cs_CZ',
                    'cs',
                ],
            ],
            [
                'sr_CS.UTF-8@latin',
                [
                    'sr_CS.UTF-8@latin',
                    'sr_CS@latin',
                    'sr@latin',
                    'sr_CS.UTF-8',
                    'sr_CS',
                    'sr',
                ],
            ],
            // For a locale containing country code, we prefer
            // full locale name, but if that's not found, fall back
            // to the language only locale name.
            [
                'sr_RS',
                [
                    'sr_RS',
                    'sr',
                ],
            ],
            // If language code is used, it's the only thing returned.
            [
                'sr',
                ['sr'],
            ],
            // There is support for language and charset only.
            [
                'sr.UTF-8',
                [
                    'sr.UTF-8',
                    'sr',
                ],
            ],

            // It can also split out character set from the full locale name.
            [
                'sr_RS.UTF-8',
                [
                    'sr_RS.UTF-8',
                    'sr_RS',
                    'sr',
                ],
            ],

            // There is support for @modifier in locale names as well.
            [
                'sr_RS.UTF-8@latin',
                [
                    'sr_RS.UTF-8@latin',
                    'sr_RS@latin',
                    'sr@latin',
                    'sr_RS.UTF-8',
                    'sr_RS',
                    'sr',
                ],
            ],
            [
                'sr.UTF-8@latin',
                [
                    'sr.UTF-8@latin',
                    'sr@latin',
                    'sr.UTF-8',
                    'sr',
                ],
            ],

            // We can pass in only language and modifier.
            [
                'sr@latin',
                [
                    'sr@latin',
                    'sr',
                ],
            ],

            // If locale name is not following the regular POSIX pattern,
            // it's used verbatim.
            [
                'something',
                ['something'],
            ],

            // Passing in an empty string returns an empty array.
            [
                '',
                [],
            ],
        ];
    }

    private function getLoader(string $domain, string $locale): Loader
    {
        $loader = new Loader();
        $loader->setlocale($locale);
        $loader->textdomain($domain);
        $loader->bindtextdomain($domain, __DIR__ . '/data/locale/');

        return $loader;
    }

    public function testLocaleChange(): void
    {
        $loader = new Loader();
        $loader->setlocale('cs');
        $loader->textdomain('phpmyadmin');
        $loader->bindtextdomain('phpmyadmin', __DIR__ . '/data/locale/');
        $translator = $loader->getTranslator('phpmyadmin');
        self::assertSame('Typ', $translator->gettext('Type'));
        $loader->setlocale('be_BY');
        $translator = $loader->getTranslator('phpmyadmin');
        self::assertSame('Тып', $translator->gettext('Type'));
    }

    #[DataProvider('translatorData')]
    public function testGetTranslator(string $domain, string $locale, string $otherdomain, string $expected): void
    {
        $loader = $this->getLoader($domain, $locale);
        $translator = $loader->getTranslator($otherdomain);
        self::assertSame(
            $expected,
            $translator->gettext('Type'),
        );
    }

    /** @return list<array{string, string, string, string}> */
    public static function translatorData(): array
    {
        return [
            [
                'phpmyadmin',
                'cs',
                '',
                'Typ',
            ],
            [
                'phpmyadmin',
                'cs_CZ',
                '',
                'Typ',
            ],
            [
                'phpmyadmin',
                'be_BY',
                '',
                'Тып',
            ],
            [
                'phpmyadmin',
                'be@latin',
                '',
                'Typ',
            ],
            [
                'phpmyadmin',
                'cs',
                'other',
                'Type',
            ],
            [
                'other',
                'cs',
                'phpmyadmin',
                'Type',
            ],
        ];
    }

    public function testInstance(): void
    {
        $loader = Loader::getInstance();
        $loader->setlocale('cs');
        $loader->textdomain('phpmyadmin');
        $loader->bindtextdomain('phpmyadmin', __DIR__ . '/data/locale/');

        $translator = $loader->getTranslator();
        self::assertSame(
            'Typ',
            $translator->gettext('Type'),
        );

        /* Ensure the object survives */
        $loader = Loader::getInstance();
        $translator = $loader->getTranslator();
        self::assertSame(
            'Typ',
            $translator->gettext('Type'),
        );

        /* Ensure the object can support different locale files for the same domain */
        $loader = Loader::getInstance();
        $loader->setlocale('be_BY');
        $loader->bindtextdomain('phpmyadmin', __DIR__ . '/data/locale/');
        $translator = $loader->getTranslator();
        self::assertSame(
            'Тып',
            $translator->gettext('Type'),
        );
    }

    public function testDetect(): void
    {
        $GLOBALS['lang'] = 'foo';
        $loader = Loader::getInstance();
        self::assertSame(
            'foo',
            $loader->detectlocale(),
        );
        unset($GLOBALS['lang']);
    }

    public function testDetectEnv(): void
    {
        $loader = Loader::getInstance();
        foreach (['LC_MESSAGES', 'LC_ALL', 'LANG'] as $var) {
            putenv($var);
            if (getenv($var) === false) {
                continue;
            }

            $this->markTestSkipped('Unsetting environment does not work');
        }

        unset($GLOBALS['lang']);
        putenv('LC_ALL=baz');
        self::assertSame(
            'baz',
            $loader->detectlocale(),
        );
        putenv('LC_ALL');
        putenv('LC_MESSAGES=bar');
        self::assertSame(
            'bar',
            $loader->detectlocale(),
        );
        putenv('LC_MESSAGES');
        putenv('LANG=barr');
        self::assertSame(
            'barr',
            $loader->detectlocale(),
        );
        putenv('LANG');
        self::assertSame(
            'en',
            $loader->detectlocale(),
        );
    }

    public function testSetCacheFactory(): void
    {
        $expected = 'Foo';
        $locale = 'be_BY';
        $domain = 'apcu';

        $cache = $this->createMock(CacheInterface::class);
        $cache->method('get')
            ->willReturn($expected);
        /** @var CacheFactoryInterface&MockObject $factory */
        $factory = $this->createMock(CacheFactoryInterface::class);
        $factory->expects($this->once())
            ->method('getInstance')
            ->with($this->isInstanceOf(MoParser::class), $locale, $domain)
            ->willReturn($cache);

        Loader::setCacheFactory($factory);
        $loader = Loader::getInstance();
        $loader->setlocale($locale);
        $loader->bindtextdomain($domain, __DIR__ . '/data/locale/');
        $translator = $loader->getTranslator($domain);

        $actual = $translator->gettext('Type');
        self::assertSame($expected, $actual);
    }
}
