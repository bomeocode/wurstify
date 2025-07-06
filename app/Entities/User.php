<?php

namespace App\Entities;

use CodeIgniter\Shield\Entities\User as ShieldUser;

class User extends ShieldUser
{
    private ?object $userLevel = null;

    /**
     * Berechnet und speichert den Level des Nutzers.
     */
    public function getLevel(): ?object
    {
        // Wenn wir den Level schon berechnet haben, geben wir ihn aus dem Cache zurück.
        if ($this->userLevel !== null) {
            return $this->userLevel;
        }

        $db = db_connect();

        // 1. Zähle die Bewertungen des Nutzers
        $ratingCount = $db->table('ratings')->where('user_id', $this->id)->countAllResults();

        // 2. Finde den höchsten Level, den der Nutzer mit dieser Anzahl erreicht hat
        $this->userLevel = $db->table('user_levels')
            ->where('min_ratings <=', $ratingCount)
            ->orderBy('level_number', 'DESC')
            ->get()
            ->getFirstRow();

        return $this->userLevel;
    }
}
