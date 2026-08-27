<?php


declare(strict_types=1);

namespace MyAILib\Tests\Config;

use MyAILib\Config\AIConfig;
use PHPUnit\Framework\TestCase;

final class AIConfigTest extends TestCase
{
    public function testCanRetrieveProviderConfiguration(): void
    {
        $config = new AIConfig([
            'openrouter' => [
                'api_key' => 'test-key',
                'model' => 'test-model',
            ],
        ]);

        $this->assertTrue(
            $config->hasProvider('openrouter')
        );

        $this->assertSame(
            [
                'api_key' => 'test-key',
                'model' => 'test-model',
            ],
            $config->provider('openrouter')
        );
    }

    public function testUnknownProviderReturnsEmptyConfiguration(): void
    {
        $config = new AIConfig();

        $this->assertFalse(
            $config->hasProvider('unknown')
        );

        $this->assertSame(
            [],
            $config->provider('unknown')
        );
    }
}
