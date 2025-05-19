@extends('layouts.admin')

@section('title', 'Générer une sélection globale')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Générer une sélection globale d'étudiants</h1>

    <!-- Affichage des messages d'erreur -->
    @if(session('error'))
        <div class="alert alert-danger">
            {!! session('error') !!}
        </div>
    @endif

    <!-- Affichage des messages de succès -->
    @if(session('success'))
        <div class="alert alert-success">
            <i class="fas fa-check-circle me-2"></i> {!! session('success') !!}
        </div>
    @endif

    <!-- Affichage des combinaisons manquantes -->
    @if(isset($combinaisons_manquantes) && count($combinaisons_manquantes) > 0)
        <div class="card mb-4 border-danger">
            <div class="card-header bg-danger text-white">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Critères manquants pour filières sélectives
            </div>
            <div class="card-body">
                <p>Veuillez configurer les critères de sélection pour les combinaisons filière sélective/niveau suivantes :</p>
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th>Niveau</th>
                                <th>Filière</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($combinaisons_manquantes as $combinaison)
                                <tr>
                                    <td>{{ $combinaison['niveau'] }}</td>
                                    <td>{{ $combinaison['filiere'] }} <span class="badge bg-danger">SÉLECTIVE</span></td>
                                    <td>
                                        <a href="{{ route('admin.criteres') }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-cog me-1"></i> Configurer les critères
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <i class="fas fa-globe me-1"></i>
            Générer une sélection globale pour toutes les filières et tous les niveaux
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i> Cette action va générer une liste de sélection optimisée pour <strong>toutes les filières et tous les niveaux</strong> en une seule opération. Le système attribue automatiquement chaque étudiant à une seule filière en tenant compte :
                <ul class="mb-0 mt-2">
                    <li>Des points obtenus selon les critères de sélection pour les filières sélectives</li>
                    <li>De la priorité des choix de l'étudiant (1er, 2ème ou 3ème choix)</li>
                    <li>Du nombre de places disponibles pour les filières sélectives</li>
                </ul>
            </div>

            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i> <strong>Important :</strong> Assurez-vous d'avoir correctement défini les critères de sélection pour les filières sélectives (Informatique et ICT4D) dans tous les niveaux avant de procéder à la génération globale.
            </div>

            <form action="{{ route('admin.generer-selection-global') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-12 text-center">
                        <button type="submit" class="btn btn-lg btn-success px-5 me-3">
                            <i class="fas fa-cogs me-2"></i> Lancer la génération globale
                        </button>

                        <button type="button" class="btn btn-lg btn-danger px-5" data-bs-toggle="modal" data-bs-target="#resetModal">
                            <i class="fas fa-trash-alt me-2"></i> Supprimer la sélection actuelle
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-question-circle me-1"></i>
            Prérequis pour la génération globale
        </div>
        <div class="card-body">
            <p>Avant de générer une sélection globale, assurez-vous que :</p>
            <ul>
                <li>Les <strong>critères de sélection</strong> ont été correctement définis pour les filières <strong>sélectives</strong> (Informatique et ICT4D) dans tous les niveaux</li>
                <li>Le <strong>nombre de places disponibles</strong> a été configuré pour chaque filière dans chaque niveau</li>
                <li>Les <strong>données des étudiants</strong> sont complètes et correctes, y compris leurs choix de filières</li>
            </ul>

            <div class="mt-3">
                <a href="{{ route('admin.selections.generer') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-2"></i> Revenir à la sélection par filière
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

<!-- Modal de confirmation pour réinitialiser la sélection globale -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="resetModalLabel">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Confirmer la suppression
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Attention :</strong> Cette action va supprimer <strong>TOUTES</strong> les sélections générées précédemment pour tous les niveaux et toutes les filières.
                </div>
                <p>Cette action va :</p>
                <ul>
                    <li>Supprimer toutes les entrées de la table des sélections</li>
                    <li>Réinitialiser le statut "sélectionné" pour tous les étudiants</li>
                    <li>Effacer les attributions de filières pour tous les étudiants</li>
                    <li>Supprimer les points de sélection et détails calculés</li>
                </ul>
                <p class="text-danger fw-bold">Cette action est irréversible !</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>
                    Annuler
                </button>
                <form action="{{ route('admin.reinitialiser-selection-globale') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i>
                        Confirmer la suppression
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
