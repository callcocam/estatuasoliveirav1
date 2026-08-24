<?php

return [

    'fields' => [
        'name' => 'Nome',
        'name_placeholder' => 'Nome completo',
        'email' => 'E-mail',
        'email_address' => 'Endereço de e-mail',
        'email_placeholder' => 'email@exemplo.com',
        'password' => 'Senha',
        'password_placeholder' => 'Senha',
        'password_confirmation' => 'Confirmar senha',
        'password_confirmation_placeholder' => 'Confirmar senha',
    ],

    'login' => [
        'title' => 'Entre na sua conta',
        'description' => 'Informe seu e-mail e senha abaixo para entrar',
        'head_title' => 'Entrar',
        'invitation_action' => 'Entrar',
        'forgot_password' => 'Esqueceu a senha?',
        'remember_me' => 'Lembrar de mim',
        'submit' => 'Entrar',
        'no_account' => 'Não tem uma conta?',
        'sign_up' => 'Cadastre-se',
    ],

    'register' => [
        'title' => 'Crie uma conta',
        'description' => 'Informe seus dados abaixo para criar sua conta',
        'head_title' => 'Cadastro',
        'invitation_action' => 'Cadastrar',
        'submit' => 'Criar conta',
        'already_registered' => 'Já tem uma conta?',
        'log_in' => 'Entrar',
    ],

    'forgot_password' => [
        'title' => 'Esqueceu a senha',
        'description' => 'Informe seu e-mail para receber um link de redefinição de senha',
        'head_title' => 'Esqueceu a senha',
        'submit' => 'Enviar link de redefinição de senha',
        'or_return_to' => 'Ou volte para',
        'log_in' => 'entrar',
    ],

    'reset_password' => [
        'title' => 'Redefinir senha',
        'description' => 'Informe sua nova senha abaixo',
        'head_title' => 'Redefinir senha',
        'submit' => 'Redefinir senha',
    ],

    'confirm_password' => [
        'title' => 'Confirme sua senha',
        'description' => 'Esta é uma área segura da aplicação. Confirme sua senha antes de continuar.',
        'head_title' => 'Confirmar senha',
        'passkey_label' => 'Confirmar com passkey',
        'passkey_loading' => 'Confirmando…',
        'passkey_separator' => 'Ou confirme com a senha',
        'submit' => 'Confirmar senha',
    ],

    'verify_email' => [
        'title' => 'Verificação de e-mail',
        'description' => 'Verifique seu endereço de e-mail clicando no link que acabamos de enviar para você.',
        'head_title' => 'Verificação de e-mail',
        'link_sent' => 'Um novo link de verificação foi enviado para o endereço de e-mail informado durante o cadastro.',
        'resend' => 'Reenviar e-mail de verificação',
        'log_out' => 'Sair',
    ],

    'two_factor' => [
        'head_title' => 'Autenticação de dois fatores',
        'continue' => 'Continuar',
        'or_you_can' => 'ou você pode',
        'code' => [
            'title' => 'Código de autenticação',
            'description' => 'Informe o código de autenticação gerado pelo seu aplicativo autenticador.',
            'toggle' => 'entrar usando um código de recuperação',
        ],
        'recovery' => [
            'title' => 'Código de recuperação',
            'description' => 'Confirme o acesso à sua conta informando um dos seus códigos de recuperação de emergência.',
            'toggle' => 'entrar usando um código de autenticação',
            'placeholder' => 'Digite o código de recuperação',
        ],
    ],

];
