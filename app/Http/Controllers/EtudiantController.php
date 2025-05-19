<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Selection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EtudiantController extends Controller
{
    public function showForm()
    {
        $niveaux = Niveau::all();
        $filieres = Filiere::all();

        return view('etudiant.formulaire', compact('niveaux', 'filieres'));
    }

    public function store(Request $request)
    {
        // Valider les champs communs
        $validator = Validator::make($request->all(), [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'date_naissance' => 'required|date',
            'email' => 'required|email|unique:etudiants,email',
            'sexe' => 'required|in:M,F',
            'niveau_id' => 'required|exists:niveaux,id',
            'premier_choix_id' => 'required|exists:filieres,id',
            'deuxieme_choix_id' => 'required|exists:filieres,id',
            'troisieme_choix_id' => 'required|exists:filieres,id',
            'region_origine' => 'required|in:nord,sud,est,ouest,centre,Adamaoua,littoral,extrême nord,nord-ouest,sud-ouest',
        ]);

        // Valider en fonction du niveau
        $niveau_id = $request->niveau_id;

        // Licence 1
        if ($niveau_id == 1) {
            $validator = Validator::make($request->all(), [
                'etablissement_precedent' => 'required|string|max:255',
                'serie_bac' => 'required|in:A,C,D,TI',
                'moyenne_bac' => 'required|numeric|min:0|max:20',
                'note_math' => 'required|numeric|min:0|max:20',
                'note_physique' => 'required|numeric|min:0|max:20',
                'note_svteehb' => 'required|numeric|min:0|max:20',
                'note_informatique' => 'required|numeric|min:0|max:20',
            ]);
        }
        // Licence 2 et supérieur
        else {
            $validator = Validator::make($request->all(), [
                'universite_precedente' => 'required|string|max:255',
                'filiere_precedente_id' => 'required|exists:filieres,id',
                'mgp' => 'required|numeric|min:0|max:4',
            ]);
        }

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        // Créer l'étudiant
        $etudiant = new Etudiant($request->all());
        $etudiant->save();

        return redirect()->route('home')
            ->with('success', 'Votre inscription a été enregistrée avec succès. Vous recevrez une notification par email concernant votre sélection.');
    }
    
    /**
     * Supprimer un étudiant inscrit spécifique
     * 
     * @param int $id ID de l'étudiant à supprimer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function supprimerEtudiant($id)
    {
        try {
            // Vérifier si une transaction est déjà active
            if (!DB::transactionLevel()) {
                DB::beginTransaction();
            }
            
            // Trouver l'étudiant
            $etudiant = Etudiant::findOrFail($id);
            
            // Si l'étudiant est déjà sélectionné, il faut mettre à jour le nombre de places disponibles
            if ($etudiant->est_selectionne && $etudiant->filiere_selectionnee_id && $etudiant->niveau_id) {
                // Récupérer les informations
                $filiereId = $etudiant->filiere_selectionnee_id;
                $niveauId = $etudiant->niveau_id;
                
                // Incrémenter le nombre de places disponibles dans la filière/niveau
                DB::table('filiere_niveau')
                    ->where('filiere_id', $filiereId)
                    ->where('niveau_id', $niveauId)
                    ->increment('places_disponibles');
            }
            
            // Note: Il n'y a pas de lien direct entre étudiants et sélections via etudiant_id
            // Les étudiants sont liés aux sélections via leur filiere_selectionnee_id et niveau_id
            
            // Supprimer l'étudiant
            $etudiant->delete();
            
            // Confirmer que nous avons toujours une transaction active avant de commit
            if (DB::transactionLevel()) {
                DB::commit();
            }
            
            return redirect()->back()
                ->with('success', 'L\'étudiant a été supprimé avec succès.');
        } 
        catch (\Exception $e) {
            // Ne faire un rollback que si nous avons une transaction active
            if (DB::transactionLevel()) {
                DB::rollBack();
            }
            
            // Log l'erreur pour le débogage
            Log::error('Erreur lors de la suppression d\'un étudiant:', [
                'etudiant_id' => $id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la suppression de l\'étudiant: ' . $e->getMessage());
        }
    }
    
    /**
     * Réinitialiser la liste complète des étudiants inscrits
     * Attention : cette action supprime TOUS les étudiants de la base de données
     * 
     * @return \Illuminate\Http\RedirectResponse
     */
    public function reinitialiserListeEtudiants()
    {
        try {
            // 1. Utiliser un mécanisme plus simple pour supprimer les étudiants
            // On utilise delete() au lieu de truncate() pour éviter les problèmes de contrainte
            $nbEtudiantsSupprimes = DB::table('etudiants')->delete();
            
            // Nous ne réinitialisons plus les places disponibles pour conserver les valeurs définies par l'administrateur
            // Les lignes suivantes sont commentées pour éviter la réinitialisation à 30 places
            /*
            $capaciteParDefaut = 30; // Valeur par défaut pour les places disponibles
            
            // Utiliser une requête simple pour éviter les erreurs de colonne inexistante
            $filieresNiveaux = DB::table('filiere_niveau')->get();
            
            // Réinitialiser les compteurs de places manuellement, évite les triggers et autres problèmes
            foreach ($filieresNiveaux as $fn) {
                DB::table('filiere_niveau')
                    ->where('id', $fn->id)
                    ->update(['places_disponibles' => $capaciteParDefaut]);
            }
            */
            
            return redirect()->back()
                ->with('success', "La liste des étudiants a été réinitialisée avec succès. {$nbEtudiantsSupprimes} étudiants ont été supprimés.");
        } 
        catch (\Exception $e) {
            // Log l'erreur pour le débogage
            Log::error('Erreur lors de la réinitialisation de la liste des étudiants:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la réinitialisation de la liste des étudiants: ' . $e->getMessage());
        }
    }
}
