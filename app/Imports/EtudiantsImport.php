<?php

namespace App\Imports;

use App\Models\Etudiant;
use App\Models\Filiere;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class EtudiantsImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnFailure
{
    use SkipsFailures;

    private $niveau_id;
    private $filieres;

    public function __construct($niveau_id)
    {
        $this->niveau_id = $niveau_id;
        $this->filieres = Filiere::pluck('id', 'nom')->toArray();
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Récupérer la première filière comme valeur par défaut
        $default_filiere_id = Filiere::first()->id ?? 1;
        
        // Gérer les choix de filières
        $premier_choix_id = $default_filiere_id; // Valeur par défaut
        $deuxieme_choix_id = $default_filiere_id; // Valeur par défaut
        $troisieme_choix_id = $default_filiere_id; // Valeur par défaut
        $filiere_precedente_id = null; // Peut être null

        // Essayer de trouver les filières par leur nom
        if (isset($row['premier_choix']) && !empty($row['premier_choix'])) {
            if (array_key_exists($row['premier_choix'], $this->filieres)) {
                $premier_choix_id = $this->filieres[$row['premier_choix']];
            } else {
                Log::warning("Filière premier choix non trouvée: {$row['premier_choix']} pour l'étudiant {$row['nom']} {$row['prenom']}");
            }
        }

        if (isset($row['deuxieme_choix']) && !empty($row['deuxieme_choix'])) {
            if (array_key_exists($row['deuxieme_choix'], $this->filieres)) {
                $deuxieme_choix_id = $this->filieres[$row['deuxieme_choix']];
            } else {
                Log::warning("Filière deuxième choix non trouvée: {$row['deuxieme_choix']} pour l'étudiant {$row['nom']} {$row['prenom']}");
            }
        }

        if (isset($row['troisieme_choix']) && !empty($row['troisieme_choix'])) {
            if (array_key_exists($row['troisieme_choix'], $this->filieres)) {
                $troisieme_choix_id = $this->filieres[$row['troisieme_choix']];
            } else {
                Log::warning("Filière troisième choix non trouvée: {$row['troisieme_choix']} pour l'étudiant {$row['nom']} {$row['prenom']}");
            }
        }

        if (isset($row['filiere_precedente']) && !empty($row['filiere_precedente'])) {
            if (array_key_exists($row['filiere_precedente'], $this->filieres)) {
                $filiere_precedente_id = $this->filieres[$row['filiere_precedente']];
            } else {
                Log::warning("Filière précédente non trouvée: {$row['filiere_precedente']} pour l'étudiant {$row['nom']} {$row['prenom']}");
            }
        }
        
        // Traiter la date de naissance
        $date_naissance = null;
        if (isset($row['date_naissance'])) {
            try {
                // Essayer de convertir depuis le format Excel
                if (is_numeric($row['date_naissance'])) {
                    $date_naissance = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['date_naissance']);
                } else {
                    // Si ce n'est pas un nombre, essayer de parser comme une chaîne de date
                    $date_naissance = \Carbon\Carbon::parse($row['date_naissance'])->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // En cas d'erreur, utiliser la date actuelle et logger l'erreur
                \Log::warning("Erreur de conversion de date pour l'étudiant {$row['nom']} {$row['prenom']}: {$e->getMessage()}");
                $date_naissance = now()->format('Y-m-d');
            }
        }

        // Créer et retourner l'instance d'étudiant
        return new Etudiant([
            'nom' => $row['nom'],
            'prenom' => $row['prenom'],
            'email' => $row['email'],
            'date_naissance' => $date_naissance,
            'sexe' => $row['sexe'],
            'region_origine' => $row['region_origine'],
            'niveau_id' => $this->niveau_id,
            'etablissement_precedent' => $row['etablissement_precedent'] ?? null,
            'serie_bac' => $row['serie_bac'] ?? null,
            'moyenne_bac' => $row['moyenne_bac'] ?? null,
            'note_math' => $row['note_math'] ?? null,
            'note_physique' => $row['note_physique'] ?? null,
            'note_svteehb' => $row['note_svteehb'] ?? null,
            'note_informatique' => $row['note_informatique'] ?? null,
            'universite_precedente' => $row['universite_precedente'] ?? null,
            'mgp' => $row['mgp'] ?? null,
            'filiere_precedente_id' => $filiere_precedente_id,
            'premier_choix_id' => $premier_choix_id,
            'deuxieme_choix_id' => $deuxieme_choix_id,
            'troisieme_choix_id' => $troisieme_choix_id,
        ]);
    }

    public function rules(): array
    {
        return [
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email|unique:etudiants,email',
            'date_naissance' => 'required',
            'sexe' => 'required|in:M,F',
            'region_origine' => 'required',
            // Les choix de filière sont maintenant optionnels dans la validation
            // car nous utilisons des valeurs par défaut dans la méthode model
            'premier_choix' => 'nullable|string',
            'deuxieme_choix' => 'nullable|string',
            'troisieme_choix' => 'nullable|string',
        ];
    }
}
