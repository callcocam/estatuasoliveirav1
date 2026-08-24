<?php

return [

    'layout' => [
        'title' => 'Configurações',
        'description' => 'Gerencie seu perfil e as configurações da conta',
        'nav_label' => 'Configurações',
        'nav' => [
            'profile' => 'Perfil',
            'security' => 'Segurança',
            'teams' => 'Equipes',
            'appearance' => 'Aparência',
        ],
    ],

    'profile' => [
        'title' => 'Configurações do perfil',
        'heading' => 'Perfil',
        'heading_description' => 'Atualize seu nome e endereço de e-mail',
        'name_label' => 'Nome',
        'name_placeholder' => 'Nome completo',
        'email_label' => 'Endereço de e-mail',
        'email_placeholder' => 'Endereço de e-mail',
        'email_unverified' => 'Seu endereço de e-mail ainda não foi verificado.',
        'resend_verification' => 'Clique aqui para reenviar o e-mail de verificação.',
        'verification_link_sent' => 'Um novo link de verificação foi enviado para o seu endereço de e-mail.',
    ],

    'appearance' => [
        'title' => 'Configurações de aparência',
        'heading' => 'Configurações de aparência',
        'heading_description' => 'Atualize as configurações de aparência da sua conta',
        'themes' => [
            'light' => 'Claro',
            'dark' => 'Escuro',
            'system' => 'Sistema',
        ],
    ],

    'security' => [
        'title' => 'Configurações de segurança',
    ],

    'password' => [
        'heading' => 'Atualizar senha',
        'heading_description' => 'Garanta que sua conta esteja usando uma senha longa e aleatória para se manter segura',
        'current_label' => 'Senha atual',
        'current_placeholder' => 'Senha atual',
        'new_label' => 'Nova senha',
        'new_placeholder' => 'Nova senha',
        'confirm_label' => 'Confirmar senha',
        'confirm_placeholder' => 'Confirmar senha',
        'show' => 'Mostrar senha',
        'hide' => 'Ocultar senha',
    ],

    'two_factor' => [
        'heading' => 'Autenticação de dois fatores',
        'heading_description' => 'Gerencie as configurações da sua autenticação de dois fatores',
        'disabled_description' => 'Ao ativar a autenticação de dois fatores, será solicitado um PIN seguro durante o login. Esse PIN pode ser obtido em um aplicativo compatível com TOTP no seu celular.',
        'enabled_description' => 'Será solicitado um PIN seguro e aleatório durante o login, que você pode obter no aplicativo compatível com TOTP no seu celular.',
        'continue_setup' => 'Continuar configuração',
        'enable' => 'Ativar 2FA',
        'disable' => 'Desativar 2FA',

        'setup_modal' => [
            'enabled_title' => 'Autenticação de dois fatores ativada',
            'enabled_description' => 'A autenticação de dois fatores está ativada. Escaneie o QR code ou insira a chave de configuração no seu aplicativo autenticador.',
            'verify_title' => 'Verificar código de autenticação',
            'verify_description' => 'Insira o código de 6 dígitos do seu aplicativo autenticador',
            'enable_title' => 'Ativar autenticação de dois fatores',
            'enable_description' => 'Para concluir a ativação da autenticação de dois fatores, escaneie o QR code ou insira a chave de configuração no seu aplicativo autenticador',
            'manual_entry' => 'ou insira o código manualmente',
        ],

        'recovery_codes' => [
            'heading' => 'Códigos de recuperação 2FA',
            'description' => 'Os códigos de recuperação permitem recuperar o acesso caso você perca seu dispositivo 2FA. Guarde-os em um gerenciador de senhas seguro.',
            'view' => 'Ver códigos de recuperação',
            'hide' => 'Ocultar códigos de recuperação',
            'regenerate' => 'Gerar novos códigos',
            'usage_note' => 'Cada código de recuperação pode ser usado uma única vez para acessar sua conta e será removido após o uso. Se precisar de mais, clique em',
            'usage_note_action' => 'Gerar novos códigos',
            'usage_note_suffix' => 'acima.',
        ],
    ],

    'passkeys' => [
        'heading' => 'Chaves de acesso',
        'heading_description' => 'Gerencie suas chaves de acesso para entrar sem senha',
        'empty_title' => 'Nenhuma chave de acesso ainda',
        'empty_description' => 'Adicione uma chave de acesso para entrar sem senha',

        'register' => [
            'not_supported' => 'Chaves de acesso não são compatíveis com este navegador.',
            'add' => 'Adicionar chave de acesso',
            'name_label' => 'Nome da chave de acesso',
            'name_placeholder' => 'ex.: MacBook Pro, iPhone',
            'name_help' => 'Um nome ajuda você a identificar esta chave de acesso mais tarde.',
            'submit' => 'Registrar chave de acesso',
            'submitting' => 'Registrando…',
        ],

        'item' => [
            'added' => 'Adicionada :date',
            'last_used' => 'Último uso :date',
            'remove' => 'Remover',
            'remove_title' => 'Remover chave de acesso',
            'remove_description' => 'Tem certeza de que deseja remover a chave de acesso ":name"? Você não poderá mais usá-la para entrar.',
            'remove_confirm' => 'Remover chave de acesso',
            'removing' => 'Removendo…',
        ],

        'verify' => [
            'label' => 'Entrar com uma chave de acesso',
            'loading' => 'Autenticando…',
            'separator' => 'ou continue com e-mail',
        ],
    ],

    'delete_account' => [
        'heading' => 'Excluir conta',
        'heading_description' => 'Exclua sua conta e todos os seus recursos',
        'warning_title' => 'Atenção',
        'warning_description' => 'Prossiga com cautela: esta ação não poderá ser desfeita.',
        'button' => 'Excluir conta',
        'confirm_title' => 'Tem certeza de que deseja excluir sua conta?',
        'confirm_description' => 'Depois que sua conta for excluída, todos os seus recursos e dados também serão excluídos permanentemente. Digite sua senha para confirmar que deseja excluir sua conta de forma permanente.',
        'password_label' => 'Senha',
        'password_placeholder' => 'Senha',
    ],

];
