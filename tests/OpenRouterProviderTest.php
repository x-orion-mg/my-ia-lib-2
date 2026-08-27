<?php


declare(strict_types=1);

namespace MyAILib\Tests;

use MyAILib\Http\HttpResponse;
use MyAILib\Provider\OpenRouter\OpenRouterProvider;
use MyAILib\Request\AIRequest;
use MyAILib\Tests\Http\FakeHttpClient;
use PHPUnit\Framework\TestCase;

final class OpenRouterProviderTest extends TestCase
{
    public function testProviderReturnsResponse(): void
    {
        $http = new FakeHttpClient([
            new HttpResponse(
                200,
                json_encode([
                    'model' => 'test-model',
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Bonjour depuis OpenRouter',
                            ],
                        ],
                    ],
                ], JSON_THROW_ON_ERROR)
            ),
        ]);

        $provider = new OpenRouterProvider($http);

        $provider->configure([
            'api_key' => 'test-key',
            'model' => 'test-model',
        ]);

        $response = $provider->ask(
            new AIRequest('Bonjour')
        );

        $this->assertSame(
            'Bonjour depuis OpenRouter',
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
    }

    public function testProviderFallsBackToNextModel(): void
    {
        $http = new FakeHttpClient([
            new HttpResponse(
                500,
                '{"error":{"message":"Model unavailable"}}'
            ),
            new HttpResponse(
                200,
                json_encode([
                    'model' => 'model-2',
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Réponse du modèle 2',
                            ],
                        ],
                    ],
                ], JSON_THROW_ON_ERROR)
            ),
        ]);

        $provider = new OpenRouterProvider($http);

        $provider->configure([
            'api_key' => 'test-key',
            'models_list' => [
                'model-1',
                'model-2',
            ],
        ]);

        $response = $provider->ask(
            new AIRequest('Bonjour')
        );

        $this->assertSame(
            'Réponse du modèle 2',
            $response->text()
        );

        $this->assertSame(
            'model-2',
            $response->model()
        );

        $this->assertCount(
            2,
            $http->requests
        );
    }
}
