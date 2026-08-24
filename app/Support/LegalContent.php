<?php

namespace App\Support;

class LegalContent
{
    /**
     * Resolve the default legal sections for a page ("terms" or "privacy"),
     * replacing company placeholders with data from the site settings.
     *
     * @return array{title: string, intro: string, updated: string, sections: list<array{title: string, body: string}>}
     */
    public static function for(string $page): array
    {
        $company = CompanyProfile::toArray();

        $contact = is_string($company['email']) && $company['email'] !== ''
            ? __('app.site.legal.contact_via_email', ['email' => $company['email']])
            : __('app.site.legal.contact_via_page');

        $replacements = [
            ':company' => $company['name'],
            ':contact' => $contact,
        ];

        $sections = array_map(fn (array $section): array => [
            'title' => strtr($section['title'], $replacements),
            'body' => strtr($section['body'], $replacements),
        ], __("app.site.legal.{$page}.sections"));

        return [
            'title' => __("app.site.legal.{$page}.title"),
            'intro' => strtr(__("app.site.legal.{$page}.intro"), $replacements),
            'updated' => __('app.site.legal.updated'),
            'sections' => $sections,
        ];
    }
}
