<?php

declare(strict_types=1);

namespace MyAILib;

use MyAILib\AI\AIManager;
use MyAILib\Config\ConfigLoader;
use MyAILib\Provider\OpenRouter\OpenRouterProvider;
use MyAILib\Provider\ProviderFactory;
use MyAILib\Provider\ProviderRegistry;
use MyAILib\Response\AIResponse;
use MyAILib\Session\AISession;
use MyAILib\Session\FileSessionStore;
use MyAILib\Session\SessionStoreInterface;
use RuntimeException;

final class MyAI
{
    private readonly SessionStoreInterface $sessionStore;

    private function __construct(
        private readonly AIManager $manager
    ) {
    }

    /**
     * Crée une instance MyAI avec une configuration simple.
     *
     * Exemple :
     *
     * $ai = MyAI::create([
     *     'provider' => 'openrouter',
     *     'api_key' => '...',
     *     'model' => 'openai/gpt-5',
     * ]);
     *
     * @param array<string, mixed> $options
     */
    public static function create(
        array $options = []
    ): self {
        $provider = $options['provider'] ?? 'openrouter';

        if (!is_string($provider) || $provider === '') {
            throw new RuntimeException(
                'The provider must be a non-empty string.'
            );
        }

        $apiKey = $options['api_key'] ?? null;

        if ($apiKey !== null && !is_string($apiKey)) {
            throw new RuntimeException(
                'The api_key must be a string.'
            );
        }

        $providerOptions = $options;

        unset(
            $providerOptions['provider'],
            $providerOptions['session_directory']
        );

        $registry = new ProviderRegistry();

        self::registerBuiltInProviders(
            $registry
        );

        $config = ConfigLoader::fromArray([
            'default_provider' => $provider,

            'providers' => [
                $provider => $providerOptions,
            ],
        ]);

        $factory = new ProviderFactory(
            $registry,
            $config
        );

        $sessionDirectory = $options['session_directory']
            ?? dirname(__DIR__, 2) . '/storage/sessions';

        if (!is_string($sessionDirectory)) {
            throw new RuntimeException(
                'The session_directory must be a string.'
            );
        }

        $sessionStore = new FileSessionStore(
            $sessionDirectory
        );

        $manager = AIManager::create(
            $provider,
            $factory,
            [],
            $sessionStore
        );

        return new self($manager);
    }

    /**
     * Envoie une question à l'IA.
     */
    public function ask(
        string $prompt
    ): AIResponse {
        return $this->manager->ask(
            $prompt
        );
    }

    /**
     * Démarre ou recharge une conversation.
     */
    public function session(
        string $id
    ): self {
        $this->manager->startSession(
            $id
        );

        return $this;
    }

    /**
     * Définit le system prompt de la conversation courante.
     */
    public function setSystemPrompt(
        string $prompt
    ): self {
        $this->manager->setSystemPrompt(
            $prompt
        );

        return $this;
    }

    /**
     * Retourne le provider actuellement utilisé.
     */
    public function provider()
    {
        return $this->manager->getProvider();
    }

    /**
     * Retourne le gestionnaire interne.
     *
     * Utile pour les utilisateurs avancés.
     */
    public function manager(): AIManager
    {
        return $this->manager;
    }

    /**
     * Enregistre les providers intégrés à la librairie.
     */
    private static function registerBuiltInProviders(
        ProviderRegistry $registry
    ): void {
        $registry->register(
            'openrouter',
            OpenRouterProvider::class
        );
    }

    public function history(): array
    {
        $session = $this->manager->getSession();

        if ($session === null) {
            return [];
        }

        return $session->messages();
    }
    public function systemPrompt(): ?string
    {
        return $this->manager->getSystemPrompt();
    }

    public function sessionData(): ?AISession
    {
        return $this->manager->getSession();
    }
    public function sessions(): array
    {
        return $this->sessionStore->all();
    }

}