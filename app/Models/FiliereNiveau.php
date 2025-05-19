<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Filiere;
use App\Models\Niveau;

class FiliereNiveau extends Model
{
    use HasFactory;
    
    protected $table = 'filiere_niveau';
    
    protected $fillable = [
        'filiere_id',
        'niveau_id',
        'places_disponibles'
    ];
    
    // Relations
    public function filiere()
    {
        return $this->belongsTo(Filiere::class);
    }
    
    public function niveau()
    {
        return $this->belongsTo(Niveau::class);
    }
}
