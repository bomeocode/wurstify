<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    protected function initialize(): void
    {
        parent::initialize();

        $this->allowedFields = [
            ...$this->allowedFields,
            'avatar', // Stellt sicher, dass das Avatar-Feld gespeichert werden darf
        ];
    }

    /**
     * Holt alle Benutzer und ihre zugewiesenen Gruppen.
     * Fasst mehrere Gruppen pro Benutzer in einem String zusammen.
     * @param string|null $searchTerm
     * @return array
     */
    public function getUsersWithGroups(?string $searchTerm = null)
    {
        $builder = $this->select('
                users.id, 
                users.username, 
                auth_identities.secret as email, 
                users.created_at,
                GROUP_CONCAT(auth_groups_users.group SEPARATOR ", ") as groups 
            ')
            ->join('auth_identities', 'auth_identities.user_id = users.id AND auth_identities.type = "email_password"', 'left')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id', 'left')
            ->groupBy('users.id');

        if ($searchTerm) {
            $builder->groupStart()
                ->like('users.username', $searchTerm)
                ->orLike('auth_identities.secret', $searchTerm)
                ->groupEnd();
        }

        return $builder->orderBy('users.id', 'ASC')->paginate(15);
    }
}
