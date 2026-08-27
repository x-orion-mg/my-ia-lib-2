<?php

declare(strict_types=1);

namespace MyAILib\Provider\OpenAI;

use MyAILib\Provider\ProviderCapability;
use MyAILib\Provider\ProviderInterface;
use MyAILib\Request\AIRequest;
use MyAILib\Response\AIResponse;
use MyAILib\Http\CurlHttpClient;
use MyAILib\Http\HttpClientInterface;
use MyAILib\Exception\AuthenticationException;
use MyAILib\Exception\InvalidRequestException;
use MyAILib\Exception\ProviderException;
use MyAILib\Exception\RateLimitException;


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
            throw new AuthenticationException(
                'OpenAI API key is missing.',
                $this->getSlug()
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
            $this->baseUrl . '/responses',
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
            throw new ProviderException(
                'Invalid JSON response from OpenAI.',
                $this->getSlug()
            );
        }


        if (!$response->isSuccessful()) {
            $statusCode = $response->statusCode();

            $message = $data['error']['message']
                ?? 'Unknown OpenAI error.';

            if ($statusCode === 401 || $statusCode === 403) {
                throw new AuthenticationException(
                    $message,
                    $this->getSlug(),
                    $statusCode
                );
            }

            if ($statusCode === 429) {
                throw new RateLimitException(
                    $message,
                    $this->getSlug(),
                    $statusCode
                );
            }

            if ($statusCode >= 400 && $statusCode < 500) {
                throw new InvalidRequestException(
                    $message,
                    $this->getSlug(),
                    $statusCode
                );
            }

            throw new ProviderException(
                $message,
                $this->getSlug(),
                $statusCode
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

    public function supports(ProviderCapability $capability): bool
    {
        return match ($capability) {
            ProviderCapability::CHAT, ProviderCapability::VISION => true,
            ProviderCapability::TOOLS, ProviderCapability::JSON, ProviderCapability::STREAMING => false,
        };
    }

    public function getModels(): array
    {
        return [
            new \MyAILib\Model\AIModel(
                id: 'gpt-5',
                name: 'GPT-5',
                capabilities: [ProviderCapability::CHAT->value]
            ),
            new \MyAILib\Model\AIModel(
                id: 'gpt-4',
                name: 'GPT-4',
                capabilities: [ProviderCapability::CHAT->value]
            ),
        ];
    }
}
