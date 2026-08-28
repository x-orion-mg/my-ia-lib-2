<?php


require_once __DIR__ . '/../vendor/autoload.php';

use MyAILib\MyAI;

$ai = MyAI::create([
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),
    'models_list' => [
        'openai/gpt-5',
        'nvidia/nemotron-3-nano-30b-a3b:free',
        'allenai/olmo-3.1-32b-think:free',
        'inclusionai/ling-3.0-flash-fin:free'
    ],
]);

$response = $ai->ask(
    'Explique-moi simplement ce qu’est une API.'
);

echo $response->text();