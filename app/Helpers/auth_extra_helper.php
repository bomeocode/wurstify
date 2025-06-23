<?php
// app/Helpers/auth_extra_helper.php

/**
 * Prüft, ob der aktuell eingeloggte Benutzer Mitglied
 * der angegebenen Gruppe ist.
 * Funktioniert mit der benutzerdefinierten Tabellenstruktur (user_id, group).
 *
 * @param string $groupName Der Name der Gruppe (z.B. 'admin')
 * @return bool
 */
function user_is_in_group(string $groupName): bool
{
  // Ist überhaupt jemand eingeloggt?
  if (!auth()->loggedIn()) {
    return false;
  }

  $userId = auth()->id();
  $db = \Config\Database::connect();

  // Wir zählen die Einträge in 'auth_groups_users', die für unsere
  // User-ID und den gesuchten Gruppennamen übereinstimmen.
  $result = $db->table('auth_groups_users')
    ->where('user_id', $userId)
    ->where('group', $groupName)
    ->countAllResults(); // Gibt 0 oder 1 (oder mehr) zurück

  return $result > 0;
}
