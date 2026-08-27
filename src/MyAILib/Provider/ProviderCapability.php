<?php


declare(strict_types=1);

namespace MyAILib\Provider;

enum ProviderCapability: string
{
    case CHAT = 'chat';
    case VISION = 'vision';
    case TOOLS = 'tools';
    case JSON = 'json';
    case STREAMING = 'streaming';
}
