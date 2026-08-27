<?php

declare(strict_types=1);

namespace MyAILib\AI;

use MyAILib\Message\Message;
use MyAILib\Message\MessageRole;
use MyAILib\Provider\ProviderFactory;
use MyAILib\Provider\ProviderInterface;
use MyAILib\Request\AIRequest;
use MyAILib\Response\AIResponse;
use MyAILib\Session\AISession;
use MyAILib\Session\SessionStoreInterface;
use RuntimeException;

final class AIManager implements AIInterface
{
    private ?AISession $session = null;

    public function __construct(
        private readonly ProviderInterface $provider,
        private readonly ?SessionStoreInterface $sessionStore = null,
    ) {
    }

    public static function create(
        ?string $providerSlug,
        ProviderFactory $factory,
        array $options = [],
        ?SessionStoreInterface $sessionStore = null
    ): self {

        $providerSlug = $factory->resolve($providerSlug);

        return new self(
            $factory->create(
                $providerSlug,
                $options
            ),
            $sessionStore
        );
    }


    public function startSession(string $id): void
    {
        if ($this->sessionStore === null) {
            throw new RuntimeException(
                'A SessionStoreInterface is required to use sessions.'
            );
        }

        $session = $this->sessionStore->get($id);

        if ($session === null) {
            $session = new AISession($id);
            $this->sessionStore->save($session);
        }

        if (!$session instanceof AISession) {
            throw new RuntimeException(
                'Session must be an instance of AISession.'
            );
        }

        $this->session = $session;
    }

    public function ask(
        string|AIRequest $request
    ): AIResponse {
        if (is_string($request)) {
            $request = AIRequest::fromPrompt($request);
        }

        $contextRequest = $this->addSessionContext($request);

        $response = $this->provider->ask($contextRequest);

        $this->updateSession(
            $request,
            $response
        );


        return $response;
    }

    public function getProvider(): ProviderInterface
    {
        return $this->provider;
    }

    public function getSession(): ?AISession
    {
        return $this->session;
    }

    private function addSessionContext(
        AIRequest $request
    ): AIRequest {
        if ($this->session === null) {
            return $request;
        }

        $messages = [];

        $systemPrompt = $this->session->getSystemPrompt();

        if ($systemPrompt !== null) {
            $messages[] = new Message(
                MessageRole::SYSTEM,
                $systemPrompt
            );
        }

        $messages = [
            ...$messages,
            ...$this->session->messages(),
            ...$request->messages(),
        ];


        return new AIRequest(
            messages: $messages,
            options: $request->options()
        );
    }

    private function updateSession(
        AIRequest $request,
        AIResponse $response
    ): void {
        if ($this->session === null) {
            return;
        }

        foreach ($request->messages() as $message) {
            $this->session->addMessage($message);
        }

        $this->session->addMessage(
            new Message(
                MessageRole::ASSISTANT,
                $response->text()
            )
        );

        $this->sessionStore?->save($this->session);
    }

    public function setSystemPrompt(string $prompt): void
    {
        if ($this->session === null) {
            throw new \RuntimeException(
                'A session is required to set a system prompt.'
            );
        }

        $this->session->setSystemPrompt($prompt);

        $this->sessionStore?->save($this->session);
    }

    public function getSystemPrompt(): ?string
    {
        return $this->session?->getSystemPrompt();
    }

}
