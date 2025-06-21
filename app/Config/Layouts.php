<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Layouts extends BaseConfig
{
    /**
     * Definiert die verfügbaren Layout-Vorlagen.
     * Schlüssel: Dateiname der Template-View (ohne .php)
     * Wert: Lesbare Bezeichnung für den Benutzer
     *
     * @var array<string, string>
     */
    public array $templates = [
        '1-col'           => 'Eine Spalte',
        '2-col'           => 'Zwei Spalten (50% / 50%)',
        '3-col'           => 'Drei Spalten (33% / 33% / 33%)',
        '2-col-split-right' => 'Zwei Spalten, rechts geteilt (50% / 25% + 25%)',
        '2-col-split-left'  => 'Zwei Spalten, links geteilt (25% + 25% / 50%)',
        '3-col-split-right' => 'Drei Spalten, rechts geteilt', // für deine Vorlage 2
        '2-col-asym'        => 'Zwei Spalten, asymmetrisch (66% / 33%)', // für deine Vorlage 4
    ];
}
