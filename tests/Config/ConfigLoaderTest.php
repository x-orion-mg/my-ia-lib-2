<?php
declare(strict_types=1);

namespace MyAILib\Tests\Config;

use InvalidArgumentException;
use MyAILib\Config\ConfigLoader;
use PHPUnit\Framework\TestCase;

final class ConfigLoaderTest extends TestCase
{
    public function testCanLoadConfigurationFromArray(): void
    {
        $config = ConfigLoader::fromArray([
            'providers' => [
                'fake' => [
                    'api_key' => 'test-key',
                ],
            ],
        ]);

        $this->assertTrue(
            $config->hasProvider('fake')
        );

        $this->assertSame(
            'test-key',
            $config->provider('fake')['api_key']
        );
    }

    public function testCanLoadConfigurationFromFile(): void
    {
        $file = dirname(__DIR__)
            . '/fixtures/ai.php';

        $config = ConfigLoader::fromFile($file);

        $this->assertTrue(
            $config->hasProvider('openrouter')
        );

        $this->assertSame(
            'test-model',
            $config->provider('openrouter')['model']
        );
    }

    public function testThrowsExceptionWhenFileDoesNotExist(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        ConfigLoader::fromFile(
            __DIR__ . '/does-not-exist.php'
        );
    }

    public function testThrowsExceptionWhenFileDoesNotReturnArray(): void
    {
        $file = dirname(__DIR__)
            . '/fixtures/invalid-config.php';

        file_put_contents(
            $file,
            '<?php return "invalid";'
        );

        try {
            $this->expectException(
                InvalidArgumentException::class
            );

            ConfigLoader::fromFile($file);
        } finally {
            @unlink($file);
        }
    }
}
