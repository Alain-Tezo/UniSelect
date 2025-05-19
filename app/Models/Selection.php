<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Selection extends Model
{
    use HasFactory;

    protected $table = 'selections';

    protected $fillable = [
        'niveau_id',
        'filiere_id',
        'critere_selection_id',
        'created_by',
        'nombre_etudiants_selectionnes',
        'date_selection',
    ];

    // Relations
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }

    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }

    public function critereSelection()
    {
        return $this->belongsTo(CritereSelection::class);
    }

    public function createur()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
