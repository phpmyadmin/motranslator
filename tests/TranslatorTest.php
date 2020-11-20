<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests;

use PhpMyAdmin\MoTranslator\Translator;
use PHPUnit\Framework\TestCase;

/**
 * Test for translator API
 */
class TranslatorTest extends TestCase
{
    /**
     * Test on empty gettext
     */
    public function testGettext(): void
    {
        $translator = new Translator('');
        $this->assertEquals('Test', $translator->gettext('Test'));
    }

    /**
     * Test set a translation
     */
    public function testSetTranslation(): void
    {
        $translator = new Translator('');
        $translator->setTranslation('Test', 'Translation');
        $this->assertEquals('Translation', $translator->gettext('Test'));
    }

    /**
     * Test get and set all translations
     */
    public function testGetSetTranslations(): void
    {
        $transTable = ['Test' => 'Translation'];
        $translator = new Translator('');
        $translator->setTranslations($transTable);
        $this->assertEquals('Translation', $translator->gettext('Test'));
        $this->assertSame($transTable, $translator->getTranslations());
        $translator = new Translator(null);
        $translator->setTranslations($transTable);
        $this->assertSame($transTable, $translator->getTranslations());
        $this->assertEquals('Translation', $translator->gettext('Test'));
        $transTable = [
            'Test' => 'Translation',
            'shouldIWriteTests' => 'as much as possible',
            'is it hard' => 'it depends',
        ];
        $translator = new Translator('');
        $translator->setTranslations($transTable);
        $this->assertSame($transTable, $translator->getTranslations());
        $this->assertEquals('as much as possible', $translator->gettext('shouldIWriteTests'));
        $translator = new Translator(null);
        $translator->setTranslations($transTable);
        $this->assertSame($transTable, $translator->getTranslations());
        $this->assertEquals('it depends', $translator->gettext('is it hard'));
    }
}
