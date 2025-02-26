<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests;

use PhpMyAdmin\MoTranslator\Cache\InMemoryCache;
use PhpMyAdmin\MoTranslator\MoParser;
use PhpMyAdmin\MoTranslator\Translator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

use function basename;
use function glob;
use function str_contains;

/**
 * Test for MO files parsing.
 */
class MoFilesTest extends TestCase
{
    #[DataProvider('provideMoFiles')]
    public function testMoFileTranslate(string $filename): void
    {
        $parser = $this->getTranslator($filename);
        self::assertSame(
            'Pole',
            $parser->gettext('Column'),
        );
        // Non existing string
        self::assertSame(
            'Column parser',
            $parser->gettext('Column parser'),
        );
    }

    #[DataProvider('provideMoFiles')]
    public function testMoFilePlurals(string $filename): void
    {
        $parser = $this->getTranslator($filename);
        $expected2 = '%d sekundy';
        if (str_contains($filename, 'invalid-formula.mo') || str_contains($filename, 'lessplurals.mo')) {
            $expected0 = '%d sekunda';
            $expected2 = '%d sekunda';
        } elseif (str_contains($filename, 'plurals.mo') || str_contains($filename, 'noheader.mo')) {
            $expected0 = '%d sekundy';
        } else {
            $expected0 = '%d sekund';
        }

        self::assertSame($expected0, $parser->ngettext('%d second', '%d seconds', 0));
        self::assertSame('%d sekunda', $parser->ngettext('%d second', '%d seconds', 1));
        self::assertSame($expected2, $parser->ngettext('%d second', '%d seconds', 2));
        self::assertSame($expected0, $parser->ngettext('%d second', '%d seconds', 5));
        self::assertSame($expected0, $parser->ngettext('%d second', '%d seconds', 10));
        // Non existing string
        self::assertSame('"%d" seconds', $parser->ngettext('"%d" second', '"%d" seconds', 10));
    }

    #[DataProvider('provideMoFiles')]
    public function testMoFileContext(string $filename): void
    {
        $parser = $this->getTranslator($filename);
        self::assertSame('Tabulka', $parser->pgettext('Display format', 'Table'));
    }

    #[DataProvider('provideNotTranslatedFiles')]
    public function testMoFileNotTranslated(string $filename): void
    {
        $parser = $this->getTranslator($filename);
        self::assertSame('%d second', $parser->ngettext('%d second', '%d seconds', 1));
    }

    /** @return list<array{string}> */
    public static function provideMoFiles(): array
    {
        return self::getFiles('./tests/data/*.mo');
    }

    /** @return list<array{string}> */
    public static function provideErrorMoFiles(): array
    {
        return self::getFiles('./tests/data/error/*.mo');
    }

    /** @return list<array{string}> */
    public static function provideNotTranslatedFiles(): array
    {
        return self::getFiles('./tests/data/not-translated/*.mo');
    }

    #[DataProvider('provideErrorMoFiles')]
    public function testEmptyMoFile(string $file): void
    {
        $parser = new MoParser($file);
        $translator = new Translator(new InMemoryCache($parser));
        if (basename($file) === 'magic.mo') {
            self::assertSame(Translator::ERROR_BAD_MAGIC, $parser->error);
        } else {
            self::assertSame(Translator::ERROR_READING, $parser->error);
        }

        self::assertSame('Table', $translator->pgettext('Display format', 'Table'));
        self::assertSame('"%d" seconds', $translator->ngettext('"%d" second', '"%d" seconds', 10));
    }

    #[DataProvider('provideMoFiles')]
    public function testExists(string $file): void
    {
        $parser = $this->getTranslator($file);
        self::assertTrue($parser->exists('Column'));
        self::assertFalse($parser->exists('Column parser'));
    }

    /**
     * @param string $pattern path names pattern to match
     *
     * @return list<array{string}>
     */
    private static function getFiles(string $pattern): array
    {
        $files = glob($pattern);
        if ($files === false) {
            return [];
        }

        $result = [];
        foreach ($files as $file) {
            $result[] = [$file];
        }

        return $result;
    }

    private function getTranslator(string $filename): Translator
    {
        return new Translator(new InMemoryCache(new MoParser($filename)));
    }
}
