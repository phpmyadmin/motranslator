<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests\Cache;

use PhpMyAdmin\MoTranslator\Cache\Psr6Cache;
use PhpMyAdmin\MoTranslator\MoParser;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\ArrayAdapter;

use function chr;
use function explode;
use function implode;
use function md5;
use function sleep;

/**
 * @covers \PhpMyAdmin\MoTranslator\Cache\Psr6Cache
 */
class Psr6CacheTest extends TestCase
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

    public function testConstructorLoadsCache(): void
    {
        $expected = 'Pole';
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';

        new Psr6Cache($this->psr6Cache, new MoParser(__DIR__ . '/../data/little.mo'), $locale, $domain);

        $actual = $this->psr6Cache->getItem('mo_' . $locale . '.' . $domain . '.' . md5($msgid))->get();
        $this->assertSame($expected, $actual);
    }

    public function testConstructorSetsTtl(): void
    {
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';
        $ttl = 1;

        new Psr6Cache($this->psr6Cache, new MoParser(__DIR__ . '/../data/little.mo'), $locale, $domain, $ttl);

        sleep($ttl * 2);

        $cacheItem = $this->psr6Cache->getItem('mo_' . $locale . '.' . $domain . '.' . md5($msgid));
        $this->assertFalse($cacheItem->isHit());

        $cacheItem = $this->psr6Cache->getItem('mo_' . $locale . '.' . $domain . '.' . md5(Psr6Cache::LOADED_KEY));
        $this->assertFalse($cacheItem->isHit());
    }

    public function testConstructorSetsReloadOnMiss(): void
    {
        $expected = 'Column';
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';
        $prefix = 'baz_';

        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(__DIR__ . '/../data/little.mo'),
            $locale,
            $domain,
            0,
            false,
            $prefix
        );

        $this->psr6Cache->deleteItem($prefix . $locale . '.' . $domain . '.' . md5($msgid));

        $actual = $cache->get($msgid);
        $this->assertEquals($expected, $actual);
    }

    public function testConstructorSetsPrefix(): void
    {
        $expected = 'Pole';
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';
        $prefix = 'baz_';

        new Psr6Cache(
            $this->psr6Cache,
            new MoParser(__DIR__ . '/../data/little.mo'),
            $locale,
            $domain,
            0,
            true,
            $prefix
        );

        $actual = $this->psr6Cache->getItem($prefix . $locale . '.' . $domain . '.' . md5($msgid))->get();
        $this->assertSame($expected, $actual);
    }

    public function testEnsureTranslationsLoadedSetsLoadedKey(): void
    {
        $expected = 1;
        $locale = 'foo';
        $domain = 'bar';

        new Psr6Cache(
            $this->psr6Cache,
            new MoParser(__DIR__ . '/../data/little.mo'),
            $locale,
            $domain
        );

        $actual = $this->psr6Cache->getItem('mo_' . $locale . '.' . $domain . '.' . md5(Psr6Cache::LOADED_KEY))->get();
        $this->assertSame($expected, $actual);
    }

    public function testGetReturnsMsgstr(): void
    {
        $expected = 'Pole';
        $msgid = 'Column';

        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(__DIR__ . '/../data/little.mo'),
            'foo',
            'bar'
        );

        $actual = $cache->get($msgid);
        $this->assertSame($expected, $actual);
    }

    public function testGetReturnsMsgidForCacheMiss(): void
    {
        $expected = 'Column';

        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(null),
            'foo',
            'bar'
        );

        $actual = $cache->get($expected);
        $this->assertSame($expected, $actual);
    }

    public function testStoresMsgidOnCacheMiss(): void
    {
        $expected = 'Column';
        $locale = 'foo';
        $domain = 'bar';

        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(null),
            $locale,
            $domain
        );
        $cache->get($expected);

        $actual = $this->psr6Cache->getItem('mo_' . $locale . '.' . $domain . '.' . md5($expected))->get();
        $this->assertSame($expected, $actual);
    }

    public function testGetReloadsOnCacheMiss(): void
    {
        $expected = 'Pole';
        $locale = 'foo';
        $domain = 'bar';
        $msgid = 'Column';

        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(__DIR__ . '/../data/little.mo'),
            $locale,
            $domain
        );

        $this->psr6Cache->deleteItem('mo_' . $locale . '.' . $domain . '.' . md5(Psr6Cache::LOADED_KEY));

        $actual = $cache->get($msgid);
        $this->assertSame($expected, $actual);
    }

    public function testSetSetsMsgstr(): void
    {
        $expected = 'Pole';
        $msgid = 'Column';

        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(null),
            'foo',
            'bar'
        );
        $cache->set($msgid, $expected);

        $actual = $cache->get($msgid);
        $this->assertSame($expected, $actual);
    }

    public function testHasReturnsFalse(): void
    {
        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(null),
            'foo',
            'bar'
        );

        $actual = $cache->has('Column');
        $this->assertFalse($actual);
    }

    public function testHasReturnsTrue(): void
    {
        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(__DIR__ . '/../data/little.mo'),
            'foo',
            'bar'
        );

        $actual = $cache->has('Column');
        $this->assertTrue($actual);
    }

    public function testSetAllSetsTranslations(): void
    {
        $translations = [
            'foo' => 'bar',
            'and' => 'another',
        ];

        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(null),
            'foo',
            'bar'
        );
        $cache->setAll($translations);

        foreach ($translations as $msgid => $expected) {
            $actual = $cache->get($msgid);
            $this->assertEquals($expected, $actual);
        }
    }

    public function testCacheStoresPluralForms(): void
    {
        $expected = ['first', 'second'];

        $plural = ["%d pig went to the market\n", "%d pigs went to the market\n"];
        $msgid = implode(chr(0), $plural);

        $cache = new Psr6Cache(
            $this->psr6Cache,
            new MoParser(null),
            'foo',
            'bar'
        );
        $cache->set($msgid, implode(chr(0), $expected));

        $msgstr = $cache->get($msgid);
        $actual = explode(chr(0), $msgstr);
        $this->assertSame($expected, $actual);
    }
}
