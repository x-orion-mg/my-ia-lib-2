<?php

declare(strict_types=1);

namespace MyAILib\Tests;

use JsonException;
use MyAILib\Http\HttpResponse;
use MyAILib\Options\GenerationOptions;
use MyAILib\Provider\OpenRouter\OpenRouterProvider;
use MyAILib\Provider\ProviderCapability;
use MyAILib\Request\AIRequest;
use MyAILib\Tests\Http\FakeHttpClient;
use PHPUnit\Framework\TestCase;

final class OpenRouterProviderTest extends TestCase
{
    /**
     * @throws JsonException
     */
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
                            'finish_reason' => 'stop',
                        ],
                    ],
                    'usage' => [
                        'prompt_tokens' => 5,
                        'completion_tokens' => 10,
                        'total_tokens' => 15,
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
            AIRequest::fromPrompt('Bonjour')
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

        $this->assertSame(
            15,
            $response->usage()['total_tokens']
        );

        $this->assertSame(
            5,
            $response->usage()['prompt_tokens']
        );

        $this->assertSame(
            10,
            $response->usage()['completion_tokens']
        );

        $this->assertSame(
            'stop',
            $response->finishReason()
        );
    }

    /**
     * @throws JsonException
     */
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
                            'finish_reason' => 'stop',
                        ],
                    ],
                    'usage' => [
                        'prompt_tokens' => 8,
                        'completion_tokens' => 12,
                        'total_tokens' => 20,
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
            AIRequest::fromPrompt('Bonjour')
        );

        $this->assertSame(
            'Réponse du modèle 2',
            $response->text()
        );

        $this->assertSame(
            'model-2',
            $response->model()
        );

        $this->assertSame(
            'stop',
            $response->finishReason()
        );

        $this->assertSame(
            20,
            $response->usage()['total_tokens']
        );

        $this->assertSame(
            8,
            $response->usage()['prompt_tokens']
        );

        $this->assertSame(
            12,
            $response->usage()['completion_tokens']
        );

        $this->assertCount(
            2,
            $http->requests
        );
    }

    /**
     * @throws JsonException
     * @throws \Exception
     */
    public function testProviderSendsRequestOptions(): void
    {
        $http = new FakeHttpClient([
            new HttpResponse(
                200,
                json_encode([
                    'model' => 'test-model',
                    'choices' => [
                        [
                            'message' => [
                                'content' => 'Réponse',
                            ],
                            'finish_reason' => 'stop',
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
        $options = new GenerationOptions(
            temperature: 0.7,
            maxTokens: 500,
            topP: 0.9,
        );
        $provider->ask(
            AIRequest::fromPrompt(
                'Bonjour',
                options: $options
            )
        );

        $request = $http->requests[0];

        $this->assertSame(
            0.7,
            $request['data']['temperature']
        );

        $this->assertSame(
            500,
            $request['data']['max_tokens']
        );
    }

    public function testProviderReportsCapabilities(): void
    {
        $provider = new OpenRouterProvider();

        $this->assertTrue(
            $provider->supports(
                ProviderCapability::CHAT
            )
        );

        $this->assertTrue(
            $provider->supports(
                ProviderCapability::JSON
            )
        );

        $this->assertFalse(
            $provider->supports(
                ProviderCapability::STREAMING
            )
        );
    }
    public function testProviderExposesConfiguredModels(): void
    {
        $provider = new OpenRouterProvider();

        $provider->configure([
            'api_key' => 'test-key',
            'models_list' => [
                'model-1',
                'model-2',
            ],
        ]);

        $models = $provider->getModels();

        $this->assertCount(
            2,
            $models
        );

        $this->assertSame(
            'model-1',
            $models[0]->id()
        );

        $this->assertSame(
            'model-2',
            $models[1]->id()
        );
    }


}
