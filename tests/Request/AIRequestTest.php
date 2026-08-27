<?php


declare(strict_types=1);

namespace MyAILib\Tests\Request;

use MyAILib\Message\Message;
use MyAILib\Message\MessageRole;
use MyAILib\Request\AIRequest;
use PHPUnit\Framework\TestCase;

final class AIRequestTest extends TestCase
{
    public function testRequestCanBeCreatedFromPrompt(): void
    {
        $request = AIRequest::fromPrompt('Bonjour');

        $this->assertCount(
            1,
            $request->messages()
        );

        $this->assertSame(
            'Bonjour',
            $request->getPrompt()
        );
    }

    public function testRequestSupportsConversation(): void
    {
        $request = new AIRequest([
            new Message(
                MessageRole::SYSTEM,
                'Tu es un assistant.'
            ),
            new Message(
                MessageRole::USER,
                'Bonjour'
            ),
            new Message(
                MessageRole::ASSISTANT,
                'Bonjour !'
            ),
            new Message(
                MessageRole::USER,
                'Comment vas-tu ?'
            ),
        ]);

        $this->assertCount(
            4,
            $request->messages()
        );

        $this->assertSame(
            'Comment vas-tu ?',
            $request->getPrompt()
        );

        $this->assertSame(
            'system',
            $request->toArray()[0]['role']
        );

        $this->assertSame(
            'assistant',
            $request->toArray()[2]['role']
        );
    }
}
