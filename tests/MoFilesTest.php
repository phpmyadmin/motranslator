<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests;

use PhpMyAdmin\MoTranslator\Translator;
use PHPUnit\Framework\TestCase;
use function basename;
use function glob;
use function strpos;

/**
 * Test for MO files parsing.
 */
class MoFilesTest extends TestCase
{
    /**
     * @dataProvider provideMoFiles
     */
    public function testMoFileTranslate(string $filename): void
    {
        $parser = new Translator($filename);
        $this->assertEquals(
            'Pole',
            $parser->gettext('Column')
        );
        // Non existing string
        $this->assertEquals(
            'Column parser',
            $parser->gettext('Column parser')
        );
    }

    /**
     * @dataProvider provideMoFiles
     */
    public function testMoFilePlurals(string $filename): void
    {
        $parser = new Translator($filename);
        $expected2 = '%d sekundy';
        if (strpos($filename, 'invalid-formula.mo') !== false || strpos($filename, 'lessplurals.mo') !== false) {
            $expected0 = '%d sekunda';
            $expected2 = '%d sekunda';
        } elseif (strpos($filename, 'plurals.mo') !== false || strpos($filename, 'noheader.mo') !== false) {
            $expected0 = '%d sekundy';
        } else {
            $expected0 = '%d sekund';
        }

        $this->assertEquals(
            $expected0,
            $parser->ngettext(
                '%d second',
                '%d seconds',
                0
            )
        );
        $this->assertEquals(
            '%d sekunda',
            $parser->ngettext(
                '%d second',
                '%d seconds',
                1
            )
        );
        $this->assertEquals(
            $expected2,
            $parser->ngettext(
                '%d second',
                '%d seconds',
                2
            )
        );
        $this->assertEquals(
            $expected0,
            $parser->ngettext(
                '%d second',
                '%d seconds',
                5
            )
        );
        $this->assertEquals(
            $expected0,
            $parser->ngettext(
                '%d second',
                '%d seconds',
                10
            )
        );
        // Non existing string
        $this->assertEquals(
            '"%d" seconds',
            $parser->ngettext(
                '"%d" second',
                '"%d" seconds',
                10
            )
        );
    }

    /**
     * @dataProvider provideMoFiles
     */
    public function testMoFileContext(string $filename): void
    {
        $parser = new Translator($filename);
        $this->assertEquals(
            'Tabulka',
            $parser->pgettext(
                'Display format',
                'Table'
            )
        );
    }

    /**
     * @dataProvider provideNotTranslatedFiles
     */
    public function testMoFileNotTranslated(string $filename): void
    {
        $parser = new Translator($filename);
        $this->assertEquals(
            '%d second',
            $parser->ngettext(
                '%d second',
                '%d seconds',
                1
            )
        );
    }

    /**
     * @return array[]
     */
    public function provideMoFiles(): array
    {
        return $this->getFiles('./tests/data/*.mo');
    }

    /**
     * @return array[]
     */
    public function provideErrorMoFiles(): array
    {
        return $this->getFiles('./tests/data/error/*.mo');
    }

    /**
     * @return array[]
     */
    public function provideNotTranslatedFiles(): array
    {
        return $this->getFiles('./tests/data/not-translated/*.mo');
    }

    /**
     * @dataProvider provideErrorMoFiles
     */
    public function testEmptyMoFile(string $file): void
    {
        $parser = new Translator($file);
        if (basename($file) === 'magic.mo') {
            $this->assertEquals(Translator::ERROR_BAD_MAGIC, $parser->error);
        } else {
            $this->assertEquals(Translator::ERROR_READING, $parser->error);
        }

        $this->assertEquals(
            'Table',
            $parser->pgettext(
                'Display format',
                'Table'
            )
        );
        $this->assertEquals(
            '"%d" seconds',
            $parser->ngettext(
                '"%d" second',
                '"%d" seconds',
                10
            )
        );
    }

    /**
     * @dataProvider provideMoFiles
     */
    public function testExists(string $file): void
    {
        $parser = new Translator($file);
        $this->assertEquals(
            true,
            $parser->exists('Column')
        );
        $this->assertEquals(
            false,
            $parser->exists('Column parser')
        );
    }

    /**
     * @param string $pattern path names pattern to match
     *
     * @return array[]
     */
    private function getFiles(string $pattern): array
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
}
