<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests\Cache;

use PhpMyAdmin\MoTranslator\Cache\InMemoryCache;
use PhpMyAdmin\MoTranslator\MoParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InMemoryCache::class)]
class InMemoryCacheTest extends TestCase
{
    public function testConstructorParsesCache(): void
    {
        $expected = 'Pole';
        $parser = new MoParser(__DIR__ . '/../data/little.mo');
        $cache = new InMemoryCache($parser);
        $actual = $cache->get('Column');
        self::assertSame($expected, $actual);
    }

    public function testGetReturnsMsgidForCacheMiss(): void
    {
        $expected = 'Column';
        $cache = new InMemoryCache(new MoParser(null));
        $actual = $cache->get($expected);
        self::assertSame($expected, $actual);
    }

    public function testSetSetsMsgstr(): void
    {
        $expected = 'Pole';
        $msgid = 'Column';
        $cache = new InMemoryCache(new MoParser(null));
        $cache->set($msgid, $expected);
        $actual = $cache->get($msgid);
        self::assertSame($expected, $actual);
    }

    public function testHasReturnsFalse(): void
    {
        $cache = new InMemoryCache(new MoParser(null));
        $actual = $cache->has('Column');
        self::assertFalse($actual);
    }

    public function testHasReturnsTrue(): void
    {
        $cache = new InMemoryCache(new MoParser(__DIR__ . '/../data/little.mo'));
        $actual = $cache->has('Column');
        self::assertTrue($actual);
    }

    public function testSetAllSetsTranslations(): void
    {
        $translations = [
            'foo' => 'bar',
            'and' => 'another',
        ];
        $cache = new InMemoryCache(new MoParser(null));
        $cache->setAll($translations);
        foreach ($translations as $msgid => $expected) {
            $actual = $cache->get($msgid);
            self::assertSame($expected, $actual);
        }
    }

    public function testGetAllReturnsTranslations(): void
    {
        $expected = [
            'foo' => 'bar',
            'and' => 'another',
        ];
        $cache = new InMemoryCache(new MoParser(null));
        $cache->setAll($expected);
        $actual = $cache->getAll();
        self::assertSame($expected, $actual);
    }
}
