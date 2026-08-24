import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type TranslationTree = { [key: string]: string | TranslationTree };

type Replacements = Record<string, string | number>;

/**
 * Lightweight translation composable backed by the `translations` Inertia
 * prop shared from the backend (see HandleInertiaRequests::share()).
 *
 * Keys use dot notation mirroring the lang file tree, e.g.
 * `t('app.auth.login.title')`. Placeholders use Laravel's `:name` syntax and
 * are replaced by the optional second argument. Unknown keys return the key
 * itself so missing translations are visible on screen.
 */
export function useT() {
    const page = usePage();

    const translations = computed<TranslationTree>(
        () => (page.props.translations as TranslationTree | undefined) ?? {},
    );

    const locale = computed<string>(() => (page.props.locale as string | undefined) ?? 'pt_BR');

    function t(key: string, replacements?: Replacements): string {
        let node: string | TranslationTree | undefined = translations.value;

        for (const segment of key.split('.')) {
            if (typeof node !== 'object' || node === null) {
                node = undefined;
                break;
            }

            node = node[segment];
        }

        if (typeof node !== 'string') {
            return key;
        }

        if (!replacements) {
            return node;
        }

        return Object.entries(replacements).reduce(
            (message, [name, value]) => message.replaceAll(`:${name}`, String(value)),
            node,
        );
    }

    return { t, locale };
}
