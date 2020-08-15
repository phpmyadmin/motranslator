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
     * Test on empty gettext
     */
    public function testSetTranslation(): void
    {
        $translator = new Translator('');
        $translator->setTranslation('Test', 'Translation');
        $this->assertEquals('Translation', $translator->gettext('Test'));
    }
}
