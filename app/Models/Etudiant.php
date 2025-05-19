<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Etudiant extends Model
{
    use HasFactory;

    protected $table = 'etudiants';

    protected $fillable = [
        'nom',
        'prenom',
        'date_naissance',
        'email',
        'sexe',
        'niveau_id',
        'etablissement_precedent',
        'serie_bac',
        'moyenne_bac',
        'note_math',
        'note_physique',
        'note_svteehb',
        'note_informatique',
        'universite_precedente',
        'mgp',
        'filiere_precedente_id',
        'premier_choix_id',
        'deuxieme_choix_id',
        'troisieme_choix_id',
        'region_origine',
        'est_selectionne',
        'filiere_selectionnee_id',
        'points_selection',
        'notification_envoyee',
        'details_selection',
        'date_notification',
    ];

    // Relations
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function filierePrecedente()
    {
        return $this->belongsTo(Filiere::class, 'filiere_precedente_id');
    }

    public function premierChoix()
    {
        return $this->belongsTo(Filiere::class, 'premier_choix_id');
    }

    public function deuxiemeChoix()
    {
        return $this->belongsTo(Filiere::class, 'deuxieme_choix_id');
    }

    public function troisiemeChoix()
    {
        return $this->belongsTo(Filiere::class, 'troisieme_choix_id');
    }

    public function filiereSelectionnee()
    {
        return $this->belongsTo(Filiere::class, 'filiere_selectionnee_id');
    }
}
