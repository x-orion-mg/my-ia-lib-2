<?php


declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use MyAILib\AI\AIManager;
use MyAILib\Config\ConfigLoader;
use MyAILib\Provider\OpenRouter\OpenRouterProvider;
use MyAILib\Provider\ProviderFactory;
use MyAILib\Provider\ProviderRegistry;
use MyAILib\Request\AIRequest;
use MyAILib\Session\MemorySessionStore;

// --------------------------------------------------
// 1. Registry
// --------------------------------------------------

$registry = new ProviderRegistry();

$registry->register(
    'openrouter',
    OpenRouterProvider::class
);

// --------------------------------------------------
// 2. Configuration
// --------------------------------------------------

$config = ConfigLoader::fromArray([
    'providers' => [
        'openrouter' => [
            'api_key' => getenv('OPENRORB_API_KEY'),
            'model' => 'inclusionai/ling-3.0-flash-fin:free',
            'models_list' => [
                'openai/gpt-5',
                'nvidia/nemotron-3-nano-30b-a3b:free',
                'allenai/olmo-3.1-32b-think:free',
                'inclusionai/ling-3.0-flash-fin:free'
            ],

            'temperature' => 0.7,
            'max_tokens' => 500,

            'referer' => 'https://www.example.com',
            'x_title' => 'My Application',
        ],
    ],
]);

// --------------------------------------------------
// 3. Factory
// --------------------------------------------------

$factory = new ProviderFactory(
    $registry,
    $config
);

// --------------------------------------------------
// 4. Session store
// --------------------------------------------------

$sessionStore = new MemorySessionStore();

// --------------------------------------------------
// 5. Création de l'IA
// --------------------------------------------------

$ai = AIManager::create(
    'openrouter',
    $factory,
    [],
    $sessionStore
);

// --------------------------------------------------
// 6. Session
// --------------------------------------------------

$ai->startSession('example-session');

// --------------------------------------------------
// 7. System prompt
// --------------------------------------------------

$ai->setSystemPrompt(
    'Tu es un assistant français. '
    . 'Réponds de manière claire et concise.'
);

// --------------------------------------------------
// 8. Question
// --------------------------------------------------

$request = AIRequest::fromPrompt(
    'Explique-moi simplement ce qu’est une API.'
);

try {
    $response = $ai->ask($request);

    echo '<h1>Réponse</h1>';

    echo '<pre>';
    echo htmlspecialchars(
        $response->text(),
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</pre>';

    echo '<h2>Informations</h2>';

    echo '<pre>';
    print_r([
        'provider' => $response->provider(),
        'model' => $response->model(),
        'usage' => $response->usage(),
        'finish_reason' => $response->finishReason(),
    ]);
    echo '</pre>';

} catch (Throwable $e) {
    echo '<h1>Erreur</h1>';

    echo '<pre>';
    echo htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    );
    echo '</pre>';
}
