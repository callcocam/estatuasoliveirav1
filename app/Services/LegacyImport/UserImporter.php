<?php

namespace App\Services\LegacyImport;

use App\Enums\UserRole;
use App\Models\User;

class UserImporter extends LegacyImporter
{
    public function handle(): void
    {
        $rows = $this->reader->rows('users');
        $this->report->source('users', count($rows));

        $roles = $this->rolesByUser();

        foreach ($rows as $row) {
            if (! $this->isRealCompany($row)) {
                $this->report->skip('users', $row['id'], 'outra empresa');

                continue;
            }

            if (($row['deleted_at'] ?? null) !== null || ($row['status'] ?? null) === 'deleted') {
                $this->report->skip('users', $row['id'], 'usuário deletado no legado');

                continue;
            }

            $email = strtolower(trim((string) $row['email']));

            if ($email === '') {
                $this->report->skip('users', $row['id'], 'e-mail vazio');

                continue;
            }

            $ulid = $this->ids->ulid('users', (string) $row['id']);

            if (User::withTrashed()->whereKey($ulid)->exists()) {
                $this->report->skip('users', $row['id'], 'já importado');

                continue;
            }

            if (User::withTrashed()->where('email', $email)->exists()) {
                $this->report->skip('users', $row['id'], "e-mail já em uso: {$email}");

                continue;
            }

            $user = new User([
                'name' => trim((string) $row['name']),
                'email' => $email,
                'phone' => $row['phone'],
                'role' => $roles[$row['id']] ?? UserRole::Customer,
            ]);

            $user->id = $ulid;

            // Preserva o hash bcrypt legado sem passar pelo cast `hashed`
            // (que rejeitaria hashes com custo diferente do configurado).
            $user->setRawAttributes(['password' => (string) $row['password']] + $user->getAttributes());
            $user->email_verified_at = self::timestamp($row['created_at'] ?? null);

            $this->persistWithTimestamps($user, $row);
            $this->report->imported('users');
        }
    }

    /**
     * Resolve the new role for each legacy user id from roles/role_user.
     *
     * @return array<string, UserRole>
     */
    private function rolesByUser(): array
    {
        $adminRoleIds = [];
        $staffRoleIds = [];

        foreach ($this->reader->rows('roles') as $role) {
            if (($role['special'] ?? null) === 'all-access') {
                $adminRoleIds[] = $role['id'];
            } else {
                $staffRoleIds[] = $role['id'];
            }
        }

        $map = [];

        foreach ($this->reader->rows('role_user') as $pivot) {
            $current = $map[$pivot['user_id']] ?? null;

            if (in_array($pivot['role_id'], $adminRoleIds, true)) {
                $map[$pivot['user_id']] = UserRole::Admin;
            } elseif ($current !== UserRole::Admin && in_array($pivot['role_id'], $staffRoleIds, true)) {
                $map[$pivot['user_id']] = UserRole::Manager;
            }
        }

        return $map;
    }
}
