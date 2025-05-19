<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Filiere;
use App\Models\Niveau;
use Illuminate\Support\Facades\DB;

class FiliereNiveauSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Récupérer toutes les filières et niveaux
        $filieres = Filiere::all();
        $niveaux = Niveau::all();
        
        // Pour chaque filière, associer chaque niveau avec un nombre de places spécifique
        foreach ($filieres as $filiere) {
            foreach ($niveaux as $niveau) {
                // Définir un nombre de places différent selon le niveau
                $places = 0;
                
                // Exemple : L1: 20 places, L2: 15 places, L3: 10 places, M1: 8 places, M2: 5 places
                if ($niveau->nom == 'L1') {
                    $places = 20;
                } elseif ($niveau->nom == 'L2') {
                    $places = 15;
                } elseif ($niveau->nom == 'L3') {
                    $places = 10;
                } elseif ($niveau->nom == 'M1') {
                    $places = 8;
                } elseif ($niveau->nom == 'M2') {
                    $places = 5;
                }
                
                // Insérer dans la table pivot
                DB::table('filiere_niveau')->insert([
                    'filiere_id' => $filiere->id,
                    'niveau_id' => $niveau->id,
                    'places_disponibles' => $places,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
}
