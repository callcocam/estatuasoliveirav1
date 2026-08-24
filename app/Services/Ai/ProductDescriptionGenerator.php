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
     * @param  array{name: string, category?: string|null, reference?: string|null, width_cm?: int|string|null, height_cm?: int|string|null, weight_kg?: float|string|null}  $attributes
     */
    public function generate(array $attributes): string
    {
        return $this->generator->generate($this->buildPrompt($attributes));
    }

    /**
     * @param  array{name: string, category?: string|null, reference?: string|null, width_cm?: int|string|null, height_cm?: int|string|null, weight_kg?: float|string|null}  $attributes
     */
    private function buildPrompt(array $attributes): string
    {
        $facts = array_filter([
            'Nome do produto: '.$attributes['name'],
            filled($attributes['category'] ?? null) ? 'Categoria: '.$attributes['category'] : null,
            filled($attributes['reference'] ?? null) ? 'Referência: '.$attributes['reference'] : null,
            filled($attributes['width_cm'] ?? null) ? "Largura: {$attributes['width_cm']} cm" : null,
            filled($attributes['height_cm'] ?? null) ? "Altura: {$attributes['height_cm']} cm" : null,
            filled($attributes['weight_kg'] ?? null) ? "Peso: {$attributes['weight_kg']} kg" : null,
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
