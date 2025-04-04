<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests;

use PhpMyAdmin\MoTranslator\Cache\CacheInterface;
use PhpMyAdmin\MoTranslator\Cache\InMemoryCache;
use PhpMyAdmin\MoTranslator\CacheException;
use PhpMyAdmin\MoTranslator\MoParser;
use PhpMyAdmin\MoTranslator\Translator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for translator API
 */
class TranslatorTest extends TestCase
{
    public function testConstructorWithFilenameParam(): void
    {
        $expected = 'Pole';
        $translator = new Translator(__DIR__ . '/data/little.mo');
        $actual = $translator->gettext('Column');
        self::assertSame($expected, $actual);
    }

    public function testConstructorWithNullParam(): void
    {
        $expected = 'Column';
        $translator = new Translator(null);
        $actual = $translator->gettext($expected);
        self::assertSame($expected, $actual);
    }

    /**
     * Test on empty gettext
     */
    public function testGettext(): void
    {
        $translator = $this->getTranslator('');
        self::assertSame('Test', $translator->gettext('Test'));
    }

    /**
     * Test set a translation
     */
    public function testSetTranslation(): void
    {
        $translator = $this->getTranslator('');
        $translator->setTranslation('Test', 'Translation');
        self::assertSame('Translation', $translator->gettext('Test'));
    }

    /**
     * Test get and set all translations
     */
    public function testGetSetTranslations(): void
    {
        $transTable = ['Test' => 'Translation'];
        $translator = $this->getTranslator('');
        $translator->setTranslations($transTable);
        self::assertSame('Translation', $translator->gettext('Test'));
        self::assertSame($transTable, $translator->getTranslations());
        $translator = $this->getTranslator(null);
        $translator->setTranslations($transTable);
        self::assertSame($transTable, $translator->getTranslations());
        self::assertSame('Translation', $translator->gettext('Test'));
        $transTable = [
            'Test' => 'Translation',
            'shouldIWriteTests' => 'as much as possible',
            'is it hard' => 'it depends',
        ];
        $translator = $this->getTranslator('');
        $translator->setTranslations($transTable);
        self::assertSame($transTable, $translator->getTranslations());
        self::assertSame('as much as possible', $translator->gettext('shouldIWriteTests'));
        $translator = $this->getTranslator(null);
        $translator->setTranslations($transTable);
        self::assertSame($transTable, $translator->getTranslations());
        self::assertSame('it depends', $translator->gettext('is it hard'));
    }

    public function testGetTranslationsThrowsException(): void
    {
        /** @var CacheInterface&MockObject $cache */
        $cache = $this->createMock(CacheInterface::class);
        $translator = new Translator($cache);

        $this->expectException(CacheException::class);
        $translator->getTranslations();
    }

    private function getTranslator(string|null $filename): Translator
    {
        return new Translator(new InMemoryCache(new MoParser($filename)));
    }
}
