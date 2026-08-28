<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/Agent/RecipeAgent.php';
use MyAILib\AI\AIManager;
use MyAILib\Config\ConfigLoader;
use MyAILib\Provider\OpenRouter\OpenRouterProvider;
use MyAILib\Provider\ProviderFactory;
use MyAILib\Provider\ProviderRegistry;
use MyAILib\Session\FileSessionStore;
use MyAILib\Examples\Agent\RecipeAgent;

// ==================================================
// 1. Provider Registry
// ==================================================

$registry = new ProviderRegistry();

$registry->register(
    'openrouter',
    OpenRouterProvider::class
);

// ==================================================
// 2. Configuration
// ==================================================

$config = ConfigLoader::fromArray([
    'default_provider' => 'openrouter',

    'providers' => [
        'openrouter' => [
            /*
             * Ne mets pas ta clé directement dans Git.
             *
             * Windows :
             * set OPENROUTER_API_KEY=sk-or-...
             *
             * Puis redémarre ton serveur PHP/Laragon.
             */
            'api_key' => getenv('OPENROUTER_API_KEY'),

            'models_list' => [
                'openai/gpt-5',
                'nvidia/nemotron-3-nano-30b-a3b:free',
                'allenai/olmo-3.1-32b-think:free',
                'inclusionai/ling-3.0-flash-fin:free'
            ],

            'temperature' => 0.7,
            'max_tokens' => 500,

            'referer' => 'https://www.example.com',
            'x_title' => 'My AI Lib Demo',
        ],
    ],
]);

// ==================================================
// 3. Provider Factory
// ==================================================

$factory = new ProviderFactory(
    $registry,
    $config
);

// ==================================================
// 4. Session Store
// ==================================================

$sessionDirectory = __DIR__ . '/../storage/sessions';

$sessionStore = new FileSessionStore(
    $sessionDirectory
);

// ==================================================
// 5. Création de AIManager
// ==================================================
//
// null = utilise le provider par défaut
// configuré dans AIConfig.
//

$ai = AIManager::create(
    null,
    $factory,
    [],
    $sessionStore
);

// ==================================================
// 6. Démarrage de la conversation
// ==================================================

$ai->startSession(
    'recipe-demo'
);

// ==================================================
// 7. Création de l'agent
// ==================================================

$agent = new RecipeAgent(
    $ai
);

// ==================================================
// 8. Exécution de l'agent
// ==================================================

try {

    $response = $agent->run(
        'Je veux une recette simple avec des tomates, '
        . 'des œufs et du fromage.'
    );

    echo '<!DOCTYPE html>';
    echo '<html lang="fr">';
    echo '<head>';
    echo '<meta charset="UTF-8">';
    echo '<title>Recipe Agent</title>';

    echo '<style>';
    echo 'body {';
    echo '    font-family: Arial, sans-serif;';
    echo '    max-width: 900px;';
    echo '    margin: 40px auto;';
    echo '    padding: 20px;';
    echo '    background: #f5f5f5;';
    echo '}';
    echo '.box {';
    echo '    background: white;';
    echo '    padding: 25px;';
    echo '    border-radius: 12px;';
    echo '    box-shadow: 0 5px 20px rgba(0,0,0,.08);';
    echo '}';
    echo 'pre {';
    echo '    white-space: pre-wrap;';
    echo '    line-height: 1.6;';
    echo '}';
    echo '</style>';

    echo '</head>';
    echo '<body>';

    echo '<div class="box">';

    echo '<h1>Recipe Agent</h1>';

    echo '<h2>Réponse</h2>';

    echo '<pre>';

    echo htmlspecialchars(
        $response,
        ENT_QUOTES,
        'UTF-8'
    );

    echo '</pre>';

    // --------------------------------------------------
    // Informations sur la conversation
    // --------------------------------------------------

    $session = $ai->getSession();

    if ($session !== null) {

        echo '<h2>Conversation</h2>';

        echo '<p>';
        echo 'Session : ';
        echo htmlspecialchars(
            $session->id(),
            ENT_QUOTES,
            'UTF-8'
        );
        echo '</p>';

        echo '<p>';
        echo 'Messages : ';
        echo count(
            $session->messages()
        );
        echo '</p>';
    }

    echo '</div>';

    echo '</body>';
    echo '</html>';

} catch (Throwable $e) {

    http_response_code(500);

    echo '<h1>Erreur</h1>';

    echo '<pre>';

    echo htmlspecialchars(
        $e->getMessage(),
        ENT_QUOTES,
        'UTF-8'
    );

    echo '</pre>';
}