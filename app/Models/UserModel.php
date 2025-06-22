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
            // Hier könnten Sie EIGENE, ZUSÄTZLICHE Spalten eintragen,
            // falls Sie die `users`-Tabelle später einmal erweitern.
            // z.B. 'first_name', 'last_name'
        ];
    }
}
