# My AI Lib

My AI Lib est une librairie PHP permettant d'intégrer des modèles d'intelligence artificielle dans une application de manière simple, extensible et indépendante du provider utilisé.

L'objectif est de fournir une API commune permettant de communiquer avec différents providers d'IA tout en conservant une architecture extensible.

La librairie gère notamment :

* les providers d'IA ;
* la sélection du provider ;
* la sélection du modèle ;
* le fallback entre plusieurs modèles ;
* les requêtes et réponses IA ;
* les sessions de conversation ;
* la persistance des conversations ;
* l'historique des messages ;
* les system prompts ;
* les informations d'utilisation (usage) ;
* le finish_reason ;
* les métadonnées retournées par les providers ;
* la création d'agents utilisant la librairie.

--------------------

## Prérequis

* PHP 8.2 ou supérieur
* Composer

------------------

## Installation

Depuis ton projet :

`composer require x-orion-mg/my-ai-lib`

Puis :

`composer install`

-----------------

## Utilisation rapide

La classe MyAI constitue le point d'entrée simplifié de la librairie.

L'utilisateur n'a normalement pas besoin de créer manuellement :

* `ProviderRegistry`
* `ProviderFactory`
* `ConfigLoader`
* `FileSessionStore`

pour une utilisation classique.

## Exemple minimal
```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use MyAILib\MyAI;

$ai = MyAI::create([
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),
    'model' => 'openai/gpt-5',
]);

$response = $ai->ask(
    'Explique-moi simplement ce qu’est une API.'
);

echo $response->text();
```

La réponse est représentée par un objet `AIResponse`.

------------

# Configuration
## Provider

Le provider peut être sélectionné lors de la création :
```php
$ai = MyAI::create([
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),
]);
```
Si aucun provider n'est spécifié, la librairie utilise le provider configuré par défaut.

Exemple :
```php
$ai = MyAI::create([
    'provider' => 'openrouter',
]);
```
-------------------
## API Key

Il est recommandé de ne jamais écrire une clé API directement dans le code.

Utilisez une variable d'environnement :

`OPENROUTER_API_KEY=sk-or-xxxxxxxx`

Puis :
```php
$ai = MyAI::create([
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),
]);
```
Ne commitez jamais votre clé API dans Git.

---------------

## Sélection du modèle
Un modèle peut être sélectionné directement :

```php
$ai = MyAI::create([
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),
    'model' => 'openai/gpt-5',
]);
```
Les options du provider peuvent également être configurées :

```php
$ai = MyAI::create([
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),
    'model' => 'openai/gpt-5',
    'temperature' => 0.7,
    'max_tokens' => 500,
]);
```
---------------

## Fallback entre plusieurs modèles

Il est possible de définir plusieurs modèles.

Si le premier modèle ne peut pas répondre, le provider peut essayer le modèle suivant.

```php
$ai = MyAI::create([
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),

    'models_list' => [
        'openai/gpt-5',
        'nvidia/nemotron-3-nano-30b-a3b:free',
        'allenai/olmo-3.1-32b-think:free',
    ],
]);
```

L'ordre de la liste détermine l'ordre des tentatives.

---

## Envoyer une requête
La méthode principale est :
```php
$response = $ai->ask(
    'Bonjour, explique-moi ce qu’est une API.'
);
```
Le résultat est un AIResponse.

Pour récupérer le texte :
```php
echo $response->text();
```

---

# Informations de la réponse
Une réponse contient plusieurs informations utiles.

## Provider utilisé
```php
$response->provider();
```
Exemple :

`openrouter`

## Modèle utilisé
```php
$response->model();
```
Exemple :

`openai/gpt-5`

## Finish reason
```php
$response->finishReason();
```

Cette information permet de connaître la raison pour laquelle le provider a terminé la génération.

Exemple :

`stop`

## Usage
```php
$response->usage();
```
Selon le provider, cette information peut notamment contenir le nombre de tokens utilisés.

Exemple :
```php
print_r(
    $response->usage()
);
```

## Métadonnées
Les métadonnées permettent de conserver les informations supplémentaires fournies par le provider.

```php
$response->metadata();
```

Exemple :
```php
print_r(
    $response->metadata()
);
```
---

# Sessions et conversations
My AI Lib possède un système de sessions permettant de conserver l'historique d'une conversation.

Une session représente une conversation indépendante.

```
Session
├── message utilisateur
├── réponse IA
├── message utilisateur
├── réponse IA
└── ...
```
----

# Démarrer une session
```php
$ai->session(
    'conversation-123'
);
```
Si la session existe déjà, elle peut être rechargée.

Si elle n'existe pas, elle est créée.

---

# System Prompt

Un system prompt peut être associé à la conversation :

```php
$ai
    ->session('conversation-123')
    ->setSystemPrompt(
        'Tu es un assistant français. Réponds clairement.'
    );
```
Puis :
```php
$response = $ai->ask(
    'Bonjour'
);
```
Le system prompt est conservé avec la session.

---

# Conversations persistantes

La librairie peut utiliser FileSessionStore pour conserver les conversations sur disque.

Par défaut, les sessions sont stockées dans :
```
storage/sessions/
```
Chaque conversation est persistée sous forme de fichier JSON.

Exemple conceptuel :
```
storage/
└── sessions/
    ├── 8f7c....json
    ├── 2a91....json
    └── ...
```
L'identifiant de session n'est pas directement utilisé comme nom de fichier. Il est hashé afin d'éviter notamment les problèmes liés aux caractères spéciaux ou au path traversal.

---
# Reprendre une conversation
Une conversation peut être reprise ultérieurement avec le même identifiant :

```php
$ai->session(
    'conversation-123'
);

$response = $ai->ask(
    'Peux-tu continuer notre conversation ?'
);
```
---

# La session existante est chargée automatiquement.

Historique d'une conversation
L'historique de la session peut être récupéré avec :
```php
$history = $ai->history();
```

Puis :
```php
foreach ($history as $message) {
    echo $message->role()->value;
    echo ': ';
    echo $message->content();
}
```
Les messages possèdent notamment :
```php
$message->role();
$message->content();
```
Les rôles utilisés sont notamment :
```
system
user
assistant
```

---
# Sessions en mémoire
Pour les tests ou les utilisations temporaires, MemorySessionStore permet de conserver les sessions uniquement en mémoire.
```php
$store = new MemorySessionStore();
```

Ce stockage est particulièrement adapté aux tests automatisés.

Les données sont perdues lorsque le processus PHP se termine.

---

# Architecture
L'architecture interne est volontairement séparée en plusieurs composants.
```
MyAI
 │
 ▼
AIManager
 │
 ├── ProviderFactory
 │      │
 │      └── ProviderRegistry
 │
 ├── SessionStore
 │
 └── Provider
        │
        ▼
   API du provider
```
---
# MyAI
MyAI est la façade simplifiée destinée à l'utilisateur final.

Exemple :
```php
$ai = MyAI::create([
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),
]);
```
Elle évite à l'utilisateur de devoir construire manuellement toute l'infrastructure.

---
# AIManager
AIManager constitue le gestionnaire principal des interactions avec un provider.

Il gère notamment :

* l'envoi des requêtes ;
* les sessions ;
* le contexte de conversation ;
* le system prompt ;
* la mise à jour de l'historique.
Les utilisateurs avancés peuvent accéder au manager :
```php
$manager = $ai->manager();
```
---
# ProviderInterface
Tous les providers doivent implémenter :
```
ProviderInterface
```

Cela permet à la librairie d'utiliser différents providers derrière une interface commune.

L'objectif est de pouvoir passer de :

`OpenRouter`

à :
```
OpenAI
Anthropic
Google
Mistral
...
```
sans modifier le fonctionnement général de l'application.

---
# ProviderRegistry

Le ProviderRegistry associe un identifiant à une classe de provider.

Exemple :
```php
$registry->register(
    'openrouter',
    OpenRouterProvider::class
);
```

Le registry permet de retrouver les providers disponibles.

---
# ProviderFactory
ProviderFactory est responsable de créer une instance configurée d'un provider.
```php
$provider = $factory->create(
    'openrouter'
);
```
Il permet également de gérer la configuration du provider et les options spécifiques.

---

# Ajouter un nouveau provider
L'architecture est conçue pour permettre l'ajout de nouveaux providers.

Un provider doit implémenter :

`ProviderInterface`

Exemple conceptuel :
```php
final class MyProvider implements ProviderInterface
{
    public function configure(array $options): void
    {
        // Configuration
    }

    public function ask(AIRequest $request): AIResponse
    {
        // Appel de l'API
    }
}
```
Il peut ensuite être enregistré dans le registry :
```php
$registry->register(
    'my-provider',
    MyProvider::class
);
```
L'objectif à terme est de permettre l'installation de providers supplémentaires sans modifier le cœur de My AI Lib.

---
# Agents
Les agents ne sont pas des providers.

Un agent utilise les fonctionnalités de My AI Lib pour réaliser une tâche spécifique.

Les classes génériques liées aux agents appartiennent à la librairie :
```
src/MyAILib/Agent/
├── AgentInterface.php
└── AbstractAgent.php
```
En revanche, les agents concrets appartiennent à l'application qui utilise la librairie.

Exemple :
```
examples/
└── Agent/
    └── RecipeAgent.php
```
Exemple d'agent
```php
<?php

declare(strict_types=1);

namespace MyAILib\Examples\Agent;

use MyAILib\Agent\AbstractAgent;

final class RecipeAgent extends AbstractAgent
{
    public function name(): string
    {
        return 'recipe-agent';
    }

    public function instructions(): string
    {
        return <<<PROMPT
Tu es un assistant spécialisé dans les recettes de cuisine.

Tu dois :
- proposer des recettes adaptées ;
- donner les ingrédients ;
- expliquer les étapes ;
- répondre en français.
PROMPT;
    }

    public function run(string $input): string
    {
        return $this->ask($input);
    }
}
```
Puis :
```php
$agent = new RecipeAgent($ai);

$response = $agent->run(
    'Donne-moi une recette avec des tomates et des œufs.'
);

echo $response;
```
L'agent utilise donc AIManager et les fonctionnalités de My AI Lib, mais sa logique métier reste dans l'application.

---

# Organisation recommandée d'une application
Une application utilisant My AI Lib peut avoir une structure comme :
```
my-project/
├── src/
│   ├── Agent/
│   │   ├── RecipeAgent.php
│   │   └── SupportAgent.php
│   │
│   └── Tools/
│
├── storage/
│   └── sessions/
│
├── config/
│
├── public/
│
├── vendor/
│
└── composer.json
```

My AI Lib reste une dépendance :
```
vendor/
└── x-orion-mg/
    └── my-ai-lib/
``` 
---
# Sécurité
Ne mettez jamais une clé API directement dans le dépôt Git.

À éviter :
```php
'api_key' => 'sk-or-xxxxxxxx'
```
Préférez :
```php
'api_key' => getenv('OPENROUTER_API_KEY')
```

Ajoutez également les fichiers de stockage local au .gitignore :
```
/vendor/
/storage/sessions/
/.env
```
---
# Tests
Le projet utilise PHPUnit.

Pour lancer les tests :
```
composer test
```
Les tests couvrent notamment :

* les providers ;
* les requêtes ;
* les réponses ;
* les sessions ;
* la persistance ;
* le registry ;
* la factory ;
* la configuration ;
* le AIManager ;
* le fallback entre modèles.

---

# État actuel
Les fonctionnalités principales actuellement mises en place sont :

1. [x]  architecture des providers ;
2. [x]  ProviderInterface ;
3. [x]  ProviderRegistry ;
4. [x]  ProviderFactory ;
5. [x]  configuration ;
6. [x]  HTTP client ;
7. [x]  AIRequest ;
8. [x]  AIResponse ;
9. [x]  provider OpenRouter ;
10. [x]  fallback entre modèles ;
11. [x]  température et max_tokens ;
12. [x]  finish_reason ;
13. [x]  sessions ;
14. [x]  AISession ;
15. [x]  MemorySessionStore ;
16. [x]  FileSessionStore ;
17. [x]  persistance des conversations ;
18. [x]  system prompt ;
19. [x]  historique d'une conversation ;
20. [x]  sélection du provider par défaut ;
21. [x]  AgentInterface ;
22. [x]  AbstractAgent ;
23. [x]  façade MyAI ;
24. [x]  exemple RecipeAgent.

---

# Roadmap
Les prochaines évolutions prévues sont notamment :

1. améliorer l'API publique de MyAI ;
2. finaliser la récupération de l'historique de toutes les conversations ;
3. exposer proprement les métadonnées des réponses ;
4. améliorer la gestion des modèles et providers ;
5. permettre l'ajout de providers externes plus facilement ;
6. développer le système d'agents ;
7. ajouter un système de Tools ;
8. permettre aux agents d'utiliser plusieurs outils ;
9. améliorer la gestion du contexte ;
10. préparer une architecture adaptée aux applications IA plus complexes.

La persistance par base de données n'est volontairement pas incluse dans le cœur de My AI Lib.

Une application ou un agent pourra plus tard implémenter son propre stockage, par exemple avec :
```
My AI Lib
    │
    └── SessionStoreInterface
             │
             ├── MemorySessionStore
             ├── FileSessionStore
             └── DatabaseSessionStore (application)
```

Cela permet de garder la librairie indépendante d'une base de données particulière.
***

# Philosophie du projet
My AI Lib cherche à respecter trois principes :

## Simple à utiliser
Une utilisation classique doit pouvoir commencer avec :
```php
$ai = MyAI::create([
    'provider' => 'openrouter',
    'api_key' => getenv('OPENROUTER_API_KEY'),
]);

$response = $ai->ask('Bonjour');
```
## Extensible
Les providers, sessions et composants internes doivent pouvoir être remplacés ou étendus.

## Indépendant
Le code métier d'une application ne doit pas être fortement dépendant d'un provider particulier.

L'application doit pouvoir utiliser :
```
OpenRouter
    ↓
My AI Lib
    ↓
Application
```
sans que toute l'application soit construite directement autour de l'API OpenRouter.
***
# Licence
Voir le fichier LICENSE.

Cette version documente **ce qu'on a réellement construit** et garde la distinction importante entre **la librairie** et les **agents concrets de l'application**.
