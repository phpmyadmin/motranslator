<?php

declare(strict_types=1);

namespace PhpMyAdmin\MoTranslator\Tests\Cache;

use PhpMyAdmin\MoTranslator\Cache\ApcuCache;
use PhpMyAdmin\MoTranslator\CacheException;
use PhpMyAdmin\MoTranslator\MoParser;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;

use function apcu_enabled;
use function function_exists;

final class ApcuDisabledTest extends TestCase
{
    public function testConstructorApcuNotInstalledThrowsException(): void
    {
        if (function_exists('apcu_enabled')) {
            self::markTestSkipped('ext-apcu is installed.');
        }

        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('The APCu extension must be installed.');
        new ApcuCache(new MoParser(null), 'foo', 'bar');
    }

    #[RequiresPhpExtension('apcu')]
    public function testConstructorApcuNotEnabledThrowsException(): void
    {
        if (function_exists('apcu_enabled') && apcu_enabled()) {
            self::markTestSkipped('ext-apcu is enabled');
        }

        $this->expectException(CacheException::class);
        $this->expectExceptionMessage('The APCu extension must be enabled (apc.enabled=1) or (apc.enable_cli=1)');
        new ApcuCache(new MoParser(null), 'foo', 'bar');
    }
}
