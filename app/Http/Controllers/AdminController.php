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
use PhpOffice\PhpSpreadsheet\IOFactory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotificationSelection;

class AdminController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function dashboard()
    {
        // Récupération des statistiques pour le tableau de bord
        $totalEtudiants = Etudiant::count();
        $totalSelectionnes = Etudiant::where('est_selectionne', true)->count();
        $totalFilieres = Filiere::count();
        $etudiants_par_niveau = Etudiant::select('niveaux.nom', DB::raw('count(*) as total'))
            ->join('niveaux', 'etudiants.niveau_id', '=', 'niveaux.id')
            ->groupBy('niveaux.id', 'niveaux.nom')
            ->get();

        $etudiants_par_filiere = Etudiant::where('est_selectionne', true)
            ->select('filieres.nom', DB::raw('count(*) as total'))
            ->join('filieres', 'etudiants.filiere_selectionnee_id', '=', 'filieres.id')
            ->groupBy('filieres.id', 'filieres.nom')
            ->get();

        return view('admin.dashboard', compact(
            'totalEtudiants',
            'totalSelectionnes',
            'totalFilieres',
            'etudiants_par_niveau',
            'etudiants_par_filiere'
        ));
    }

    //Affiche la liste des étudiants avec filtrage
    public function listeEtudiants(Request $request)
    {
        // Récupérer les options de filtre
        $niveaux = Niveau::all();
        $filieres = Filiere::all();

        // Construire la requête de base
        $query = Etudiant::query();

        // Appliquer les filtres si présents
        if ($request->filled('niveau_id')) {
            $query->where('niveau_id', $request->niveau_id);
        }

        if ($request->filled('filiere_id')) {
            // Filtre sur les filières souhaitées (premier, deuxième ou troisième choix)
            $query->where(function($q) use ($request) {
                $q->where('premier_choix_id', $request->filiere_id)
                ->orWhere('deuxieme_choix_id', $request->filiere_id)
                ->orWhere('troisieme_choix_id', $request->filiere_id);
            });
        }

        if ($request->filled('est_selectionne')) {
            $query->where('est_selectionne', $request->est_selectionne == '1');
        }

        // Charger les relations
        $query->with(['niveau', 'premierChoix', 'deuxiemeChoix', 'troisiemeChoix', 'filiereSelectionnee']);

        // Trier par ordre alphabétique des noms
        $query->orderBy('nom', 'asc')->orderBy('prenom', 'asc');

        // Paginer les résultats
        $etudiants = $query->paginate(15)->withQueryString();

        // Afficher la vue avec les données
        return view('admin.etudiants.liste', compact('etudiants', 'niveaux', 'filieres'));
    }

    //Affiche le formulaire d'importation des étudiants
    public function importerEtudiants()
    {
        // Récupérer tous les niveaux d'étude
        $niveaux = Niveau::all();

        // Récupérer toutes les filières pour référence
        $filieres = Filiere::all();

        // Afficher la vue du formulaire d'importation
        return view('admin.etudiants.importer', compact('niveaux', 'filieres'));
    }

    //Traite l'importation des étudiants à partir d'un fichier Excel/CSV
    public function traiterImportation(Request $request)
    {
        $request->validate([
            'fichier_excel' => 'required|file|mimes:xlsx,xls,csv',
            'niveau_id' => 'required|exists:niveaux,id',
        ]);

        try {
            // Importer les données
            $import = new \App\Imports\EtudiantsImport($request->niveau_id);
            \Maatwebsite\Excel\Facades\Excel::import($import, $request->file('fichier_excel'));

            // Vérifier les échecs
            $failures = $import->failures();

            if ($failures->isNotEmpty()) {
                $errors = [];

                foreach ($failures as $failure) {
                    $rowIndex = $failure->row();
                    $errors[] = "Ligne {$rowIndex}: " . implode(', ', $failure->errors());
                }

                return redirect()->route('admin.etudiants')
                    ->with('warning', 'Importation terminée avec des erreurs. Certaines lignes n\'ont pas été importées.')
                    ->with('import_errors', $errors);
            }

            return redirect()->route('admin.etudiants')
                ->with('success', 'Importation réussie ! Les étudiants ont été ajoutés avec succès.');

        } catch (\Exception $e) {
            \Log::error('Erreur d\'importation: ' . $e->getMessage());

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de l\'importation: ' . $e->getMessage());
        }
    }

    //Téléchargement d'un modèle Excel pour l'importation
    public function telechargerTemplate($niveau = 'l1')
    {
        // Créer un nouveau spreadsheet
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // En-têtes communs
        $headers = [
            'nom', 'prenom', 'email', 'date_naissance', 'sexe', 'region_origine',
            'premier_choix', 'deuxieme_choix', 'troisieme_choix'
        ];

        // En-têtes spécifiques au niveau
        if ($niveau == 'l1') {
            $headers = array_merge($headers, ['etablissement_precedent','serie_bac', 'moyenne_bac', 'note_math', 'note_physique', 'note_svteehb', 'note_informatique']);
        } else {
            $headers = array_merge($headers, ['universite_precedente','filiere_precedente', 'mgp']);
        }

        // Ajouter les en-têtes
        foreach ($headers as $index => $header) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($index);
            $sheet->setCellValue($col . '1', $header);
        }

        // Mise en forme
        $sheet->getStyle('1:1')->getFont()->setBold(true);

        // Créer le fichier Excel
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $filename = 'template-import-etudiants-' . $niveau . '.xlsx';
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer->save($tempFile);

        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    public function afficherSelection($selection_id)
    {
        $selection = Selection::with(['niveau', 'filiere', 'critereSelection'])->findOrFail($selection_id);
        $filiere = $selection->filiere;
        $niveau = $selection->niveau;

        // Récupérer les étudiants sélectionnés
        $etudiantsSelectionnes = Etudiant::where('niveau_id', $niveau->id)
            ->where('filiere_selectionnee_id', $filiere->id)
            ->where('est_selectionne', true)
            ->orderByDesc('points_selection')
            ->get();

        // Statistiques
        $statistiques = [
            'total_candidats' => Etudiant::where('niveau_id', $niveau->id)
                ->where(function($q) use ($filiere) {
                    $q->where('premier_choix_id', $filiere->id)
                        ->orWhere('deuxieme_choix_id', $filiere->id)
                        ->orWhere('troisieme_choix_id', $filiere->id);
                })
                ->count(),
            'total_selectionnes' => $etudiantsSelectionnes->count(),
            'score_minimum' => $etudiantsSelectionnes->min('points_selection') ?? 0,
            'score_maximum' => $etudiantsSelectionnes->max('points_selection') ?? 0,
            'score_moyen' => $etudiantsSelectionnes->avg('points_selection') ?? 0,
        ];

        // Calculer la distribution des scores pour le graphique
        $min = floor($statistiques['score_minimum']);
        $max = ceil($statistiques['score_maximum']);
        $step = max(1, ceil(($max - $min) / 10)); // Diviser en ~10 intervalles

        $intervalles = [];
        $distribution = [];

        for ($i = $min; $i < $max; $i += $step) {
            $intervalles[] = $i . '-' . ($i + $step);
            $distribution[] = $etudiantsSelectionnes->filter(function($e) use ($i, $step) {
                return $e->points_selection >= $i && $e->points_selection < ($i + $step);
            })->count();
        }

        $statistiques['intervalles'] = $intervalles;
        $statistiques['distribution'] = $distribution;

        return view('admin.selections.details', compact('selection', 'filiere', 'niveau', 'etudiantsSelectionnes', 'statistiques'));
    }

    public function criteresSelection()
    {
        $niveaux = Niveau::all();
        $filieres = Filiere::all();

        // Charger les critères avec leurs relations, y compris la relation filiere->niveaux
        $criteres = CritereSelection::with(['niveau', 'filiere.niveaux'])->get();

        // Débogage
        \Log::info('Critères récupérés:', $criteres->toArray());

        return view('admin.criteres.index', compact('niveaux', 'filieres', 'criteres'));
    }

    public function enregistrerCriteres(Request $request)
    {
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id',
            'filiere_id' => 'required|exists:filieres,id',
            'places_disponibles' => 'required|integer|min:1',
        ]);

        // Récupérer la filière
        $filiere = Filiere::findOrFail($request->filiere_id);

        // Vérifier les permissions de l'utilisateur pour cette filière
        $user = Auth::user();
        if (!$user->canManageFiliere($filiere->id)) {
            return redirect()->route('admin.criteres')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires pour gérer cette filière');
        }

        // Vérifier que la filière est sélective
        if (!$filiere->est_selective) {
            return redirect()->route('admin.criteres')
                ->with('error', 'Cette filière n\'est pas sélective');
        }

        // Débogage : vérifier les données reçues
        \Log::info('Données de critères reçues:', $request->all());

        // Traiter les critères
        $criteres = [];
        if ($request->has('criteres')) {
            foreach ($request->criteres as $critere) {
                $criteres[] = [
                    'type' => $critere['type'] ?? null,
                    'operateur' => $critere['operateur'] ?? null,
                    'valeur' => $critere['valeur'] ?? null,
                    'poids' => $critere['poids'] ?? 0
                ];
            }
        }

        // Traiter les bonus
        $bonus = [];
        if ($request->has('bonus')) {
            foreach ($request->bonus as $bonusItem) {
                $bonus[] = [
                    'categorie' => $bonusItem['categorie'] ?? null,
                    'valeur' => $bonusItem['valeur'] ?? null,
                    'type' => $bonusItem['type'] ?? null,
                    'points' => $bonusItem['points'] ?? 0
                ];
            }
        }

        // Débogage : vérifier les tableaux construits
        \Log::info('Critères traités:', $criteres);
        \Log::info('Bonus traités:', $bonus);

        // S'assurer que l'encodage JSON est correct
        $criteresJson = !empty($criteres) ? json_encode($criteres) : null;
        $bonusJson = !empty($bonus) ? json_encode($bonus) : null;

        \Log::info('JSON critères:', ['criteres_json' => $criteresJson]);
        \Log::info('JSON bonus:', ['bonus_json' => $bonusJson]);

        // Création ou mise à jour des critères de sélection
        $critereSelection = CritereSelection::updateOrCreate(
            [
                'niveau_id' => $request->niveau_id,
                'filiere_id' => $request->filiere_id,
            ],
            [
                'criteres_json' => $criteresJson,
                'bonus_json' => $bonusJson,
            ]
        );

        // Mise à jour du nombre de places disponibles pour cette combinaison filière-niveau
        $filiere = Filiere::findOrFail($request->filiere_id);
        $niveau = Niveau::findOrFail($request->niveau_id);

        // Mettre à jour la relation via la table pivot
        $filiere->niveaux()->syncWithoutDetaching([
            $niveau->id => ['places_disponibles' => $request->places_disponibles]
        ]);

        return redirect()->route('admin.criteres')
            ->with('success', 'Critères de sélection enregistrés avec succès');
    }

    // Obtenir les détails d'un critère pour l'affichage AJAX
    public function getCritereDetails($id)
    {
        $critere = CritereSelection::with(['niveau', 'filiere'])->findOrFail($id);

        // Vérifier les permissions de l'utilisateur pour cette filière
        $user = Auth::user();
        if (!$user->canManageFiliere($critere->filiere_id)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        return response()->json($critere);
    }

    // Obtenir un critère avec le nombre de places disponibles
    public function getCritere($id)
    {
        $critere = CritereSelection::with(['niveau', 'filiere'])->findOrFail($id);

        // Vérifier les permissions de l'utilisateur pour cette filière
        $user = Auth::user();
        if (!$user->canManageFiliere($critere->filiere_id)) {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        // Récupérer le nombre de places disponibles depuis la table pivot
        $filiereNiveauDetails = $critere->filiere->niveaux()
            ->where('niveau_id', $critere->niveau_id)
            ->first();

        // Créer un tableau pour la réponse
        $response = $critere->toArray();

        // Si on trouve une relation filière-niveau avec des places définies
        if ($filiereNiveauDetails) {
            // Ajouter les places disponibles directement au niveau de la filière
            // pour que le JavaScript existant continue de fonctionner
            $response['filiere']['places_disponibles'] = $filiereNiveauDetails->pivot->places_disponibles;
        } else {
            // Si aucune place n'est définie, mettre 0 par défaut
            $response['filiere']['places_disponibles'] = 0;
        }

        return response()->json($response);
    }

    /**
     * Supprimer un critère de sélection
     *
     * @param int $id ID du critère à supprimer
     * @return \Illuminate\Http\RedirectResponse
     */
    public function supprimerCritere($id)
    {
        // Récupérer le critère
        $critere = CritereSelection::findOrFail($id);

        // Vérifier les permissions de l'utilisateur pour cette filière
        $user = Auth::user();
        if (!$user->canManageFiliere($critere->filiere_id)) {
            return redirect()->route('admin.criteres')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires pour gérer cette filière');
        }

        // Supprimer le critère
        $critere->delete();

        return redirect()->route('admin.criteres')
            ->with('success', 'Critère de sélection supprimé avec succès');
    }

    public function afficherGenererSelection()
    {
        // Récupérer les niveaux et filières pour le formulaire
        $niveaux = Niveau::all();
        $filieres = Filiere::all();

        return view('admin.selections.generer', compact('niveaux', 'filieres'));
    }

    public function genererSelection(Request $request)
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
        $selectionService = new \App\Services\SelectionService();

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
            \Log::error('Erreur lors de la génération de sélection:', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return redirect()->back()
                ->with('error', 'Une erreur est survenue lors de la génération de la sélection: ' . $e->getMessage());
        }
    }

    //Affiche la liste des étudiants sélectionnés avec filtrage
    public function listeSelectionnes(Request $request)
    {
        // Récupérer les options de filtre
        $niveaux = Niveau::all();
        $filieres = Filiere::all();

        // Construire la requête de base (seulement les étudiants sélectionnés)
        $query = Etudiant::where('est_selectionne', true);

        // Appliquer les filtres si présents
        if ($request->filled('niveau_id')) {
            $query->where('niveau_id', $request->niveau_id);
        }

        if ($request->filled('filiere_id')) {
            // Pour les sélectionnés, on filtre sur la filière attribuée
            $query->where('filiere_selectionnee_id', $request->filiere_id);
        }

        // Charger les relations
        $query->with(['niveau', 'filiereSelectionnee']);

        // Trier par points décroissants
        $query->orderByDesc('points_selection');

        // Paginer les résultats
        $etudiants = $query->paginate(15)->withQueryString();

        // Récupérer les informations des administrateurs qui ont généré les sélections
        $niveau_id = $request->filled('niveau_id') ? $request->niveau_id : null;
        $filiere_id = $request->filled('filiere_id') ? $request->filiere_id : null;

        // Récupérer les sélections correspondantes avec l'information de l'administrateur
        $selectionsQuery = Selection::with('createur');

        if ($niveau_id) {
            $selectionsQuery->where('niveau_id', $niveau_id);
        }

        if ($filiere_id) {
            $selectionsQuery->where('filiere_id', $filiere_id);
        }

        $selections = $selectionsQuery->get();

        // Récupérer l'administrateur actuel pour le PDF
        $adminActuel = Auth::user();

        // Afficher la vue avec les données
        return view('admin.selections.liste', compact('etudiants', 'niveaux', 'filieres', 'selections', 'adminActuel'));
    }

    // Réinitialise la sélection pour une filière et un niveau spécifiques
    public function reinitialiserSelection(Request $request)
    {
        // Validation des entrées
        $request->validate([
            'niveau_id' => 'required|exists:niveaux,id',
            'filiere_id' => 'required|exists:filieres,id',
        ]);

        // Compter les étudiants concernés
        $count = Etudiant::where('niveau_id', $request->niveau_id)
            ->where('filiere_selectionnee_id', $request->filiere_id)
            ->where('est_selectionne', true)
            ->count();

        // Si aucun étudiant à réinitialiser
        if ($count === 0) {
            return redirect()->back()
                ->with('info', 'Aucun étudiant à réinitialiser pour cette filière et ce niveau.');
        }

        // Réinitialiser chaque étudiant
        $etudiants = Etudiant::where('niveau_id', $request->niveau_id)
            ->where('filiere_selectionnee_id', $request->filiere_id)
            ->where('est_selectionne', true)
            ->get();

        foreach ($etudiants as $etudiant) {
            $etudiant->est_selectionne = false;
            $etudiant->filiere_selectionnee_id = null;
            $etudiant->notification_envoyee = false;
            $etudiant->save();
        }

        // Récupérer les noms pour le message
        $niveau = Niveau::find($request->niveau_id);
        $filiere = Filiere::find($request->filiere_id);

        return redirect()->back()
            ->with('success', "Sélection réinitialisée avec succès. $count étudiants désélectionnés de la filière {$filiere->nom} au niveau {$niveau->nom}.");
    }

    /**
     * Envoie des notifications par email aux étudiants sélectionnés
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function notifierEtudiants(Request $request)
    {
        // Validation des entrées
        $request->validate([
            'niveau_id' => 'nullable|exists:niveaux,id',
            'filiere_id' => 'nullable|exists:filieres,id',
        ]);

        // Construire la requête pour récupérer les étudiants à notifier
        $query = Etudiant::where('est_selectionne', true)
                        ->where('notification_envoyee', false);

        // Filtres
        if ($request->filled('niveau_id')) {
            $query->where('niveau_id', $request->niveau_id);
        }

        if ($request->filled('filiere_id')) {
            $query->where('filiere_selectionnee_id', $request->filiere_id);
        }

        // Récupérer les étudiants à notifier
        $etudiants = $query->with(['niveau', 'filiereSelectionnee'])->get();
        $count = $etudiants->count();

        // Si aucun étudiant à notifier
        if ($count === 0) {
            return redirect()->back()
                ->with('warning', 'Aucun étudiant à notifier.');
        }

        try {
            // Compteur de réussite
            $compteurReussite = 0;

            // Envoi des notifications
            foreach ($etudiants as $etudiant) {
                try {
                    // Envoi du mail
                    Mail::to($etudiant->email)
                        ->send(new \App\Mail\NotificationSelection($etudiant));

                    // Marquer l'étudiant comme notifié
                    $etudiant->notification_envoyee = true;
                    $etudiant->date_notification = now();
                    $etudiant->save();

                    $compteurReussite++;
                } catch (\Exception $e) {
                    \Log::error("Erreur d'envoi de notification à {$etudiant->email}: " . $e->getMessage());
                }
            }

            // Message de succès
            return redirect()->back()
                ->with('success', "$compteurReussite notification(s) envoyée(s) avec succès.");

        } catch (\Exception $e) {
            \Log::error("Erreur générale d'envoi de notifications: " . $e->getMessage());
            return redirect()->back()
                ->with('error', "Une erreur est survenue lors de l'envoi des notifications: " . $e->getMessage());
        }
    }

    public function statistiques()
    {
        $niveaux = Niveau::all();
        $filieres = Filiere::all();

        // Statistiques globales
        $totalEtudiants = Etudiant::count();
        $totalSelectionnes = Etudiant::where('est_selectionne', true)->count();
        $totalNotifies = Etudiant::where('est_selectionne', true)
                    ->where('notification_envoyee', true)
                    ->count();

        return view('admin.statistiques', compact('niveaux', 'filieres', 'totalEtudiants', 'totalSelectionnes', 'totalNotifies'));
    }

    public function rechercherEtudiant(Request $request)
    {
        $query = $request->input('query');

        $etudiants = Etudiant::where('nom', 'LIKE', "%{$query}%")
            ->orWhere('prenom', 'LIKE', "%{$query}%")
            ->orWhere('email', 'LIKE', "%{$query}%")
            ->with(['niveau', 'filiereSelectionnee'])
            ->get();

        return response()->json($etudiants);
    }

    // Méthodes supplémentaires pour les super_admin
    public function gestionAdmins()
    {
        // Vérification du rôle
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires');
        }

        // Récupérer tous les utilisateurs (admins et super_admin)
        $admins = User::whereIn('role', ['admin', 'super_admin'])->get();

        return view('admin.admins.index', compact('admins'));
    }

    public function creerAdmin(Request $request)
    {
        // Vérification du rôle
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
        ]);

        return redirect()->route('admin.gestion-admins')
            ->with('success', 'Administrateur créé avec succès');
    }

    public function storeAdmin(Request $request)
    {
        // Vérification du rôle
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'role' => 'admin',
        ]);

        return redirect()->route('admin.gestion-admins')
            ->with('success', 'Administrateur créé avec succès');
    }

    public function updateAdmin(Request $request, $id)
    {
        // Vérification du rôle
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires');
        }

        $admin = User::findOrFail($id);

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
        ];

        // Validation du mot de passe uniquement s'il est fourni
        if ($request->filled('password')) {
            $rules['password'] = 'required|string|min:8|confirmed';
        }

        $request->validate($rules);

        // Mise à jour des informations de base
        $admin->name = $request->name;
        $admin->email = $request->email;

        // Mise à jour du mot de passe si fourni
        if ($request->filled('password')) {
            $admin->password = bcrypt($request->password);
        }

        $admin->save();

        return redirect()->route('admin.gestion-admins')
            ->with('success', 'Administrateur mis à jour avec succès');
    }

    public function destroyAdmin($id)
    {
        // Vérification du rôle
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires');
        }

        // Récupérer l'utilisateur à supprimer
        $admin = User::findOrFail($id);

        // Vérifier qu'on ne supprime pas notre propre compte
        if (Auth::id() == $id) {
            return redirect()->route('admin.gestion-admins')
                ->with('error', 'Vous ne pouvez pas supprimer votre propre compte');
        }

        // Vérifier que l'utilisateur à supprimer n'est pas un super_admin
        if ($admin->role === 'super_admin') {
            return redirect()->route('admin.gestion-admins')
                ->with('error', 'Impossible de supprimer un compte super administrateur');
        }

        $admin->delete();

        return redirect()->route('admin.gestion-admins')
            ->with('success', 'Administrateur supprimé avec succès');
    }

    /**
     * Récupère les permissions d'un administrateur
     *
     * @param int $id ID de l'administrateur
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAdminPermissions($id)
    {
        // Vérification du rôle
        if (Auth::user()->role !== 'super_admin') {
            return response()->json(['error' => 'Accès non autorisé'], 403);
        }

        // Récupérer l'administrateur
        $admin = User::findOrFail($id);

        // Vérifier que c'est bien un administrateur simple
        if ($admin->role !== 'admin') {
            return response()->json(['error' => 'Cet utilisateur n\'est pas un administrateur simple'], 400);
        }

        // Récupérer toutes les filières
        $filieres = Filiere::all(['id', 'nom']);

        // Récupérer les filières assignées à l'administrateur
        $permissions = $admin->filieres()->pluck('filieres.id')->toArray();

        return response()->json([
            'filieres' => $filieres,
            'permissions' => $permissions
        ]);
    }

    /**
     * Met à jour les permissions d'un administrateur
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function updateAdminPermissions(Request $request)
    {
        // Vérification du rôle
        if (Auth::user()->role !== 'super_admin') {
            return redirect()->route('admin.dashboard')
                ->with('error', 'Vous n\'avez pas les permissions nécessaires');
        }

        $request->validate([
            'admin_id' => 'required|exists:users,id',
            'filieres' => 'nullable|array',
            'filieres.*' => 'exists:filieres,id',
        ]);

        // Récupérer l'administrateur
        $admin = User::findOrFail($request->admin_id);

        // Vérifier que c'est bien un administrateur simple
        if ($admin->role !== 'admin') {
            return redirect()->route('admin.gestion-admins')
                ->with('error', 'Cet utilisateur n\'est pas un administrateur simple');
        }

        // Synchroniser les filières assignées
        $admin->filieres()->sync($request->filieres ?? []);

        return redirect()->route('admin.gestion-admins')
            ->with('success', 'Permissions mises à jour avec succès');
    }
}
?>
