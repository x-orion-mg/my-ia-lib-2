<?php

declare(strict_types=1);

namespace MyAILib\Examples\Agent;

use MyAILib\Agent\AbstractAgent;

final class RecipeAgent extends AbstractAgent
{
    public function name(): string
    {
        return 'recipe-agent';
    }

    public function instructions(): string
    {
        return <<<PROMPT
Tu es un assistant spécialisé dans les recettes de cuisine.

Ton rôle est de :
- proposer des recettes adaptées à la demande ;
- donner les ingrédients avec les quantités ;
- expliquer les étapes clairement ;
- proposer des alternatives si un ingrédient manque ;
- répondre en français.

Ne donne pas d'informations inutiles.
PROMPT;
    }

    public function run(string $input): string
    {
        return $this->ask($input);
    }
}