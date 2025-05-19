<?php

namespace App\Http\Controllers;

use App\Models\Etudiant;
use App\Models\Filiere;
use App\Models\Niveau;
use App\Models\Selection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatistiquesController extends Controller
{
    public function index(Request $request)
    {
        $niveaux = Niveau::all();
        $filieres = Filiere::all();

        $niveau_id = $request->input('niveau_id');
        $filiere_id = $request->input('filiere_id');

        // Données pour la répartition par sexe
        $query = Etudiant::where('est_selectionne', true);

        if ($niveau_id) {
            $query->where('niveau_id', $niveau_id);
        }

        if ($filiere_id) {
            $query->where('filiere_selectionnee_id', $filiere_id);
        }

        $hommes = (clone $query)->where('sexe', 'M')->count();
        $femmes = (clone $query)->where('sexe', 'F')->count();

        $donneesRepartitionSexe = [
            'Hommes' => $hommes,
            'Femmes' => $femmes
        ];

        // Données pour la répartition par région
        $donneesRepartitionRegion = (clone $query)
            ->select('region_origine', DB::raw('count(*) as total'))
            ->groupBy('region_origine')
            ->pluck('total', 'region_origine')
            ->toArray();

        // Données pour l'évolution des sélections
        $donneesEvolutionSelections = Selection::select(
                DB::raw('DATE(date_selection) as date'),
                DB::raw('SUM(nombre_etudiants_selectionnes) as nombre')
            )
            ->when($niveau_id, function($q) use ($niveau_id) {
                return $q->where('niveau_id', $niveau_id);
            })
            ->when($filiere_id, function($q) use ($filiere_id) {
                return $q->where('filiere_id', $filiere_id);
            })
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(function($item) {
                return [
                    'date' => date('d/m/Y', strtotime($item->date)),
                    'nombre' => $item->nombre
                ];
            })
            ->toArray();

        // Données pour le taux de remplissage
        $donneesTauxRemplissage = DB::table('filieres')
            ->select(
                'filieres.nom as filiere',
                'filiere_niveau.places_disponibles',
                DB::raw('COUNT(etudiants.id) as etudiants_selectionnes'),
                'niveaux.nom as niveau'
            )
            ->join('filiere_niveau', 'filieres.id', '=', 'filiere_niveau.filiere_id')
            ->join('niveaux', 'filiere_niveau.niveau_id', '=', 'niveaux.id')
            ->leftJoin('etudiants', function($join) {
                $join->on('filieres.id', '=', 'etudiants.filiere_selectionnee_id')
                    ->on('niveaux.id', '=', 'etudiants.niveau_id')
                    ->where('etudiants.est_selectionne', true);
            })
            ->when($niveau_id, function($q) use ($niveau_id) {
                return $q->where('niveaux.id', $niveau_id);
            })
            ->when($filiere_id, function($q) use ($filiere_id) {
                return $q->where('filieres.id', $filiere_id);
            })
            ->groupBy('filieres.id', 'filieres.nom', 'filiere_niveau.places_disponibles', 'niveaux.id', 'niveaux.nom')
            ->get()
            ->toArray();

        // Données pour le tableau
        $statsData = DB::table('etudiants')
            ->select(
                'filieres.nom as filiere_nom',
                'niveaux.nom as niveau_nom',
                'filiere_niveau.places_disponibles',
                DB::raw('COUNT(etudiants.id) as etudiants_selectionnes'),
                DB::raw('ROUND(SUM(CASE WHEN etudiants.sexe = "M" THEN 1 ELSE 0 END) / COUNT(etudiants.id) * 100, 2) as pourcentage_hommes'),
                DB::raw('ROUND(SUM(CASE WHEN etudiants.sexe = "F" THEN 1 ELSE 0 END) / COUNT(etudiants.id) * 100, 2) as pourcentage_femmes'),
                DB::raw('MAX(selections.date_selection) as date_selection')
            )
            ->join('filieres', 'etudiants.filiere_selectionnee_id', '=', 'filieres.id')
            ->join('niveaux', 'etudiants.niveau_id', '=', 'niveaux.id')
            ->join('filiere_niveau', function($join) {
                $join->on('filieres.id', '=', 'filiere_niveau.filiere_id')
                    ->on('niveaux.id', '=', 'filiere_niveau.niveau_id');
            })
            ->leftJoin('selections', function($join) {
                $join->on('filieres.id', '=', 'selections.filiere_id')
                    ->on('niveaux.id', '=', 'selections.niveau_id');
            })
            ->where('etudiants.est_selectionne', true)
            ->when($niveau_id, function($q) use ($niveau_id) {
                return $q->where('etudiants.niveau_id', $niveau_id);
            })
            ->when($filiere_id, function($q) use ($filiere_id) {
                return $q->where('etudiants.filiere_selectionnee_id', $filiere_id);
            })
            ->groupBy('filieres.id', 'filieres.nom', 'niveaux.id', 'niveaux.nom', 'filiere_niveau.places_disponibles')
            ->get();

        return view('admin.statistiques', compact(
            'niveaux',
            'filieres',
            'donneesRepartitionSexe',
            'donneesRepartitionRegion',
            'donneesEvolutionSelections',
            'donneesTauxRemplissage',
            'statsData'
        ));
    }
}
