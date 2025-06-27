<?php

namespace App\Models;

use CodeIgniter\Shield\Models\UserModel as ShieldUserModel;

class UserModel extends ShieldUserModel
{
    /**
     * Die initialize-Methode ist der perfekte Ort, um die Konfiguration
     * des Eltern-Models (ShieldUserModel) zu erweitern.
     */
    protected function initialize(): void
    {
        parent::initialize();

        // Hier stellen wir sicher, dass wir die erlaubten Felder vom originalen
        // Shield-Model übernehmen.
        // In diesem Array darf auf keinen Fall 'group' oder 'role' stehen,
        // da diese Spalten in der `users`-Tabelle nicht existieren.
        $this->allowedFields = [
            ...$this->allowedFields,
            'avatar',
            // Hier könnten Sie EIGENE, ZUSÄTZLICHE Spalten eintragen,
            // falls Sie die `users`-Tabelle später einmal erweitern.
            // z.B. 'first_name', 'last_name'
        ];
    }

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

        // NEU: Wenn ein Suchbegriff übergeben wird, fügen wir eine WHERE-Klausel hinzu
        if ($searchTerm) {
            $builder->groupStart() // Klammer auf: (
                ->like('users.username', $searchTerm)
                ->orLike('auth_identities.secret', $searchTerm) // Suche nach Username ODER E-Mail
                ->groupEnd(); // Klammer zu: )
        }

        // Wir ersetzen findAll() durch paginate(). Der Pager kümmert sich um den Rest.
        // Der erste Parameter ist die Anzahl der Einträge pro Seite.
        return $builder->orderBy('users.id', 'ASC')->paginate(15);
    }
}
