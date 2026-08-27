<?php

declare(strict_types=1);

namespace MyAILib\Tests\Options;

use InvalidArgumentException;
use MyAILib\Options\GenerationOptions;
use PHPUnit\Framework\TestCase;

final class GenerationOptionsTest extends TestCase
{
    public function testOptionsAreConvertedToProviderFormat(): void
    {
        $options = new GenerationOptions(
            temperature: 0.7,
            maxTokens: 500,
            topP: 0.9,
            frequencyPenalty: 0.2,
            presencePenalty: 0.1,
        );

        $this->assertSame(
            [
                'temperature' => 0.7,
                'max_tokens' => 500,
                'top_p' => 0.9,
                'frequency_penalty' => 0.2,
                'presence_penalty' => 0.1,
            ],
            $options->toArray()
        );
    }

    public function testNullOptionsAreNotIncluded(): void
    {
        $options = new GenerationOptions(
            temperature: 0.7
        );

        $this->assertSame(
            [
                'temperature' => 0.7,
            ],
            $options->toArray()
        );
    }

    public function testInvalidTemperatureThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GenerationOptions(
            temperature: 3.0
        );
    }

    public function testInvalidMaxTokensThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GenerationOptions(
            maxTokens: 0
        );
    }

    public function testInvalidTopPThrowsException(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new GenerationOptions(
            topP: 1.5
        );
    }
}
