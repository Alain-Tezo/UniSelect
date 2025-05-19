@extends('layouts.admin')

@section('title', 'Résultats de la sélection globale')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Résultats de la sélection globale</h1>

    <div class="card mb-4">
        <div class="card-header bg-success text-white">
            <i class="fas fa-check-circle me-1"></i>
            Sélection globale terminée
        </div>
        <div class="card-body">
            <div class="alert alert-success">
                <i class="fas fa-info-circle me-2"></i> {{ $resultat['message'] }}
            </div>

            <div class="row mt-4">
                <div class="col-md-6">
                    <div class="card mb-4 border-primary">
                        <div class="card-header bg-primary bg-opacity-25">
                            <i class="fas fa-chart-pie me-1"></i> Résumé
                        </div>
                        <div class="card-body">
                            <p><strong>Nombre total d'étudiants sélectionnés :</strong> {{ $resultat['nombre_total_etudiants'] }}</p>
                            <p><strong>Date et heure de la sélection :</strong> {{ now()->format('d/m/Y à H:i') }}</p>

                            <div class="mt-4">
                                <a href="{{ route('admin.selections') }}" class="btn btn-primary">
                                    <i class="fas fa-list me-2"></i> Voir la liste complète des sélectionnés
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="fas fa-cogs me-1"></i> Actions
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.selections') }}" class="btn btn-outline-success">
                                    <i class="fas fa-envelope me-2"></i> Envoyer les notifications
                                </a>

                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#resetModal">
                                    <i class="fas fa-undo me-2"></i> Réinitialiser la sélection
                                </button>

                                <a href="{{ route('admin.dashboard') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-home me-2"></i> Retour au tableau de bord
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Détails des résultats par filière et niveau -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Détails par filière et niveau
        </div>
        <div class="card-body">
            @if(count($details) > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Niveau</th>
                                <th>Filière</th>
                                <th>Type</th>
                                <th>Étudiants sélectionnés</th>
                                <th>Places disponibles</th>
                                <th>Taux de remplissage</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($details as $detail)
                                @php
                                    $filiere = App\Models\Filiere::where('nom', $detail['filiere'])->first();
                                    $est_selective = $filiere ? $filiere->est_selective : false;
                                @endphp
                                <tr>
                                    <td>{{ $detail['niveau'] }}</td>
                                    <td>{{ $detail['filiere'] }}</td>
                                    <td>
                                        @if($est_selective)
                                            <span class="badge bg-primary">Sélective</span>
                                        @else
                                            <span class="badge bg-secondary">Non-sélective</span>
                                        @endif
                                    </td>
                                    <td>{{ $detail['nombre_etudiants'] ?? 0 }}</td>
                                    <td>{{ $detail['places_disponibles'] ?? 'N/A' }}</td>
                                    <td>
                                        @if(isset($detail['places_disponibles']) && $detail['places_disponibles'] > 0)
                                            @php
                                                $tauxRemplissage = ($detail['nombre_etudiants'] / $detail['places_disponibles']) * 100;
                                            @endphp
                                            <div class="progress">
                                                <div class="progress-bar @if($tauxRemplissage < 50) bg-warning @elseif($tauxRemplissage < 80) bg-info @else bg-success @endif"
                                                     role="progressbar"
                                                     style="width: {{ min($tauxRemplissage, 100) }}%;"
                                                     aria-valuenow="{{ $tauxRemplissage }}"
                                                     aria-valuemin="0"
                                                     aria-valuemax="100">
                                                    {{ round($tauxRemplissage) }}%
                                                </div>
                                            </div>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-info">
                    Aucun détail disponible pour cette sélection.
                </div>
            @endif
        </div>
    </div>

    <!-- Messages d'erreurs éventuels -->
    @if(count($erreurs) > 0)
        <div class="card mb-4">
            <div class="card-header bg-warning">
                <i class="fas fa-exclamation-triangle me-1"></i>
                Avertissements
            </div>
            <div class="card-body">
                <ul class="list-group">
                    @foreach($erreurs as $erreur)
                        <li class="list-group-item list-group-item-warning">{{ $erreur }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
</div>

<!-- Modal de confirmation pour réinitialiser -->
<div class="modal fade" id="resetModal" tabindex="-1" aria-labelledby="resetModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="resetModalLabel">Confirmation de réinitialisation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><i class="fas fa-exclamation-triangle me-2 text-danger"></i> Êtes-vous sûr de vouloir réinitialiser la sélection ?</p>
                <p class="text-danger">Cette action est irréversible et supprimera toutes les sélections actuelles pour tous les niveaux et filières.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                <form action="{{ route('admin.selections.reinitialiser') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-2"></i> Réinitialiser
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
