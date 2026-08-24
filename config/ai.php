<?php

return [

    /*
    |--------------------------------------------------------------------------
    | AI Text Generation Driver
    |--------------------------------------------------------------------------
    |
    | Provider used to generate product descriptions in the admin panel. The
    | default (gemini) has a generous free tier; switch via AI_DRIVER without
    | touching code. Supported: gemini, groq, ollama, openai, anthropic.
    |
    */

    'driver' => env('AI_DRIVER', 'gemini'),

    'timeout' => (int) env('AI_TIMEOUT', 20),

    'drivers' => [

        'gemini' => [
            'key' => env('GEMINI_API_KEY'),
            'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
            'base_url' => env('GEMINI_BASE_URL', 'https://generativelanguage.googleapis.com/v1beta'),
        ],

        'groq' => [
            'key' => env('GROQ_API_KEY'),
            'model' => env('GROQ_MODEL', 'llama-3.3-70b-versatile'),
            'base_url' => env('GROQ_BASE_URL', 'https://api.groq.com/openai/v1'),
        ],

        'ollama' => [
            'key' => null,
            'model' => env('OLLAMA_MODEL', 'llama3.2'),
            'base_url' => env('OLLAMA_URL', 'http://localhost:11434'),
        ],

        'openai' => [
            'key' => env('OPENAI_API_KEY'),
            'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
            'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        ],

        'anthropic' => [
            'key' => env('ANTHROPIC_API_KEY'),
            'model' => env('ANTHROPIC_MODEL', 'claude-haiku-4-5-20251001'),
            'base_url' => env('ANTHROPIC_BASE_URL', 'https://api.anthropic.com/v1'),
        ],

    ],

];
