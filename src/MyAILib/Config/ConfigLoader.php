<?php


declare(strict_types=1);

namespace MyAILib\Config;

use InvalidArgumentException;

final class ConfigLoader
{
    public static function fromArray(array $data): AIConfig
    {
        return new AIConfig(
            providers: $data['providers'] ?? [],
            defaultProvider: $data['default_provider'] ?? null,
        );
    }


    public static function fromFile(string $file): AIConfig
    {
        if (!is_file($file)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Configuration file "%s" does not exist.',
                    $file
                )
            );
        }

        $config = require $file;

        if (!is_array($config)) {
            throw new InvalidArgumentException(
                'Configuration file must return an array.'
            );
        }

        return self::fromArray($config);
    }
}
