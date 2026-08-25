export const CONFIRM_WORDS = [
    'excluir',
    'remover',
    'apagar',
    'confirmar',
    'deletar',
] as const;

export function randomConfirmWord(): string {
    return CONFIRM_WORDS[Math.floor(Math.random() * CONFIRM_WORDS.length)];
}
