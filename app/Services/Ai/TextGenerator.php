<?php

namespace App\Services\Ai;

interface TextGenerator
{
    /**
     * Generate free-form text for the given prompt.
     *
     * @throws TextGenerationFailedException when the provider fails or returns nothing usable
     */
    public function generate(string $prompt): string;
}
