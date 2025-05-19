@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Tableau de bord</h1>

    <div class="row">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalEtudiants }}</h3>
                            <div>Étudiants inscrits</div>
                        </div>
                        <div>
                            <i class="fas fa-users fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.etudiants') }}">Voir les détails</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalSelectionnes }}</h3>
                            <div>Étudiants sélectionnés</div>
                        </div>
                        <div>
                            <i class="fas fa-user-check fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="{{ route('admin.selections') }}">Voir les détails</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalFilieres }}</h3>
                            <div>Filières disponibles</div>
                        </div>
                        <div>
                            <i class="fas fa-graduation-cap fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">Voir les détails</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6">
            <div class="card bg-danger text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h3 class="mb-0">{{ $totalEtudiants - $totalSelectionnes }}</h3>
                            <div>Étudiants non sélectionnés</div>
                        </div>
                        <div>
                            <i class="fas fa-user-times fa-3x"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <a class="small text-white stretched-link" href="#">Voir les détails</a>
                    <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Étudiants par niveau
                </div>
                <div class="card-body">
                    <canvas id="etudiantsParNiveau" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Étudiants sélectionnés par filière
                </div>
                <div class="card-body">
                    <canvas id="etudiantsParFiliere" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Actions rapides
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-list-ul fa-3x text-primary mb-3"></i>
                                    <h5 class="mb-3">Liste des étudiants</h5>
                                    <p class="card-text">Consultez la liste complète des étudiants inscrits.</p>
                                    <a href="{{ route('admin.etudiants') }}" class="btn btn-primary">Accéder</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-cogs fa-3x text-success mb-3"></i>
                                    <h5 class="mb-3">Critères de sélection</h5>
                                    <p class="card-text">Définissez les critères pour la sélection des étudiants.</p>
                                    <a href="{{ route('admin.criteres') }}" class="btn btn-success">Configurer</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <div class="card h-100">
                                <div class="card-body text-center">
                                    <i class="fas fa-chart-line fa-3x text-info mb-3"></i>
                                    <h5 class="mb-3">Statistiques</h5>
                                    <p class="card-text">Consultez les statistiques détaillées par filière et niveau.</p>
                                    <a href="{{ route('admin.statistiques') }}" class="btn btn-info">Analyser</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Données pour les graphiques
    const niveauxData = @json($etudiants_par_niveau);
    const filieresData = @json($etudiants_par_filiere);

    // Graphique des étudiants par niveau
    const ctxNiveau = document.getElementById('etudiantsParNiveau').getContext('2d');
    new Chart(ctxNiveau, {
        type: 'bar',
        data: {
            labels: niveauxData.map(item => item.nom),
            datasets: [{
                label: 'Nombre d\'étudiants',
                data: niveauxData.map(item => item.total),
                backgroundColor: 'rgba(0, 123, 255, 0.7)',
                borderColor: 'rgba(0, 123, 255, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Graphique des étudiants par filière
    const ctxFiliere = document.getElementById('etudiantsParFiliere').getContext('2d');
    new Chart(ctxFiliere, {
        type: 'pie',
        data: {
            labels: filieresData.map(item => item.nom),
            datasets: [{
                label: 'Étudiants sélectionnés',
                data: filieresData.map(item => item.total),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.7)',
                    'rgba(54, 162, 235, 0.7)',
                    'rgba(255, 206, 86, 0.7)',
                    'rgba(75, 192, 192, 0.7)',
                    'rgba(153, 102, 255, 0.7)',
                    'rgba(255, 159, 64, 0.7)'
                ],
                borderColor: [
                    'rgba(255, 99, 132, 1)',
                    'rgba(54, 162, 235, 1)',
                    'rgba(255, 206, 86, 1)',
                    'rgba(75, 192, 192, 1)',
                    'rgba(153, 102, 255, 1)',
                    'rgba(255, 159, 64, 1)'
                ],
                borderWidth: 1
            }]
        }
    });
</script>
@endsection
@endsection
