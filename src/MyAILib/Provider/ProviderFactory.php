<?php


declare(strict_types=1);

namespace MyAILib\Provider;

final class ProviderFactory
{
    public function __construct(
        private readonly ProviderRegistry $registry
    )
    {
    }

    public function create(
        string $slug,
        array  $options = []
    ): ProviderInterface
    {
        return $this->registry->create(
            $slug,
            $options
        );
    }
}
