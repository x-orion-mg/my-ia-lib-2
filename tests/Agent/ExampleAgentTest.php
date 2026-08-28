<?php


declare(strict_types=1);

namespace MyAILib\Tests\Agent;

use MyAILib\AI\AIManager;
use MyAILib\Agent\ExampleAgent;
use MyAILib\Tests\FakeProvider;
use PHPUnit\Framework\TestCase;

final class ExampleAgentTest extends TestCase
{
    public function testAgentHasName(): void
    {
        $ai = new AIManager(
            new FakeProvider()
        );

        $agent = new ExampleAgent($ai);

        self::assertSame(
            'example-agent',
            $agent->name()
        );
    }

    public function testAgentHasInstructions(): void
    {
        $ai = new AIManager(
            new FakeProvider()
        );

        $agent = new ExampleAgent($ai);

        self::assertNotSame(
            '',
            $agent->instructions()
        );
    }

    public function testAgentCanRun(): void
    {
        $ai = new AIManager(
            new FakeProvider()
        );

        $agent = new ExampleAgent($ai);

        $this->assertSame(
            'Fake response: Bonjour',
            $agent->run('Bonjour')
        );
    }
}
