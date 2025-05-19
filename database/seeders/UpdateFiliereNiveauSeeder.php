<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Filiere;
use App\Models\Niveau;
use Illuminate\Support\Facades\DB;

class UpdateFiliereNiveauSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Définir les valeurs de places disponibles pour chaque niveau
        $placesParNiveau = [
            'Licence 1' => 20,
            'Licence 2' => 15,
            'Licence 3' => 10,
            'Master 1' => 8,
            'Master 2' => 5,
        ];

        // Récupérer tous les niveaux
        $niveaux = Niveau::all();
        
        // Pour chaque niveau, mettre à jour les places disponibles
        foreach ($niveaux as $niveau) {
            // Trouver l'entrée correspondante dans notre tableau de places
            $places = $placesParNiveau[$niveau->nom] ?? 0;
            
            // Mettre à jour toutes les combinaisons avec ce niveau
            DB::table('filiere_niveau')
                ->where('niveau_id', $niveau->id)
                ->update(['places_disponibles' => $places]);
                
            echo "Mise à jour du niveau {$niveau->nom} avec {$places} places.\n";
        }
    }
}
