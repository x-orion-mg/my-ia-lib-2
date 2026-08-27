<?php

declare(strict_types=1);

namespace MyAILib\AI;

use MyAILib\Request\AIRequest;
use MyAILib\Response\AIResponse;

interface AIInterface
{
    public function ask(string|AIRequest $request): AIResponse;
}
