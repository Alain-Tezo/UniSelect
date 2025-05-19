<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CritereSelection extends Model
{
    use HasFactory;

    protected $table = 'criteres_selection';

    protected $fillable = [
        'niveau_id',
        'filiere_id',
        'criteres_json',
        'bonus_json',
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

    public function selections()
    {
        return $this->hasMany(Selection::class);
    }
}
