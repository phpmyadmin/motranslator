<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests\Cache;

use PhpMyAdmin\MoTranslator\Cache\Psr6Cache;
use PhpMyAdmin\MoTranslator\Cache\Psr6CacheFactory;
use PhpMyAdmin\MoTranslator\MoParser;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

use function md5;
use function sleep;

/**
 * @covers \PhpMyAdmin\MoTranslator\Cache\Psr6CacheFactory
 */
class Psr6CacheFactoryTest extends TestCase
{
    /** @var CacheItemPoolInterface */
    protected $psr6Cache;

    protected function setUp(): void
    {
        parent::setUp();

        $this->psr6Cache = new ArrayAdapter();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->psr6Cache->clear();
    }

    public function testGetInstanceReturnsPsr6Cache(): void
    {
        $factory = new Psr6CacheFactory($this->psr6Cache);
        $instance = $factory->getInstance(new MoParser(null), 'foo', 'bar');
        $this->assertInstanceOf(Psr6Cache::class, $instance);
    }

    public function testConstructorSetsTtl(): void
    {
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';
        $ttl = 1;

        $factory = new Psr6CacheFactory($this->psr6Cache, $ttl);

        $parser = new MoParser(__DIR__ . '/../data/little.mo');
        $factory->getInstance($parser, $locale, $domain);

        sleep($ttl * 2);

        $cacheItem = $this->psr6Cache->getItem('mo_' . $locale . '.' . $domain . '.' . md5($msgid));
        $this->assertFalse($cacheItem->isHit());
    }

    public function testConstructorSetsReloadOnMiss(): void
    {
        $expected = 'Column';
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';

        $factory = new Psr6CacheFactory($this->psr6Cache, 0, false);
        $parser = new MoParser(__DIR__ . '/../data/little.mo');

        $instance = $factory->getInstance($parser, $locale, $domain);

        $this->psr6Cache->deleteItem('mo_' . $locale . '.' . $domain . '.' . md5($msgid));

        $actual = $instance->get($msgid);
        $this->assertSame($expected, $actual);
    }

    public function testConstructorSetsPrefix(): void
    {
        $expected = 'Pole';
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';
        $prefix = 'baz_';

        $factory = new Psr6CacheFactory($this->psr6Cache, 0, true, $prefix);
        $parser = new MoParser(__DIR__ . '/../data/little.mo');

        $factory->getInstance($parser, $locale, $domain);

        $actual = $this->psr6Cache->getItem($prefix . $locale . '.' . $domain . '.' . md5($msgid))->get();
        $this->assertSame($expected, $actual);
    }
}
