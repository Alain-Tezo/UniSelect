<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Filiere extends Model
{
    use HasFactory;

    protected $table = 'filieres';

    protected $fillable = [
        'nom',
        'description',
        'est_selective',
    ];

    // Relations
    public function etudiants()
    {
        return $this->hasMany(Etudiant::class, 'filiere_selectionnee_id');
    }

    public function criteresSelection()
    {
        return $this->hasMany(CritereSelection::class);
    }
    
    // Nouvelle relation many-to-many avec le modèle Niveau
    public function niveaux()
    {
        return $this->belongsToMany(Niveau::class, 'filiere_niveau')
                    ->withPivot('places_disponibles')
                    ->withTimestamps();
    }
    
    /**
     * Récupère les administrateurs qui peuvent gérer cette filière
     * 
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function administrateurs()
    {
        return $this->belongsToMany(User::class, 'admin_filiere', 'filiere_id', 'user_id')
                    ->withTimestamps();
    }
}
