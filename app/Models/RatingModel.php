<?php

namespace App\Models;

use CodeIgniter\Model;

class RatingModel extends Model
{
    /**
     * Die Tabelle, die von diesem Model verwendet wird.
     *
     * @var string
     */
    protected $table            = 'ratings';

    /**
     * Der Primärschlüssel der Tabelle.
     *
     * @var string
     */
    protected $primaryKey       = 'id';

    /**
     * Ob der Primärschlüssel automatisch inkrementiert wird.
     *
     * @var bool
     */
    protected $useAutoIncrement = true;

    /**
     * Der Datentyp, in dem Ergebnisse zurückgegeben werden sollen.
     * 'object' oder 'array'.
     *
     * @var string
     */
    protected $returnType       = 'array';

    /**
     * Die Felder, die über das Model per insert(), save() oder update()
     * gesetzt werden dürfen. Dies ist eine wichtige Sicherheitsfunktion.
     *
     * @var array
     */
    protected $allowedFields    = [
        'user_id',
        'vendor_name',
        'rating_appearance',
        'rating_taste',
        'rating_presentation',
        'rating_price',
        'rating_service',
        'comment',
        'latitude',
        'longitude',
        'address_manual'
    ];

    // Datums-Felder
    /**
     * Aktiviert die automatische Verwaltung der Zeitstempel.
     *
     * @var bool
     */
    protected $useTimestamps = true;

    /**
     * Der Name der Spalte für das Erstellungsdatum.
     *
     * @var string
     */
    protected $createdField  = 'created_at';

    /**
     * Der Name der Spalte für das Aktualisierungsdatum.
     *
     * @var string
     */
    protected $updatedField  = 'updated_at';

    // Validierung
    /**
     * Validierungsregeln, die auf die Daten angewendet werden,
     * bevor sie in die Datenbank geschrieben werden.
     *
     * @var array
     */
    protected $validationRules = [
        'user_id'           => 'required',
        'vendor_name'       => 'required|string|max_length[255]',
        'rating_appearance' => 'required|in_list[1,2,3,4,5]',
        'rating_taste'      => 'required|in_list[1,2,3,4,5]',
        'rating_presentation' => 'required|in_list[1,2,3,4,5]',
        'rating_price'      => 'required|in_list[1,2,3,4,5]',
        'rating_service'    => 'required|in_list[1,2,3,4,5]',
        'latitude'          => 'permit_empty|decimal',
        'longitude'         => 'permit_empty|decimal',
    ];

    /**
     * Benutzerdefinierte Fehlermeldungen für die Validierung.
     *
     * @var array
     */
    protected $validationMessages = [
        'vendor_name' => [
            'required' => 'Bitte geben Sie einen Namen für den Anbieter ein.',
        ],
        'rating_taste' => [
            'required' => 'Eine Bewertung für den Geschmack ist erforderlich.',
            'in_list'  => 'Bitte wählen Sie eine gültige Sterne-Bewertung.'
        ],
        // Hier können weitere benutzerdefinierte Meldungen hinzugefügt werden
    ];
}
