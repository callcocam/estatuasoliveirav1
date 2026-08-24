<?php

namespace App\Services\Ai;

class ProductDescriptionGenerator
{
    public function __construct(private readonly TextGenerator $generator) {}

    /**
     * Generate a commercial description from the product attributes typed in
     * the admin form. Only informed facts reach the prompt so the model does
     * not invent measurements or materials.
     *
     * Expected keys: name (required), category, reference, width_cm,
     * height_cm and weight_kg — the validated payload of
     * GenerateProductDescriptionRequest.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function generate(array $attributes): string
    {
        return $this->generator->generate($this->buildPrompt($attributes));
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function buildPrompt(array $attributes): string
    {
        $fact = function (string $key, string $label, string $suffix = '') use ($attributes): ?string {
            $value = trim((string) ($attributes[$key] ?? ''));

            return $value === '' ? null : "{$label}: {$value}{$suffix}";
        };

        $facts = array_filter([
            'Nome do produto: '.trim((string) ($attributes['name'] ?? '')),
            $fact('category', 'Categoria'),
            $fact('reference', 'Referência'),
            $fact('width_cm', 'Largura', ' cm'),
            $fact('height_cm', 'Altura', ' cm'),
            $fact('weight_kg', 'Peso', ' kg'),
        ]);

        $factList = implode("\n", $facts);

        return <<<PROMPT
        Você é redator de uma loja de estátuas, vasos, fontes e decorações artesanais em cimento, mármore e gesso.

        Escreva a descrição comercial do produto abaixo em português do Brasil, com 2 a 3 parágrafos curtos de texto corrido.

        Regras:
        - Use apenas as informações fornecidas; não invente medidas, materiais ou características.
        - Não use markdown, listas, títulos ou emojis.
        - Responda somente com a descrição, sem introduções ou comentários.

        {$factList}
        PROMPT;
    }
}
