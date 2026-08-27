<?php


declare(strict_types=1);

namespace MyAILib\Tests;

use MyAILib\Provider\OpenAI\OpenAIProvider;
use PHPUnit\Framework\TestCase;

final class OpenAIProviderTest extends TestCase
{
    public function testProviderConfiguration(): void
    {
        $provider = new OpenAIProvider();

        $provider->configure([
            'api_key' => 'test-key',
            'model' => 'test-model',
        ]);

        $this->assertSame(
            'OpenAI',
            $provider->getName()
        );

        $this->assertSame(
            'openai',
            $provider->getSlug()
        );
    }
}
