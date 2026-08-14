<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests\Cache;

use PhpMyAdmin\MoTranslator\Cache\ApcuCache;
use PhpMyAdmin\MoTranslator\Cache\ApcuCacheFactory;
use PhpMyAdmin\MoTranslator\MoParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function apcu_clear_cache;
use function apcu_delete;
use function apcu_enabled;
use function apcu_fetch;
use function function_exists;
use function sleep;

#[CoversClass(ApcuCacheFactory::class)]
#[RequiresPhpExtension('apcu')]
class ApcuCacheFactoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (apcu_enabled()) {
            return;
        }

        $this->markTestSkipped('The APCu extension is not enabled for the CLI (apc.enable_cli=1)');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        if (! function_exists('apcu_clear_cache')) {
            return;
        }

        apcu_clear_cache();
    }

    public function testGetInstanceReturnApcuCache(): void
    {
        $factory = new ApcuCacheFactory();
        $instance = $factory->getInstance(new MoParser(null), 'foo', 'bar');
        self::assertInstanceOf(ApcuCache::class, $instance);
    }

    public function testConstructorSetsTtl(): void
    {
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';
        $ttl = 1;

        $factory = new ApcuCacheFactory($ttl);
        $parser = new MoParser(__DIR__ . '/../data/little.mo');
        $factory->getInstance($parser, $locale, $domain);
        sleep($ttl * 2);

        apcu_fetch('mo_' . $locale . '.' . $domain . '.' . $msgid, $success);
        self::assertFalse($success);
    }

    public function testConstructorSetsReloadOnMiss(): void
    {
        $expected = 'Column';
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';

        $factory = new ApcuCacheFactory(0, false);
        $parser = new MoParser(__DIR__ . '/../data/little.mo');

        $instance = $factory->getInstance($parser, $locale, $domain);

        apcu_delete('mo_' . $locale . '.' . $domain . '.' . $msgid);
        $actual = $instance->get($msgid);
        self::assertSame($expected, $actual);
    }

    public function testConstructorSetsPrefix(): void
    {
        $expected = 'Pole';
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';
        $prefix = 'baz_';

        $factory = new ApcuCacheFactory(0, true, $prefix);
        $parser = new MoParser(__DIR__ . '/../data/little.mo');

        $factory->getInstance($parser, $locale, $domain);

        $actual = apcu_fetch($prefix . $locale . '.' . $domain . '.' . $msgid);
        self::assertSame($expected, $actual);
    }
}
