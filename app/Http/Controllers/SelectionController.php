<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\User;
use App\Models\CritereSelection;
use App\Models\Selection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Services\SelectionService;

class SelectionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Récupérer les niveaux et filières pour le formulaire
        $niveaux = Niveau::all();
        $filieres = Filiere::all();

        return view('admin.selections.generer', compact('niveaux', 'filieres'));
    }

    /**
     * Affiche le formulaire pour la génération globale
     */
    public function indexGlobal()
    {
        return view('admin.selections.generer-global');
    }

    public function generer(Request $request)
    {
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id',
            'filiere_id' => 'required|exists:filieres,id',
        ]);

        $niveau = Niveau::findOrFail($request->niveau_id);
        $filiere = Filiere::findOrFail($request->filiere_id);

        // Vérifier que des critères existent pour cette combinaison
        $critere = CritereSelection::where('niveau_id', $request->niveau_id)
            ->where('filiere_id', $request->filiere_id)
            ->first();

        if (!$critere) {
            return redirect()->back()
                ->with('error', "Aucun critère de sélection n'a été défini pour la filière {$filiere->nom} au niveau {$niveau->nom}. Veuillez définir des critères avant de générer une sélection.");
        }

        // Instancier le service de sélection
        $selectionService = new SelectionService();

        try {
            // Exécuter l'algorithme de sélection
            $resultat = $selectionService->executerSelection($request->niveau_id, $request->filiere_id, Auth::id());

            if ($resultat['statut'] === 'success') {
                return redirect()->route('admin.selections')
                    ->with('success', $resultat['message']);
            } else {
                return redirect()->back()
                    ->with($resultat['statut'], $resultat['message']);
            }
        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            Log::error('Erreur lors de la génération de sélection:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la génération de la sélection: ' . $e->getMessage());
        }
    }

    /**
     * Génère la sélection globale pour toutes les filières et tous les niveaux
     */
    public function genererGlobal(Request $request)
    {
        // Instancier le service de sélection
        $selectionService = new SelectionService();

        try {
            // Exécuter l'algorithme de sélection globale
            $resultat = $selectionService->executerSelectionGlobale(Auth::id());

            if ($resultat['statut'] === 'success') {
                // Détails pour l'affichage
                $details = $resultat['details'];
                $erreurs = $resultat['erreurs'];

                return view('admin.selections.resultats-global', compact('resultat', 'details', 'erreurs'));
            } else {
                // Si des combinaisons manquantes sont présentes, les passer à la vue
                if (isset($resultat['combinaisons_manquantes'])) {
                    return view('admin.selections.generer-global')
                        ->with('error', $resultat['message'])
                        ->with('combinaisons_manquantes', $resultat['combinaisons_manquantes']);
                }

                return redirect()->back()
                    ->with($resultat['statut'], $resultat['message']);
            }
        } catch (\Exception $e) {
            // Log l'erreur pour le débogage
            Log::error('Erreur lors de la génération de sélection globale:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la génération de la sélection: ' . $e->getMessage());
        }
    }

    /**
     * Réinitialise toutes les sélections et le statut de tous les étudiants
     */
    public function reinitialiserGlobal()
    {
        try {
            // Vérifier si une transaction est déjà active
            if (!DB::transactionLevel()) {
                DB::beginTransaction();
            }

            // 1. Supprimer toutes les entrées de la table des sélections
            DB::table('selections')->delete();

            // 2. Réinitialiser tous les étudiants - utiliser une requête SQL directe
            // pour éviter les problèmes de contraintes
            DB::statement("
                UPDATE etudiants
                SET est_selectionne = 0,
                    filiere_selectionnee_id = NULL,
                    points_selection = 0,
                    details_selection = NULL,
                    notification_envoyee = 0
            ");

            // Confirmer que nous avons toujours une transaction active avant de commit
            if (DB::transactionLevel()) {
                DB::commit();
            }

            return redirect()->route('admin.selections.generer-global')
                ->with('success', 'Toutes les sélections ont été réinitialisées avec succès. Les étudiants sont maintenant disponibles pour une nouvelle génération.');
        }
        catch (\Exception $e) {
            // Ne faire un rollback que si nous avons une transaction active
            if (DB::transactionLevel()) {
                DB::rollBack();
            }

            // Log l'erreur pour le débogage
            Log::error('Erreur lors de la réinitialisation des sélections:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la réinitialisation des sélections: ' . $e->getMessage());
        }
    }
}
