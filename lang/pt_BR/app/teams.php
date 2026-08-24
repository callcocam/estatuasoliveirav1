<?php

return [

    'roles' => [
        'owner' => 'Proprietário',
        'admin' => 'Administrador',
        'member' => 'Membro',
    ],

    'index' => [
        'title' => 'Equipes',
        'description' => 'Gerencie suas equipes e participações',
        'new_team' => 'Nova equipe',
        'personal_badge' => 'Pessoal',
        'empty' => 'Você ainda não faz parte de nenhuma equipe.',
        'actions' => [
            'leave' => 'Sair da equipe',
            'view' => 'Ver equipe',
            'edit' => 'Editar equipe',
        ],
    ],

    'edit' => [
        'title_edit' => 'Editar :name',
        'title_view' => 'Visualizar :name',
        'settings' => [
            'title' => 'Configurações da equipe',
            'description' => 'Atualize o nome e as configurações da sua equipe',
            'name_label' => 'Nome da equipe',
        ],
        'danger' => [
            'title' => 'Excluir equipe',
            'description' => 'Exclua permanentemente sua equipe',
            'warning' => 'Atenção',
            'caution' => 'Prossiga com cautela, esta ação não pode ser desfeita.',
            'delete_button' => 'Excluir equipe',
        ],
    ],

    'members' => [
        'title' => 'Membros da equipe',
        'description' => 'Gerencie quem faz parte desta equipe',
        'invite' => 'Convidar membro',
        'remove_tooltip' => 'Remover membro',
    ],

    'invitations' => [
        'title' => 'Convites pendentes',
        'description' => 'Convites que ainda não foram aceitos',
        'cancel_tooltip' => 'Cancelar convite',
    ],

    'invitation_alert' => [
        'message' => ':action para entrar na equipe ":team".',
        'actions' => [
            'log_in' => 'Faça login',
            'register' => 'Cadastre-se',
        ],
    ],

    'switcher' => [
        'select_team' => 'Selecionar equipe',
        'teams' => 'Equipes',
        'new_team' => 'Nova equipe',
    ],

    'modals' => [

        'create' => [
            'title' => 'Criar nova equipe',
            'description' => 'Crie uma nova equipe para colaborar com outras pessoas.',
            'name_label' => 'Nome da equipe',
            'name_placeholder' => 'Minha equipe',
            'submit' => 'Criar equipe',
        ],

        'delete' => [
            'title' => 'Tem certeza?',
            'description_prefix' => 'Esta ação não pode ser desfeita. Isso excluirá permanentemente a equipe',
            'description_suffix' => '.',
            'confirm_label_prefix' => 'Digite',
            'confirm_label_suffix' => 'para confirmar',
            'name_placeholder' => 'Digite o nome da equipe',
            'submit' => 'Excluir equipe',
        ],

        'leave' => [
            'title' => 'Sair da equipe',
            'description_prefix' => 'Tem certeza de que deseja sair da equipe',
            'description_suffix' => '?',
            'submit' => 'Sair da equipe',
        ],

        'invite' => [
            'title' => 'Convidar um membro',
            'description' => 'Envie um convite para participar desta equipe.',
            'email_label' => 'Endereço de e-mail',
            'email_placeholder' => 'colega@exemplo.com',
            'role_label' => 'Papel',
            'role_placeholder' => 'Selecione um papel',
            'submit' => 'Enviar convite',
        ],

        'remove' => [
            'title' => 'Remover membro da equipe',
            'description_prefix' => 'Tem certeza de que deseja remover',
            'description_suffix' => 'desta equipe?',
            'submit' => 'Remover membro',
        ],

        'cancel_invitation' => [
            'title' => 'Cancelar convite',
            'description_prefix' => 'Tem certeza de que deseja cancelar o convite para',
            'description_suffix' => '?',
            'keep' => 'Manter convite',
            'submit' => 'Cancelar convite',
        ],

        'pending_invitations' => [
            'title' => 'Convites de equipe pendentes',
            'description' => 'Aceite ou recuse os convites das equipes para as quais você foi convidado.',
            'invited_by' => ':name convidou você para participar desta equipe.',
            'accept' => 'Aceitar',
            'decline' => 'Recusar',
        ],

    ],

];
