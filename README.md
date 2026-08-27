# My AI Lib

> Une couche d'abstraction PHP pour utiliser plusieurs fournisseurs d'IA avec une API commune.

My AI Lib permet d'intégrer différents fournisseurs d'intelligence artificielle dans une application PHP sans coupler le code métier à un fournisseur particulier.

L'objectif principal est simple :

```php
$response = $ai->ask('Bonjour, peux-tu m'aider ?');
```
Objectif
My AI Lib a été conçue pour séparer :

le code métier ;
le fournisseur d'IA ;
le modèle utilisé ;
la communication HTTP ;
la gestion des sessions ;
les options de génération ;
la gestion des erreurs.
L'application peut donc choisir son IA sans modifier le code métier.

Exemple
Le code métier peut rester :

$response = $ai->ask('Analyse cette recette.');

Le provider peut être changé :

OpenAI
OpenRouter
Gemini
Anthropic
Mistral
...

sans que le code qui utilise l'IA ait besoin de connaître les détails de l'API du fournisseur.

✨ Fonctionnalités
Abstraction des providers
Tous les providers implémentent le même contrat :

ProviderInterface

Un provider doit notamment fournir :

public function ask(AIRequest $request): AIResponse;

public function configure(array $options): void;

public function getName(): string;

public function getSlug(): string;

public function supports(ProviderCapability $capability): bool;

public function getModels(): array;

Cela permet d'ajouter de nouveaux providers sans modifier le cœur de la librairie.

API commune
L'interface principale est :

AIInterface

Elle expose :

public function ask(string|AIRequest $request): AIResponse;

On peut donc simplement faire :

$response = $ai->ask('Bonjour');

ou utiliser une requête structurée :

$request = AIRequest::fromPrompt(
'Bonjour'
);

$response = $ai->ask($request);

Réponses normalisées
Les providers retournent tous :

AIResponse

Une réponse contient notamment :

$response->text();
$response->provider();
$response->model();
$response->usage();
$response->finishReason();
$response->metadata();

Le code métier n'a donc pas besoin de connaître le format de réponse spécifique à OpenAI, OpenRouter ou un autre fournisseur.

Modèles
Les modèles sont représentés par :

AIModel

Un modèle possède notamment :

un identifiant ;
un nom ;
des capacités.
Exemple :

$model = new AIModel(
id: 'gpt-5',
name: 'GPT-5',
capabilities: [
'chat',
]
);

Les providers peuvent exposer leurs modèles via :

$models = $provider->getModels();

Capacités
Les capacités sont représentées par :

ProviderCapability

Les capacités actuellement définies sont :

CHAT
VISION
TOOLS
JSON
STREAMING

Un provider peut indiquer ce qu'il supporte :

if (
$provider->supports(
ProviderCapability::VISION
)
) {
// ...
}

Cette abstraction permettra notamment aux futurs agents de demander une capacité sans dépendre directement d'un provider.

Sessions et historique
My AI Lib peut gérer une session de conversation.

$ai->startSession('conversation-123');

Les messages sont alors conservés dans la session.

Le système permet également de définir un system prompt :

$ai->setSystemPrompt(
'Tu es un assistant spécialisé en cuisine.'
);

L'historique et le system prompt sont ensuite intégrés automatiquement aux requêtes.

Options de génération
Les requêtes peuvent recevoir des options de génération.

Exemple :

$options = new GenerationOptions(
temperature: 0.7,
maxTokens: 500
);

$request = AIRequest::fromPrompt(
'Explique-moi cette recette.',
$options
);

$response = $ai->ask($request);

Les options sont ensuite traduites par chaque provider vers le format attendu par son API.

Fallback de modèles
Certains providers peuvent essayer plusieurs modèles.

OpenRouter supporte par exemple :

[
'models_list' => [
'model-1',
'model-2',
'model-3',
],
]

Si le premier modèle échoue avec une erreur provider compatible avec le fallback, le modèle suivant peut être essayé.

Cela permet de construire des applications plus résilientes.

Gestion des erreurs
My AI Lib fournit des exceptions communes :

AuthenticationException
InvalidRequestException
RateLimitException
ProviderException

Le code métier peut donc gérer les erreurs de manière indépendante du provider.

Exemple :

try {
$response = $ai->ask($request);
} catch (RateLimitException $e) {
// Limite de requêtes atteinte
} catch (AuthenticationException $e) {
// Problème de clé API
} catch (ProviderException $e) {
// Erreur du provider
}

📦 Installation
My AI Lib est une librairie Composer.

composer require x-orion-mg/my-ai-lib

Ou pour travailler directement avec le dépôt :

git clone https://github.com/x-orion-mg/my-ia-lib-2.git

cd my-ia-lib-2

composer install

Le projet nécessite PHP 8.2 ou supérieur.
G
GitHub

🚀 Utilisation
Créer un provider
Le principe est de créer un ProviderRegistry, puis d'enregistrer les providers disponibles.

use MyAILib\Provider\ProviderRegistry;
use MyAILib\Provider\OpenAI\OpenAIProvider;

$registry = new ProviderRegistry();

$registry->register(
'openai',
OpenAIProvider::class
);

Créer une factory
use MyAILib\Provider\ProviderFactory;

$factory = new ProviderFactory(
$registry
);

La ProviderFactory est responsable de la création des instances de providers.

Créer une IA
use MyAILib\AI\AIManager;

$ai = AIManager::create(
'openai',
$factory,
[
'api_key' => getenv('OPENAI_API_KEY'),
'model' => 'gpt-5',
]
);

Poser une question
$response = $ai->ask(
'Explique-moi simplement la théorie de la relativité.'
);

echo $response->text();

🔵 OpenAI
Le provider OpenAI est disponible avec le slug :

openai

Exemple :

$registry->register(
'openai',
OpenAIProvider::class
);

$ai = AIManager::create(
'openai',
$factory,
[
'api_key' => getenv('OPENAI_API_KEY'),
'model' => 'gpt-5',
]
);

$response = $ai->ask(
'Bonjour OpenAI'
);

La clé API peut également être récupérée depuis :

OPENAI_API_KEY

Le provider OpenAI utilise l'abstraction HTTP de My AI Lib et peut recevoir un HttpClientInterface personnalisé.
G
GitHub

🟣 OpenRouter
Le provider OpenRouter utilise le slug :

openrouter

Exemple :

use MyAILib\Provider\OpenRouter\OpenRouterProvider;

$registry->register(
'openrouter',
OpenRouterProvider::class
);

$ai = AIManager::create(
'openrouter',
$factory,
[
'api_key' => getenv('OPENROUTER_API_KEY'),

        'models_list' => [
            'openai/gpt-5',
            'model-2',
            'model-3',
        ],
    ]
);

$response = $ai->ask(
'Bonjour OpenRouter'
);

OpenRouter supporte notamment :

'model' => 'model-name'

ou :

'models_list' => [
'model-1',
'model-2',
'model-3',
]

Le provider construit également une AIResponse normalisée contenant notamment le modèle, l'utilisation et la raison de fin de génération.
G
GitHub

🔧 Configuration
La configuration peut être centralisée avec :

use MyAILib\Config\AIConfig;
use MyAILib\Config\ConfigLoader;

Depuis un tableau
$config = ConfigLoader::fromArray([
'providers' => [
'openai' => [
'api_key' => getenv('OPENAI_API_KEY'),
'model' => 'gpt-5',
],

        'openrouter' => [
            'api_key' => getenv('OPENROUTER_API_KEY'),
            'models_list' => [
                'model-1',
                'model-2',
            ],
        ],
    ],
]);

Depuis un fichier PHP
Créer par exemple :

config/ai.php

<?php

return [
    'providers' => [
        'openai' => [
            'api_key' => getenv('OPENAI_API_KEY'),
            'model' => 'gpt-5',
        ],

        'openrouter' => [
            'api_key' => getenv('OPENROUTER_API_KEY'),
            'model' => 'model-name',
        ],
    ],
];

Puis :

$config = ConfigLoader::fromFile(
    __DIR__ . '/config/ai.php'
);

Ne commitez jamais vos clés API dans Git.

🌐 Abstraction HTTP
Les providers ne sont pas obligés de gérer directement les requêtes HTTP.

My AI Lib fournit :

HttpClientInterface

avec une implémentation cURL :

CurlHttpClient

Cela permet notamment d'utiliser un client HTTP différent ou un fake client dans les tests.

Architecture :

Provider
    │
    ▼
HttpClientInterface
    │
    ├── CurlHttpClient
    └── FakeHttpClient

Cette séparation permet de tester les providers sans effectuer de véritables appels réseau.

🧩 Ajouter un nouveau provider
L'un des objectifs principaux de My AI Lib est de permettre l'ajout de providers sans modifier le cœur de la librairie.

Supposons que nous voulions ajouter :

MyProvider

Créer :

src/MyAILib/Provider/MyProvider/
└── MyProvider.php

Puis implémenter :

<?php

declare(strict_types=1);

namespace MyAILib\Provider\MyProvider;

use MyAILib\Model\AIModel;
use MyAILib\Provider\ProviderCapability;
use MyAILib\Provider\ProviderInterface;
use MyAILib\Request\AIRequest;
use MyAILib\Response\AIResponse;

final class MyProvider implements ProviderInterface
{
    public function ask(
        AIRequest $request
    ): AIResponse {
        // Appel API du provider
    }

    public function configure(
        array $options
    ): void {
        // Configuration
    }

    public function getName(): string
    {
        return 'My Provider';
    }

    public function getSlug(): string
    {
        return 'my-provider';
    }

    public function supports(
        ProviderCapability $capability
    ): bool {
        return match ($capability) {
            ProviderCapability::CHAT => true,
            default => false,
        };
    }

    /**
     * @return AIModel[]
     */
    public function getModels(): array
    {
        return [
            new AIModel(
                id: 'my-model',
                name: 'My Model',
                capabilities: [
                    ProviderCapability::CHAT->value,
                ]
            ),
        ];
    }
}

Puis l'enregistrer :

$registry->register(
    'my-provider',
    MyProvider::class
);

Et l'utiliser :

$ai = AIManager::create(
    'my-provider',
    $factory
);

$response = $ai->ask(
    'Bonjour'
);

Le code métier n'a pas besoin de changer.

🏗️ Architecture
La structure actuelle du projet est organisée autour de plusieurs responsabilités :

src/MyAILib/
│
├── AI/
│   ├── AIInterface.php
│   └── AIManager.php
│
├── Catalog/
│   └── AICatalog.php
│
├── Config/
│   ├── AIConfig.php
│   └── ConfigLoader.php
│
├── Exception/
│
├── Http/
│   ├── CurlHttpClient.php
│   ├── HttpClientInterface.php
│   └── HttpResponse.php
│
├── Message/
│
├── Model/
│   └── AIModel.php
│
├── Options/
│
├── Provider/
│   ├── OpenAI/
│   ├── OpenRouter/
│   ├── ProviderCapability.php
│   ├── ProviderFactory.php
│   ├── ProviderInterface.php
│   └── ProviderRegistry.php
│
├── Request/
│   └── AIRequest.php
│
├── Response/
│   └── AIResponse.php
│
└── Session/

Cette organisation sépare volontairement le cœur de la librairie des implémentations spécifiques aux fournisseurs.

🧠 Principe architectural
Le principe central est :

                  Application
                       │
                       ▼
                   AIManager
                       │
                       ▼
                ProviderInterface
                       │
          ┌────────────┼────────────┐
          ▼            ▼            ▼
       OpenAI      OpenRouter     Future...
          │            │
          └──────┬─────┘
                 ▼
        HttpClientInterface
                 │
                 ▼
            HTTP Client

Le code métier dépend de l'abstraction :

AIInterface
ProviderInterface
AIRequest
AIResponse

et non d'une API spécifique.

🤖 Préparer les futurs agents
My AI Lib n'est pas un framework d'agents.

C'est volontaire.

La librairie fournit la couche d'infrastructure permettant ensuite de construire des agents au-dessus.

Par exemple :

Application
     │
     ▼
   Agent
     │
     ▼
 My AI Lib
     │
     ├── AIManager
     ├── ProviderFactory
     ├── ProviderRegistry
     ├── AIRequest
     ├── AIResponse
     ├── Sessions
     └── Providers

Un futur agent pourra ainsi être spécialisé :

RecipeAgent
SupportAgent
CodingAgent
ResearchAgent
AutomationAgent
...

et utiliser My AI Lib sans connaître directement OpenAI, OpenRouter ou un autre fournisseur.

Exemple conceptuel :

final class RecipeAgent
{
    public function __construct(
        private readonly AIInterface $ai
    ) {
    }

    public function run(string $recipe): AIResponse
    {
        return $this->ai->ask(
            "Analyse cette recette : {$recipe}"
        );
    }
}

L'agent dépend alors de :

AIInterface

et non de :

OpenAIProvider

C'est une séparation importante pour construire des agents interchangeables.

🧪 Tests
Le projet utilise PHPUnit.

Lancer les tests :

composer test

Ou directement :

vendor/bin/phpunit

Les providers peuvent être testés avec un client HTTP fake afin de ne pas dépendre d'un service externe.

🔐 Sécurité
Ne stockez jamais les clés API dans le code source.

Utilisez des variables d'environnement :

OPENAI_API_KEY=...
OPENROUTER_API_KEY=...

Et assurez-vous que les fichiers contenant des secrets sont exclus de Git.

📋 État du projet
Core
 Abstraction AI
 AIManager
 AIRequest
 AIResponse
 Messages
 Sessions
 System prompt
 Generation options
 Provider interface
 Provider registry
 Provider factory
 Provider capabilities
 AI models
 AI catalog
 Configuration
 HTTP abstraction
 Gestion des erreurs providers
Providers
 OpenAI
 OpenRouter
À venir
 Streaming
 Structured output / JSON
 Tool calling
 Vision / multimodalisation avancée
 Sélection automatique du provider/modèle
 Nouveaux providers
 Framework d'agents
🛣️ Philosophie du projet
My AI Lib cherche à respecter trois principes :

1. Un code métier indépendant du provider
$ai->ask(...);

doit rester la principale interaction avec l'IA.

2. Les providers sont interchangeables
Ajouter ou remplacer un provider ne doit pas nécessiter de réécrire les agents ou le code métier.

3. Le cœur ne doit pas connaître les API propriétaires
Les différences entre les APIs sont encapsulées dans les providers.

📄 Licence
My AI Lib est distribué sous licence MIT.

Voir le fichier LICENSE.

👤 Auteur
Projet personnel développé par x-orion-mg.

Repository :

https://github.com/x-orion-mg/my-ia-lib-2


### Une remarque importante avant de le mettre

J'ai volontairement **corrigé un point par rapport à notre discussion précédente** : je n'ai pas présenté les agents, le streaming ou le tool calling comme déjà disponibles. Le dépôt contient actuellement les briques `AI`, `Catalog`, `Config`, `Http`, `Model`, `Options`, `Provider`, `Request`, `Response` et `Session`, avec OpenAI et OpenRouter comme providers visibles. 
G
GitHub
+1



Il y a également un point que je voudrais **corriger avant qu'on considère la librairie comme "finalisée"** : ton `OpenRouterProvider` et ton `OpenAIProvider` utilisent actuellement `HttpClientInterface`, ce qui est très bien, mais leurs implémentations doivent rester parfaitement alignées avec l'interface HTTP actuelle. Le dépôt montre notamment l'usage d'une méthode `post()`, donc je ne veux surtout pas qu'on continue à ajouter des couches théoriques sans vérifier la cohérence complète des contrats. 
G
GitHub
+1



Et surtout : **je pense qu'on devrait maintenant arrêter d'ajouter des fonctionna