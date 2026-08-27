<?php

declare(strict_types=1);

namespace MyAILib\Provider\OpenAI;

use MyAILib\Provider\ProviderInterface;
use MyAILib\Request\AIRequest;
use MyAILib\Response\AIResponse;
use RuntimeException;
use MyAILib\Http\CurlHttpClient;
use MyAILib\Http\HttpClientInterface;

final class OpenAIProvider implements ProviderInterface
{
    private string $apiKey = '';

    private string $model = 'gpt-5';

    private string $baseUrl = 'https://api.openai.com/v1';

    private array $options = [];
    private HttpClientInterface $httpClient;

    public function __construct(
        ?HttpClientInterface $httpClient = null
    ) {
        $this->httpClient = $httpClient
            ?? new CurlHttpClient();
    }

    public function configure(array $options): void
    {
        $this->options = $options;

        $this->apiKey = $options['api_key']
            ?? getenv('OPENAI_API_KEY')
            ?: '';

        $this->model = $options['model']
            ?? $this->model;

        $this->baseUrl = rtrim(
            $options['base_url'] ?? $this->baseUrl,
            '/'
        );

        if ($this->apiKey === '') {
            throw new RuntimeException(
                'OpenAI API key is missing.'
            );
        }
    }

    public function ask(AIRequest $request): AIResponse
    {
        $payload = [
            'model' => $this->model,
            'input' => $request->getPrompt(),
        ];

        $response = $this->request(
            $payload
        );

        return new AIResponse(
            text: $this->extractText($response),
            provider: $this->getSlug(),
            model: $response['model'] ?? $this->model,
            metadata: $response
        );
    }

    public function getName(): string
    {
        return 'OpenAI';
    }

    public function getSlug(): string
    {
        return 'openai';
    }

    private function request(
        array $payload
    ): array {
        $response = $this->httpClient->post(
            $this->baseUrl . $endpoint,
            [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->apiKey,
            ],
            $payload
        );

        $data = json_decode(
            $response->body(),
            true
        );

        if (!is_array($data)) {
            throw new RuntimeException(
                'Invalid JSON response from OpenAI.'
            );
        }

        if (!$response->isSuccessful()) {
            $message = $data['error']['message']
                ?? 'Unknown OpenAI error.';

            throw new RuntimeException(
                sprintf(
                    'OpenAI API error (%d): %s',
                    $response->statusCode(),
                    $message
                )
            );
        }

        return $data;
    }


    private function extractText(array $response): string
    {
        if (
            isset($response['output_text'])
            && is_string($response['output_text'])
        ) {
            return $response['output_text'];
        }

        return '';
    }
}
