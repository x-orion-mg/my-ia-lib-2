<?php


declare(strict_types=1);

namespace MyAILib\Provider\OpenRouter;

use MyAILib\Exception\AuthenticationException;
use MyAILib\Exception\InvalidRequestException;
use MyAILib\Exception\ProviderException;
use MyAILib\Exception\RateLimitException;
use MyAILib\Http\CurlHttpClient;
use MyAILib\Http\HttpClientInterface;
use MyAILib\Model\AIModel;
use MyAILib\Provider\ProviderCapability;
use MyAILib\Request\AIRequest;
use MyAILib\Response\AIResponse;
use MyAILib\Provider\ProviderInterface;

final class OpenRouterProvider implements ProviderInterface
{
    private string $apiKey = '';

    private string $model = '';

    private array $modelsList = [];

    private string $baseUrl = 'https://openrouter.ai/api/v1';

    private ?string $referer = null;

    private ?string $title = null;

    public function __construct(
        private readonly ?HttpClientInterface $httpClient = null
    )
    {
    }

    public function configure(array $options): void
    {
        $this->apiKey = $options['api_key']
            ?? getenv('OPENROUTER_API_KEY')
            ?: '';

        $this->model = $options['model'] ?? '';

        $this->modelsList = $options['models_list'] ?? [];

        $this->baseUrl = rtrim(
            $options['base_url'] ?? $this->baseUrl,
            '/'
        );

        $this->referer = $options['referer'] ?? null;

        $this->title = $options['x_title'] ?? null;

        if ($this->apiKey === '') {
            throw new AuthenticationException(
                'OpenRouter API key is missing.',
                $this->getSlug()
            );
        }

        if ($this->model === '' && $this->modelsList === []) {
            throw new InvalidRequestException(
                'OpenRouter requires a model or models_list.',
                $this->getSlug()
            );
        }
    }

    /**
     * @throws \Exception
     */
    public function ask(AIRequest $request): AIResponse
    {
        $models = $this->getModelsToTry();

        $lastException = null;

        foreach ($models as $model) {
            try {
                return $this->askWithModel(
                    $request,
                    $model
                );
            } catch (ProviderException $e) {
                $lastException = $e;
            }
        }

        if ($lastException !== null) {
            throw $lastException;
        }

        throw new ProviderException(
            'OpenRouter could not find a usable model.',
            $this->getSlug()
        );
    }

    public function getName(): string
    {
        return 'OpenRouter';
    }

    public function getSlug(): string
    {
        return 'openrouter';
    }

    private function askWithModel(
        AIRequest $request,
        string    $model
    ): AIResponse
    {
        $payload = [
            'model' => $model,
            'messages' => $request->toArray(),
        ];

        $options = $request->options();

        if ($options !== null) {
            $payload = [
                ...$payload,
                ...$options->toArray(),
            ];
        }



        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $this->apiKey,
        ];

        if ($this->referer !== null) {
            $headers['HTTP-Referer'] = $this->referer;
        }

        if ($this->title !== null) {
            $headers['X-Title'] = $this->title;
        }

        $client = $this->httpClient
            ?? new CurlHttpClient();

        $response = $client->post(
            $this->baseUrl . '/chat/completions',
            $headers,
            $payload
        );

        $data = json_decode(
            $response->body(),
            true
        );

        if (!is_array($data)) {
            throw new ProviderException(
                'Invalid JSON response from OpenRouter.',
                $this->getSlug(),
                $response->statusCode()
            );
        }

        if (!$response->isSuccessful()) {
            $this->throwApiException(
                $data,
                $response->statusCode()
            );
        }

        $text = $data['choices'][0]['message']['content']
            ?? null;

        if (!is_string($text)) {
            throw new ProviderException(
                'OpenRouter response does not contain valid text.',
                $this->getSlug(),
                $response->statusCode()
            );
        }

        return new AIResponse(
            text: $text,
            provider: $this->getSlug(),
            model: $data['model'] ?? $model,
            usage: $data['usage'] ?? null,
            finishReason: $data['choices'][0]['finish_reason'] ?? null,
            metadata: $data
        );
    }

    private function getModelsToTry(): array
    {
        if ($this->modelsList !== []) {
            return $this->modelsList;
        }

        return [$this->model];
    }

    private function throwApiException(
        array $data,
        int   $statusCode
    ): never
    {
        $message = $data['error']['message']
            ?? 'Unknown OpenRouter error.';

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

    public function supports(
        ProviderCapability $capability
    ): bool {
        return match ($capability) {
            ProviderCapability::CHAT, ProviderCapability::JSON => true,
            ProviderCapability::VISION, ProviderCapability::TOOLS, ProviderCapability::STREAMING => false,
        };
    }

    public function getModels(): array
    {
        $models = $this->modelsList;

        if ($models === [] && $this->model !== '') {
            $models = [$this->model];
        }

        return array_map(
            static fn (string $model) => new AIModel(
                id: $model,
                name: $model,
            ),
            $models
        );
    }



}
