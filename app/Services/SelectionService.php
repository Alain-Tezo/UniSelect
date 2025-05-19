<?php
// app/Services/SelectionService.php

namespace App\Services;

use App\Models\Etudiant;
use App\Models\CritereSelection;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Selection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Mail\NotificationSelection;

class SelectionService
{
    /**
     * Exécuter l'algorithme de sélection pour un niveau et une filière
     *
     * @param int $niveau_id
     * @param int $filiere_id
     * @param int $user_id ID de l'administrateur qui effectue la sélection
     * @return array Résultats de la sélection
     */
    public function executerSelection($niveau_id, $filiere_id, $user_id)
    {
        // Récupérer les critères de sélection
        $critere = CritereSelection::where('niveau_id', $niveau_id)
            ->where('filiere_id', $filiere_id)
            ->firstOrFail();

        // Vérifier que les critères sont bien définis
        if (empty($critere->criteres_json)) {
            return [
                'statut' => 'error',
                'message' => 'Aucun critère de sélection n\'a été défini pour cette filière et ce niveau.',
                'nombre_etudiants' => 0
            ];
        }

        // Récupérer la filière et le nombre de places disponibles
        $filiere = Filiere::findOrFail($filiere_id);
        $niveau = Niveau::findOrFail($niveau_id);

        // Récupérer le nombre de places disponibles pour cette filière et ce niveau spécifique
        $filiereNiveau = $filiere->niveaux()->where('niveau_id', $niveau_id)->first();

        if (!$filiereNiveau) {
            return [
                'statut' => 'error',
                'message' => 'Aucun nombre de places n\'a été défini pour la filière ' . $filiere->nom . ' au niveau ' . $niveau->nom . '.',
                'nombre_etudiants' => 0
            ];
        }

        $places_disponibles = $filiereNiveau->pivot->places_disponibles;

        // Si aucune place disponible
        if ($places_disponibles <= 0) {
            return [
                'statut' => 'error',
                'message' => 'Aucune place disponible pour la filière ' . $filiere->nom . ' au niveau ' . $niveau->nom . '.',
                'nombre_etudiants' => 0
            ];
        }

        // Récupérer tous les étudiants du niveau spécifié qui ont sélectionné cette filière en 1er, 2e ou 3e choix
        $etudiants = Etudiant::where('niveau_id', $niveau_id)
            ->where(function($query) use ($filiere_id) {
                $query->where('premier_choix_id', $filiere_id)
                    ->orWhere('deuxieme_choix_id', $filiere_id)
                    ->orWhere('troisieme_choix_id', $filiere_id);
            })
            ->where('est_selectionne', false)
            ->get();

        // Si aucun étudiant trouvé
        if ($etudiants->isEmpty()) {
            return [
                'statut' => 'info',
                'message' => 'Aucun étudiant à sélectionner pour cette filière et ce niveau.',
                'nombre_etudiants' => 0
            ];
        }

        // Calculer les points pour chaque étudiant et créer un tableau détaillé
        $resultats = [];
        foreach ($etudiants as $etudiant) {
            $detailPoints = $this->calculerPointsDetailles($etudiant, $critere);

            $resultats[] = [
                'etudiant' => $etudiant,
                'points_total' => $detailPoints['total'],
                'details' => $detailPoints['details']
            ];
        }

        // Trier par points décroissants
        usort($resultats, function($a, $b) {
            return $b['points_total'] <=> $a['points_total'];
        });

        // Limiter au nombre de places disponibles
        $selectionnes = array_slice($resultats, 0, $places_disponibles);

        // Créer l'entrée de sélection
        $selection = new Selection();
        $selection->niveau_id = $niveau_id;
        $selection->filiere_id = $filiere_id;
        $selection->critere_selection_id = $critere->id;
        $selection->created_by = $user_id;
        $selection->nombre_etudiants_selectionnes = count($selectionnes);
        $selection->date_selection = now();
        $selection->save();

        // Mettre à jour les étudiants sélectionnés
        foreach ($selectionnes as $resultat) {
            $etudiant = $resultat['etudiant'];
            $etudiant->est_selectionne = true;
            $etudiant->filiere_selectionnee_id = $filiere_id;
            $etudiant->points_selection = $resultat['points_total'];
            $etudiant->details_selection = json_encode($resultat['details']);
            $etudiant->save();
        }

        // Enregistrer les logs détaillés de la sélection
        $this->logSelectionDetails($selection->id, $resultats, $selectionnes);

        return [
            'statut' => 'success',
            'message' => sprintf('Sélection réussie. %d étudiants ont été sélectionnés pour la filière %s au niveau %s.',
                count($selectionnes),
                $filiere->nom,
                $niveau->nom
            ),
            'nombre_etudiants' => count($selectionnes),
            'etudiants' => collect($selectionnes)->pluck('etudiant'),
            'resultats_detailles' => $selectionnes
        ];
    }

    /**
     * Exécuter l'algorithme de sélection globale pour toutes les filières et tous les niveaux
     *
     * @param int $user_id ID de l'administrateur qui effectue la sélection
     * @return array Résultats de la sélection
     */
    public function executerSelectionGlobale($user_id)
    {
        // Résultats globaux
        $resultatsGlobaux = [
            'statut' => 'success',
            'message' => '',
            'details' => [],
            'erreurs' => [],
            'nombre_total_etudiants' => 0
        ];

        // Récupérer toutes les filières et tous les niveaux
        $filieres = Filiere::all();
        $niveaux = Niveau::all();

        // Vérifier que toutes les combinaisons filière-niveau ont des critères définis
        $combinaisonsManquantes = [];

        foreach ($niveaux as $niveau) {
            foreach ($filieres as $filiere) {
                // Ignorer les filières non-sélectives qui n'ont pas besoin de critères
                if (!$filiere->est_selective) {
                    continue;
                }

                // Vérifier si la filière est associée à ce niveau (via la table pivot)
                $filiereNiveau = $filiere->niveaux()
                    ->where('niveau_id', $niveau->id)
                    ->first();

                if (!$filiereNiveau) {
                    // Si cette combinaison n'est pas définie dans la table pivot, continuez
                    continue;
                }

                // Vérifier si les critères sont définis pour cette combinaison
                $critere = CritereSelection::where('niveau_id', $niveau->id)
                    ->where('filiere_id', $filiere->id)
                    ->first();

                // Si pas de critères ou critères vides
                if (!$critere || empty($critere->criteres_json)) {
                    $combinaisonsManquantes[] = [
                        'niveau' => $niveau->nom,
                        'filiere' => $filiere->nom
                    ];
                }
            }
        }

        // S'il manque des critères pour certaines combinaisons, renvoyer une erreur
        if (!empty($combinaisonsManquantes)) {
            // Préparer le message d'erreur
            $message = "Impossible de générer la sélection globale. Des critères n'ont pas été définis pour les combinaisons suivantes :<br>";

            foreach ($combinaisonsManquantes as $combinaison) {
                $message .= "- {$combinaison['niveau']} / {$combinaison['filiere']}<br>";
            }

            $message .= "<br>Veuillez définir des critères pour toutes les combinaisons avant de générer la sélection globale.";

            return [
                'statut' => 'error',
                'message' => $message,
                'nombre_total_etudiants' => 0,
                'combinaisons_manquantes' => $combinaisonsManquantes
            ];
        }

        // 1. Récupérer toutes les combinaisons filière-niveau avec des critères définis
        $criteresDefinis = CritereSelection::with(['filiere', 'niveau'])
            ->get()
            ->groupBy(function($critere) {
                return $critere->niveau_id;
            });

        if ($criteresDefinis->isEmpty()) {
            return [
                'statut' => 'error',
                'message' => 'Aucun critère de sélection n\'a été défini. Veuillez définir des critères avant de générer une sélection.',
                'nombre_total_etudiants' => 0
            ];
        }

        // ---- NOUVEL ALGORITHME POUR FILIÈRES SÉLECTIVES ET NON-SÉLECTIVES ----

        // 1. Récupérer tous les étudiants non encore sélectionnés
        $etudiants = Etudiant::where('est_selectionne', false)->get();

        // Statistiques pour le rapport
        $resultatsParFiliere = [];
        $etudiants_selectionnes_ids = [];

        // 2. Premier passage: traiter les premiers choix non-sélectifs
        foreach ($etudiants as $etudiant) {
            // Vérifier si l'étudiant a déjà été traité
            if (in_array($etudiant->id, $etudiants_selectionnes_ids)) {
                continue;
            }

            // Obtenir le premier choix
            if ($etudiant->premier_choix_id) {
                $filiere_premier_choix = Filiere::find($etudiant->premier_choix_id);

                // Si le premier choix est une filière non-sélective
                if ($filiere_premier_choix && !$filiere_premier_choix->est_selective) {
                    // Placer directement l'étudiant dans son premier choix
                    $this->placerEtudiantDansFiliere(
                        $etudiant,
                        $filiere_premier_choix->id,
                        0,  // Points (non pertinent pour filières non-sélectives)
                        [], // Détails (non pertinents pour filières non-sélectives)
                        $resultatsParFiliere,
                        $etudiants_selectionnes_ids
                    );
                }
            }
        }

        // 3. Deuxième passage: traiter les premiers choix sélectifs
        // Pour chaque filière sélective, calculer les points et classer les étudiants
        $filieresSelectives = Filiere::where('est_selective', true)->get();

        foreach ($filieresSelectives as $filiere) {
            foreach ($niveaux as $niveau) {
                // Vérifier si la filière est disponible pour ce niveau
                $filiereNiveau = $filiere->niveaux()->where('niveau_id', $niveau->id)->first();
                if (!$filiereNiveau) {
                    continue;
                }

                $places_disponibles = $filiereNiveau->pivot->places_disponibles;

                // Obtenir les critères pour cette combinaison
                $critere = CritereSelection::where('niveau_id', $niveau->id)
                    ->where('filiere_id', $filiere->id)
                    ->first();

                if (!$critere || empty($critere->criteres_json)) {
                    continue;
                }

                // Trouver tous les étudiants qui ont cette filière comme premier choix
                $candidats = Etudiant::where('niveau_id', $niveau->id)
                    ->where('premier_choix_id', $filiere->id)
                    ->whereNotIn('id', $etudiants_selectionnes_ids)
                    ->get();

                if ($candidats->isEmpty()) {
                    continue;
                }

                // Calculer les points pour chaque candidat
                $candidatsAvecPoints = [];
                foreach ($candidats as $candidat) {
                    $detailPoints = $this->calculerPointsDetailles($candidat, $critere);

                    $candidatsAvecPoints[] = [
                        'etudiant' => $candidat,
                        'points_total' => $detailPoints['total'] + 5, // +5 pour le 1er choix
                        'details' => $detailPoints['details']
                    ];
                }

                // Trier par points décroissants
                usort($candidatsAvecPoints, function($a, $b) {
                    return $b['points_total'] <=> $a['points_total'];
                });

                // Prendre les meilleurs candidats selon les places disponibles
                $candidatsRetenus = array_slice($candidatsAvecPoints, 0, $places_disponibles);

                // Enregistrer les candidats retenus
                foreach ($candidatsRetenus as $candidat) {
                    $this->placerEtudiantDansFiliere(
                        $candidat['etudiant'],
                        $filiere->id,
                        $candidat['points_total'],
                        $candidat['details'],
                        $resultatsParFiliere,
                        $etudiants_selectionnes_ids
                    );
                }
            }
        }

        // 4. Troisième passage: traiter les deuxièmes choix
        foreach ($etudiants as $etudiant) {
            // Ignorer les étudiants déjà traités
            if (in_array($etudiant->id, $etudiants_selectionnes_ids)) {
                continue;
            }

            // Obtenir le deuxième choix
            if ($etudiant->deuxieme_choix_id) {
                $filiere_deuxieme_choix = Filiere::find($etudiant->deuxieme_choix_id);

                // Si le deuxième choix est une filière non-sélective
                if ($filiere_deuxieme_choix && !$filiere_deuxieme_choix->est_selective) {
                    // Placer directement l'étudiant dans son deuxième choix
                    $this->placerEtudiantDansFiliere(
                        $etudiant,
                        $filiere_deuxieme_choix->id,
                        0,
                        [],
                        $resultatsParFiliere,
                        $etudiants_selectionnes_ids
                    );
                }
                // Si c'est une filière sélective
                else if ($filiere_deuxieme_choix && $filiere_deuxieme_choix->est_selective) {
                    // Vérifier s'il reste des places
                    $filiereNiveau = $filiere_deuxieme_choix->niveaux()
                        ->where('niveau_id', $etudiant->niveau_id)
                        ->first();

                    if (!$filiereNiveau) {
                        continue;
                    }

                    $places_disponibles = $filiereNiveau->pivot->places_disponibles;
                    $places_occupees = isset($resultatsParFiliere[$etudiant->niveau_id][$filiere_deuxieme_choix->id])
                        ? count($resultatsParFiliere[$etudiant->niveau_id][$filiere_deuxieme_choix->id])
                        : 0;

                    if ($places_occupees >= $places_disponibles) {
                        continue; // Filière complète
                    }

                    // Calculer les points
                    $critere = CritereSelection::where('niveau_id', $etudiant->niveau_id)
                        ->where('filiere_id', $filiere_deuxieme_choix->id)
                        ->first();

                    if (!$critere || empty($critere->criteres_json)) {
                        continue;
                    }

                    $detailPoints = $this->calculerPointsDetailles($etudiant, $critere);
                    $points = $detailPoints['total'] + 2; // +2 pour le 2ème choix

                    // Placer l'étudiant dans cette filière
                    $this->placerEtudiantDansFiliere(
                        $etudiant,
                        $filiere_deuxieme_choix->id,
                        $points,
                        $detailPoints['details'],
                        $resultatsParFiliere,
                        $etudiants_selectionnes_ids
                    );
                }
            }
        }

        // 5. Dernier passage: traiter les troisièmes choix
        foreach ($etudiants as $etudiant) {
            // Ignorer les étudiants déjà traités
            if (in_array($etudiant->id, $etudiants_selectionnes_ids)) {
                continue;
            }

            // Obtenir le troisième choix
            if ($etudiant->troisieme_choix_id) {
                $filiere_troisieme_choix = Filiere::find($etudiant->troisieme_choix_id);

                // Si le troisième choix est une filière non-sélective ou s'il reste des places dans une sélective
                if ($filiere_troisieme_choix) {
                    if (!$filiere_troisieme_choix->est_selective) {
                        // Placer directement l'étudiant dans son troisième choix non-sélectif
                        $this->placerEtudiantDansFiliere(
                            $etudiant,
                            $filiere_troisieme_choix->id,
                            0,
                            [],
                            $resultatsParFiliere,
                            $etudiants_selectionnes_ids
                        );
                    } else {
                        // C'est une filière sélective, vérifier s'il reste des places
                        $filiereNiveau = $filiere_troisieme_choix->niveaux()
                            ->where('niveau_id', $etudiant->niveau_id)
                            ->first();

                        if (!$filiereNiveau) {
                            continue;
                        }

                        $places_disponibles = $filiereNiveau->pivot->places_disponibles;
                        $places_occupees = isset($resultatsParFiliere[$etudiant->niveau_id][$filiere_troisieme_choix->id])
                            ? count($resultatsParFiliere[$etudiant->niveau_id][$filiere_troisieme_choix->id])
                            : 0;

                        if ($places_occupees >= $places_disponibles) {
                            continue; // Filière complète
                        }

                        // Calculer les points
                        $critere = CritereSelection::where('niveau_id', $etudiant->niveau_id)
                            ->where('filiere_id', $filiere_troisieme_choix->id)
                            ->first();

                        if (!$critere || empty($critere->criteres_json)) {
                            continue;
                        }

                        $detailPoints = $this->calculerPointsDetailles($etudiant, $critere);
                        $points = $detailPoints['total'] + 1; // +1 pour le 3ème choix

                        // Placer l'étudiant
                        $this->placerEtudiantDansFiliere(
                            $etudiant,
                            $filiere_troisieme_choix->id,
                            $points,
                            $detailPoints['details'],
                            $resultatsParFiliere,
                            $etudiants_selectionnes_ids
                        );
                    }
                }
            }
        }

        // Créer et enregistrer les sélections dans la base de données
        $this->enregistrerSelectionsEnBDD($resultatsParFiliere, $user_id, $resultatsGlobaux);

        // Message de réussite global
        if ($resultatsGlobaux['nombre_total_etudiants'] > 0) {
            $resultatsGlobaux['message'] = "Sélection globale réussie. {$resultatsGlobaux['nombre_total_etudiants']} étudiants ont été sélectionnés au total.";
        } else {
            $resultatsGlobaux['statut'] = 'info';
            $resultatsGlobaux['message'] = "Aucun étudiant n'a été sélectionné. Veuillez vérifier les critères et les places disponibles.";
        }

        return $resultatsGlobaux;
    }

    /**
     * Place un étudiant dans une filière et met à jour les statistiques
     */
    private function placerEtudiantDansFiliere($etudiant, $filiere_id, $points, $details, &$resultatsParFiliere, &$etudiants_selectionnes_ids)
    {
        $niveau_id = $etudiant->niveau_id;

        // Initialiser le tableau des résultats si nécessaire
        if (!isset($resultatsParFiliere[$niveau_id])) {
            $resultatsParFiliere[$niveau_id] = [];
        }

        if (!isset($resultatsParFiliere[$niveau_id][$filiere_id])) {
            $resultatsParFiliere[$niveau_id][$filiere_id] = [];
        }

        // Ajouter l'étudiant aux résultats
        $resultatsParFiliere[$niveau_id][$filiere_id][] = [
            'etudiant' => $etudiant,
            'points_total' => $points,
            'details' => $details
        ];

        // Marquer l'étudiant comme traité
        $etudiants_selectionnes_ids[] = $etudiant->id;
    }

    /**
     * Enregistre les sélections dans la base de données
     */
    private function enregistrerSelectionsEnBDD($resultatsParFiliere, $user_id, &$resultatsGlobaux)
    {
        foreach ($resultatsParFiliere as $niveau_id => $filieres) {
            $niveau = Niveau::findOrFail($niveau_id);

            foreach ($filieres as $filiere_id => $etudiants) {
                if (empty($etudiants)) {
                    continue;
                }

                $filiere = Filiere::findOrFail($filiere_id);

                // Créer l'entrée de sélection
                $selection = new Selection();
                $selection->niveau_id = $niveau_id;
                $selection->filiere_id = $filiere_id;

                // Récupérer un critère s'il existe pour cette combinaison
                $critere = CritereSelection::where('niveau_id', $niveau_id)
                    ->where('filiere_id', $filiere_id)
                    ->first();

                if ($critere) {
                    $selection->critere_selection_id = $critere->id;
                } else {
                    // S'assurer que le champ peut être NULL dans la base de données
                    // Si la filière n'est pas sélective, il n'y a pas de critère
                    $selection->critere_selection_id = null;
                }

                $selection->created_by = $user_id;
                $selection->nombre_etudiants_selectionnes = count($etudiants);
                $selection->date_selection = now();
                $selection->save();

                // Mettre à jour les étudiants sélectionnés
                foreach ($etudiants as $resultat) {
                    $etudiant = $resultat['etudiant'];
                    $etudiant->est_selectionne = true;
                    $etudiant->filiere_selectionnee_id = $filiere_id;
                    $etudiant->points_selection = $resultat['points_total'];

                    // Sauvegarder les détails seulement s'ils existent
                    if (!empty($resultat['details'])) {
                        $etudiant->details_selection = json_encode($resultat['details']);
                    }

                    $etudiant->save();
                }

                // Récupérer le nombre de places disponibles
                $filiereNiveau = $filiere->niveaux()->where('niveau_id', $niveau_id)->first();
                $places_disponibles = $filiereNiveau ? $filiereNiveau->pivot->places_disponibles : 0;

                // Ajouter aux résultats globaux
                $resultatsGlobaux['details'][] = [
                    'niveau' => $niveau->nom,
                    'filiere' => $filiere->nom,
                    'nombre_etudiants' => count($etudiants),
                    'places_disponibles' => $places_disponibles
                ];

                $resultatsGlobaux['nombre_total_etudiants'] += count($etudiants);

                // Enregistrer les logs si des critères existent
                if ($critere) {
                    $this->logSelectionDetails($selection->id, $etudiants, $etudiants);
                }
            }
        }
    }

    protected function calculerPointsDetailles(Etudiant $etudiant, CritereSelection $critere)
    {
        $criteres = json_decode($critere->criteres_json, true) ?? [];
        $bonus = json_decode($critere->bonus_json, true) ?? [];

        $details = [
            'criteres' => [],
            'bonus' => [],
            'choix_filiere' => null
        ];

        $total = 0;

        // Calculer la somme totale des poids pour normalisation
        $poidsTotal = 0;
        foreach ($criteres as $critereDef) {
            $poidsTotal += $critereDef['poids'];
        }

        // Facteur de normalisation (pour ramener à 100%)
        $facteurNormalisation = ($poidsTotal > 0) ? 100 / $poidsTotal : 1;

        // Calcul pour chaque critère avec normalisation des poids
        foreach ($criteres as $critereDef) {
            $valeurActuelle = $this->getValeurCritere($etudiant, $critereDef['type']);
            $score = $this->evaluerCritere($valeurActuelle, $critereDef['operateur'], $critereDef['valeur']);

            // Normalisation du poids pour que la somme soit 100%
            $poidsNormalise = $critereDef['poids'] * $facteurNormalisation;
            $pointsCritere = $score * ($poidsNormalise / 100);

            $details['criteres'][] = [
                'type' => $critereDef['type'],
                'operateur' => $critereDef['operateur'],
                'valeur_reference' => $critereDef['valeur'],
                'valeur_actuelle' => $valeurActuelle,
                'score_brut' => $score,
                'poids_original' => $critereDef['poids'],
                'poids_normalise' => $poidsNormalise,
                'points' => $pointsCritere
            ];

            $total += $pointsCritere;
        }

        // Application des bonus/malus
        foreach ($bonus as $bonusDef) {
            $applicable = $this->getValeurBonus($etudiant, $bonusDef['categorie'], $bonusDef['valeur']);
            $pointsBonus = 0;

            if ($applicable) {
                if ($bonusDef['type'] === 'bonus') {
                    $pointsBonus = floatval($bonusDef['points']);
                    $total += $pointsBonus;
                } elseif ($bonusDef['type'] === 'malus') {
                    $pointsBonus = -floatval($bonusDef['points']);
                    $total += $pointsBonus;
                } elseif ($bonusDef['type'] === 'multiplicateur') {
                    $facteur = floatval($bonusDef['points']) / 100;
                    $pointsBonus = $total * $facteur;
                    $total = $total * (1 + $facteur);
                }

                $details['bonus'][] = [
                    'categorie' => $bonusDef['categorie'],
                    'valeur' => $bonusDef['valeur'],
                    'type' => $bonusDef['type'],
                    'points' => $pointsBonus
                ];
            }
        }

        // Points pour le choix de filière
        $pointsChoix = 0;
        if ($etudiant->premier_choix_id == $critere->filiere_id) {
            $pointsChoix = 15;
            $details['choix_filiere'] = [
                'type' => 'premier_choix',
                'points' => $pointsChoix
            ];
        } elseif ($etudiant->deuxieme_choix_id == $critere->filiere_id) {
            $pointsChoix = 10;
            $details['choix_filiere'] = [
                'type' => 'deuxieme_choix',
                'points' => $pointsChoix
            ];
        } elseif ($etudiant->troisieme_choix_id == $critere->filiere_id) {
            $pointsChoix = 5;
            $details['choix_filiere'] = [
                'type' => 'troisieme_choix',
                'points' => $pointsChoix
            ];
        }

        $total += $pointsChoix;

        return [
            'total' => $total,
            'details' => $details
        ];
    }

    protected function logSelectionDetails($selection_id, $resultats, $selectionnes)
    {
        // Créer un fichier de log détaillé pour cette sélection
        $logData = [
            'selection_id' => $selection_id,
            'date' => now()->format('Y-m-d H:i:s'),
            'nombre_candidats' => count($resultats),
            'nombre_selectionnes' => count($selectionnes),
            'seuil_points' => end($selectionnes)['points_total'] ?? 0,
            'resultats_complets' => $resultats
        ];

        // Sauvegarder dans un fichier JSON
        $logPath = storage_path('logs/selections');
        if (!file_exists($logPath)) {
            mkdir($logPath, 0755, true);
        }

        file_put_contents(
            "{$logPath}/selection_{$selection_id}.json",
            json_encode($logData, JSON_PRETTY_PRINT)
        );

        // Vous pourriez également sauvegarder dans une table de base de données pour une meilleure traçabilité
    }

    /**
     * Calculer les points d'un étudiant en fonction des critères de sélection
     *
     * @param Etudiant $etudiant
     * @param CritereSelection $critere
     * @return float Total des points
     */
    protected function calculerPoints(Etudiant $etudiant, CritereSelection $critere)
    {
        $points = 0;
        $criteres = json_decode($critere->criteres_json, true);
        $bonus = json_decode($critere->bonus_json, true);

        // Calculer la somme totale des poids pour normalisation
        $poidsTotal = 0;
        foreach ($criteres as $critereDef) {
            $poidsTotal += $critereDef['poids'];
        }

        // Facteur de normalisation (pour ramener à 100%)
        $facteurNormalisation = ($poidsTotal > 0) ? 100 / $poidsTotal : 1;

        // Application des critères avec normalisation des poids
        foreach ($criteres as $critereDef) {
            $valeurCritere = $this->getValeurCritere($etudiant, $critereDef['type']);
            $pointsCritere = $this->evaluerCritere($valeurCritere, $critereDef['operateur'], $critereDef['valeur']);

            // Normalisation du poids pour que la somme soit 100%
            $poidsNormalise = $critereDef['poids'] * $facteurNormalisation;
            $points += $pointsCritere * ($poidsNormalise / 100);
        }

        // Application des bonus/malus
        if (!empty($bonus)) {
            foreach ($bonus as $bonusDef) {
                $valeurBonus = $this->getValeurBonus($etudiant, $bonusDef['categorie'], $bonusDef['valeur']);

                if ($valeurBonus) {
                    if ($bonusDef['type'] === 'bonus') {
                        $points += floatval($bonusDef['points']);
                    } elseif ($bonusDef['type'] === 'malus') {
                        $points -= floatval($bonusDef['points']);
                    } elseif ($bonusDef['type'] === 'multiplicateur') {
                        $facteur = floatval($bonusDef['points']) / 100;
                        $points = $points * (1 + $facteur);
                    }
                }
            }
        }

        // Points de base en fonction du choix de filière
        if ($etudiant->premier_choix_id == $critere->filiere_id) {
            $points += 15; // Bonus pour premier choix
        } elseif ($etudiant->deuxieme_choix_id == $critere->filiere_id) {
            $points += 10; // Bonus pour deuxième choix
        } elseif ($etudiant->troisieme_choix_id == $critere->filiere_id) {
            $points += 5; // Bonus pour troisième choix
        }

        return $points;
    }

    private function getValeurCritere(Etudiant $etudiant, $type)
    {
        switch ($type) {
            case 'moyenne_bac':
                return $etudiant->moyenne_bac;
            case 'note_math':
                return $etudiant->note_math;
            case 'note_physique':
                return $etudiant->note_physique;
            case 'note_svteehb':
                return $etudiant->note_svteehb;
            case 'note_informatique':
                return $etudiant->note_informatique;
            case 'mgp':
                return $etudiant->mgp;
            case 'choix_filiere':
                return $etudiant->premier_choix_id; // On retourne l'ID pour pouvoir comparer
            case 'age':
                return now()->diffInYears(Carbon::parse($etudiant->date_naissance));
            default:
                return null;
        }
    }

    private function evaluerCritere($valeur, $operateur, $reference)
    {
        if ($valeur === null) {
            return 0;
        }

        switch ($operateur) {
            case 'egal':
                return $valeur == $reference ? 100 : 0;
            case 'superieur':
                // Donner plus de points si la valeur dépasse largement la référence
                if ($valeur > $reference) {
                    // Calculer un bonus proportionnel à l'écart (max 50% de bonus)
                    $ecart = $valeur - $reference;
                    $bonus = min(50, $ecart * 10); // 10 points de bonus par unité d'écart, plafonné à 50
                    return 100 + $bonus;
                }
                return 0;
            case 'inferieur':
                // Donner plus de points si la valeur est bien en-dessous de la référence
                if ($valeur < $reference) {
                    // Calculer un bonus proportionnel à l'écart
                    $ecart = $reference - $valeur;
                    $bonus = min(50, $ecart * 10); // Plafonné à 50% de bonus
                    return 100 + $bonus;
                }
                return 0;
            case 'superieur_egal':
                // Donner plus de points si la valeur dépasse largement la référence
                if ($valeur >= $reference) {
                    // Score de base de 100 si le critère est satisfait
                    $score = 100;

                    // Si la valeur dépasse strictement la référence, ajouter un bonus
                    if ($valeur > $reference) {
                        // Calculer un bonus proportionnel à l'écart
                        // Plus l'étudiant dépasse le seuil, plus il reçoit de points
                        $ecart = $valeur - $reference;

                        // Pour les notes (généralement sur 20)
                        if ($reference <= 20 && $valeur <= 20) {
                            // Jusqu'à 50% de bonus pour une note qui atteint 20/20
                            $max_ecart_possible = 20 - $reference;
                            if ($max_ecart_possible > 0) {
                                $bonus = 50 * ($ecart / $max_ecart_possible);
                                $score += $bonus;
                            }
                        }
                        // Pour la MGP (généralement sur 4)
                        else if ($reference <= 4 && $valeur <= 4) {
                            // Jusqu'à 50% de bonus pour une MGP qui atteint 4/4
                            $max_ecart_possible = 4 - $reference;
                            if ($max_ecart_possible > 0) {
                                $bonus = 50 * ($ecart / $max_ecart_possible);
                                $score += $bonus;
                            }
                        }
                        // Pour d'autres valeurs numériques
                        else {
                            // Bonus plafonné à 50%
                            $bonus = min(50, $ecart * 5);
                            $score += $bonus;
                        }
                    }

                    return $score;
                }
                return 0;
            case 'inferieur_egal':
                // Donner plus de points si la valeur est bien en-dessous de la référence
                if ($valeur <= $reference) {
                    // Score de base de 100 si le critère est satisfait
                    $score = 100;

                    // Si la valeur est strictement inférieure à la référence, ajouter un bonus
                    if ($valeur < $reference) {
                        // Calculer un bonus proportionnel à l'écart
                        $ecart = $reference - $valeur;
                        $bonus = min(50, $ecart * 5); // Plafonné à 50% de bonus
                        $score += $bonus;
                    }

                    return $score;
                }
                return 0;
            case 'entre':
                $bornes = explode(',', $reference);
                if (count($bornes) == 2) {
                    // Si la valeur est dans l'intervalle
                    if ($valeur >= $bornes[0] && $valeur <= $bornes[1]) {
                        // Calculer la position relative dans l'intervalle
                        $taille_intervalle = $bornes[1] - $bornes[0];
                        if ($taille_intervalle > 0) {
                            // Plus la valeur est au centre de l'intervalle, plus elle reçoit de points
                            $distance_optimale = ($bornes[1] + $bornes[0]) / 2;
                            $ecart_depuis_optimal = abs($valeur - $distance_optimale);
                            $position_relative = 1 - ($ecart_depuis_optimal / ($taille_intervalle / 2));

                            // Entre 100 et 150 points selon la position
                            return 100 + (50 * $position_relative);
                        }
                        return 100;
                    }
                }
                return 0;
            case 'premier':
                return 100; // Déjà traité par les points de base
            case 'deuxieme':
                return 50; // Évaluation partielle
            case 'troisieme':
                return 25; // Évaluation partielle
            default:
                return 0;
        }
    }

    private function getValeurBonus(Etudiant $etudiant, $categorie, $valeur)
    {
        switch ($categorie) {
            case 'serie_bac':
                return $etudiant->serie_bac === $valeur;
            case 'filiere_precedente':
                return $etudiant->filiere_precedente_id == $valeur;
            case 'region':
                return $etudiant->region_origine === $valeur;
            case 'sexe':
                return $etudiant->sexe === $valeur;
            default:
                return false;
        }
    }

    /**
     * Envoyer des notifications aux étudiants sélectionnés
     *
     * @param int $niveau_id
     * @param int $filiere_id
     * @return array Résultats de l'envoi
     */
    public function envoyerNotifications($niveau_id, $filiere_id)
    {
        $etudiants = Etudiant::where('niveau_id', $niveau_id)
            ->where('filiere_selectionnee_id', $filiere_id)
            ->where('est_selectionne', true)
            ->where('notification_envoyee', false)
            ->get();

        if ($etudiants->isEmpty()) {
            return [
                'statut' => 'info',
                'message' => 'Aucun étudiant à notifier pour cette filière et ce niveau.',
                'nombre_etudiants' => 0
            ];
        }

        $filiere = Filiere::findOrFail($filiere_id);
        $niveau = Niveau::findOrFail($niveau_id);
        $compteur = 0;
        $erreurs = [];

        foreach ($etudiants as $etudiant) {
            try {
                // Envoi de l'email
                Mail::to($etudiant->email)->send(new NotificationSelection($etudiant, $filiere, $niveau));

                // Marquer comme notifié
                $etudiant->notification_envoyee = true;
                $etudiant->save();

                $compteur++;
            } catch (\Exception $e) {
                Log::error("Erreur lors de l'envoi de notification à {$etudiant->email}: " . $e->getMessage());
                $erreurs[] = "Erreur pour {$etudiant->prenom} {$etudiant->nom} ({$etudiant->email}): {$e->getMessage()}";
            }
        }

        $result = [
            'statut' => 'success',
            'message' => sprintf('%d notifications ont été envoyées aux étudiants sélectionnés pour la filière %s au niveau %s.',
                $compteur,
                $filiere->nom,
                $niveau->nom
            ),
            'nombre_etudiants' => $compteur
        ];

        if (!empty($erreurs)) {
            $result['statut'] = 'warning';
            $result['message'] .= ' Cependant, certaines notifications n\'ont pas pu être envoyées.';
            $result['erreurs'] = $erreurs;
        }

        return $result;
    }

    /**
     * Réinitialiser la sélection pour un niveau et une filière
     *
     * @param int $niveau_id
     * @param int $filiere_id
     * @return array Résultats de la réinitialisation
     */
    public function reinitialiserSelection($niveau_id, $filiere_id)
    {
        $etudiants = Etudiant::where('niveau_id', $niveau_id)
            ->where('filiere_selectionnee_id', $filiere_id)
            ->where('est_selectionne', true)
            ->get();

        if ($etudiants->isEmpty()) {
            return [
                'statut' => 'info',
                'message' => 'Aucun étudiant sélectionné pour cette filière et ce niveau.',
                'nombre_etudiants' => 0
            ];
        }

        $compteur = 0;

        foreach ($etudiants as $etudiant) {
            $etudiant->est_selectionne = false;
            $etudiant->filiere_selectionnee_id = null;
            $etudiant->notification_envoyee = false;
            $etudiant->save();

            $compteur++;
        }

        // Supprimer les sélections correspondantes
        Selection::where('niveau_id', $niveau_id)
            ->where('filiere_id', $filiere_id)
            ->delete();

        $filiere = Filiere::findOrFail($filiere_id);
        $niveau = Niveau::findOrFail($niveau_id);

        return [
            'statut' => 'success',
            'message' => sprintf('La sélection pour la filière %s au niveau %s a été réinitialisée. %d étudiants ont été désélectionnés.',
                $filiere->nom,
                $niveau->nom,
                $compteur
            ),
            'nombre_etudiants' => $compteur
        ];
    }

    /**
     * Obtenir les statistiques de sélection
     *
     * @param int|null $niveau_id
     * @param int|null $filiere_id
     * @return array
     */
    public function obtenirStatistiques($niveau_id = null, $filiere_id = null)
    {
        $query = Etudiant::where('est_selectionne', true);

        if ($niveau_id) {
            $query->where('niveau_id', $niveau_id);
        }

        if ($filiere_id) {
            $query->where('filiere_selectionnee_id', $filiere_id);
        }

        // Total des étudiants sélectionnés
        $total = $query->count();

        // Répartition par sexe
        $hommes = (clone $query)->where('sexe', 'M')->count();
        $femmes = (clone $query)->where('sexe', 'F')->count();

        // Répartition par région
        $regions = (clone $query)
            ->select('region_origine', DB::raw('count(*) as total'))
            ->groupBy('region_origine')
            ->get()
            ->pluck('total', 'region_origine')
            ->toArray();

        return [
            'total' => $total,
            'hommes' => $hommes,
            'femmes' => $femmes,
            'regions' => $regions,
            'pourcentage_hommes' => $total > 0 ? round(($hommes / $total) * 100, 2) : 0,
            'pourcentage_femmes' => $total > 0 ? round(($femmes / $total) * 100, 2) : 0
        ];
    }
}
