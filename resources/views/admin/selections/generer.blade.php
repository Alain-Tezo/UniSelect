@extends('layouts.admin')

@section('title', 'Générer une sélection')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Générer une sélection d'étudiants</h1>

    <!-- Bandeau principal pour la sélection globale -->
    <div class="alert alert-success mb-4">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <i class="fas fa-check-circle fa-2x"></i>
            </div>
            <div>
                <h5 class="alert-heading mb-1">Sélection globale</h5>
                <p class="mb-0">
                    Générez toutes les listes d'admission en une seule opération. 
                    Cette approche évite qu'un même étudiant soit sélectionné dans plusieurs filières et garantit une répartition optimale.
                </p>
                <a href="{{ route('admin.selections.generer-global') }}" class="btn btn-success mt-2">
                    <i class="fas fa-globe me-2"></i> Lancer la génération globale
                </a>
            </div>
        </div>
    </div>

    <!-- Information sur les filières sélectives -->
    <div class="alert alert-primary mb-4">
        <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Filières sélectives et non-sélectives</h5>
        <p>Le système distingue maintenant deux types de filières :</p>
        <ul>
            <li><strong>Filières sélectives (Informatique, ICT4D)</strong> : La sélection s'effectue selon les critères définis et le nombre de places disponibles.</li>
            <li><strong>Filières non-sélectives</strong> : Tous les étudiants qui postulent sont automatiquement acceptés, sans limite de places.</li>
        </ul>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info-circle me-1"></i>
            Informations importantes
        </div>
        <div class="card-body">
            <p>Avant de générer une sélection, assurez-vous que :</p>
            <ul>
                <li>Les <strong>critères de sélection</strong> ont été correctement définis pour les filières sélectives</li>
                <li>Le <strong>nombre de places disponibles</strong> a été configuré pour chaque combinaison filière/niveau</li>
                <li>Les <strong>données des étudiants</strong> sont complètes et correctes, y compris leurs choix de filières</li>
            </ul>
            
            <p>Une fois la sélection générée, vous pourrez :</p>
            <ul>
                <li>Consulter la liste des étudiants sélectionnés</li>
                <li>Envoyer des notifications aux étudiants</li>
                <li>Réinitialiser la sélection si nécessaire</li>
            </ul>
        </div>
    </div>
</div>
@endsection
