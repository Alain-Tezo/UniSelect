<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Niveau extends Model
{
    use HasFactory;

    protected $table = 'niveaux';

    protected $fillable = [
        'nom',
        'description',
    ];

    // Relations
    public function etudiants()
    {
        return $this->hasMany(Etudiant::class);
    }

    public function criteresSelection()
    {
        return $this->hasMany(CritereSelection::class);
    }
    
    // Nouvelle relation many-to-many avec le modèle Filiere
    public function filieres()
    {
        return $this->belongsToMany(Filiere::class, 'filiere_niveau')
                    ->withPivot('places_disponibles')
                    ->withTimestamps();
    }
}
