<?php


declare(strict_types=1);

namespace MyAILib\Tests;

use MyAILib\Response\AIResponse;
use PHPUnit\Framework\TestCase;

final class AIResponseTest extends TestCase
{
    public function testResponseExposesNormalizedData(): void
    {
        $response = new AIResponse(
            text: 'Bonjour',
            provider: 'openrouter',
            model: 'test-model',
            usage: [
                'prompt_tokens' => 10,
                'completion_tokens' => 20,
                'total_tokens' => 30,
            ],
            finishReason: 'stop',
            metadata: [
                'custom' => 'value',
            ],
        );

        $this->assertSame(
            'Bonjour',
            $response->text()
        );

        $this->assertSame(
            'openrouter',
            $response->provider()
        );

        $this->assertSame(
            'test-model',
            $response->model()
        );

        $this->assertSame(
            30,
            $response->usage()['total_tokens']
        );

        $this->assertSame(
            'stop',
            $response->finishReason()
        );

        $this->assertSame(
            'value',
            $response->metadata()['custom']
        );
    }
}
