<?php


declare(strict_types=1);

namespace MyAILib\Tests\Exception;

use MyAILib\Exception\AIException;
use MyAILib\Exception\AuthenticationException;
use MyAILib\Exception\InvalidRequestException;
use MyAILib\Exception\ProviderException;
use MyAILib\Exception\RateLimitException;
use MyAILib\Exception\NetworkException;
use PHPUnit\Framework\TestCase;

final class ExceptionTest extends TestCase
{
    public function testProviderExceptionContainsProviderAndStatus(): void
    {
        $exception = new ProviderException(
            'Something went wrong',
            'openai',
            500
        );

        $this->assertInstanceOf(
            AIException::class,
            $exception
        );

        $this->assertSame(
            'openai',
            $exception->provider()
        );

        $this->assertSame(
            500,
            $exception->statusCode()
        );
    }

    public function testSpecializedExceptionsAreProviderExceptions(): void
    {
        $exceptions = [
            new AuthenticationException('Auth failed', 'openai', 401),
            new RateLimitException('Rate limited', 'openai', 429),
            new InvalidRequestException('Bad request', 'openai', 400),
        ];

        foreach ($exceptions as $exception) {
            $this->assertInstanceOf(
                ProviderException::class,
                $exception
            );

            $this->assertInstanceOf(
                AIException::class,
                $exception
            );
        }
    }

    public function testNetworkExceptionIsAIException(): void
    {
        $exception = new NetworkException(
            'Connection failed'
        );

        $this->assertInstanceOf(
            AIException::class,
            $exception
        );
    }
}
