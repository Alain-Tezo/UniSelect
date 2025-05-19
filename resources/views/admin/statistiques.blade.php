@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Statistiques et analyses</h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filtres
        </div>
        <div class="card-body">
            <form id="filtreForm" action="{{ route('admin.statistiques') }}" method="GET">
                <div class="row">
                    <div class="col-md-4">
                        <label for="niveau_id" class="form-label">Niveau d'étude</label>
                        <select class="form-select" id="niveau_id" name="niveau_id">
                            <option value="">Tous les niveaux</option>
                            @foreach ($niveaux as $niveau)
                                <option value="{{ $niveau->id }}" {{ request('niveau_id') == $niveau->id ? 'selected' : '' }}>
                                    {{ $niveau->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filiere_id" class="form-label">Filière</label>
                        <select class="form-select" id="filiere_id" name="filiere_id">
                            <option value="">Toutes les filières</option>
                            @foreach ($filieres as $filiere)
                                <option value="{{ $filiere->id }}" {{ request('filiere_id') == $filiere->id ? 'selected' : '' }}>
                                    {{ $filiere->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-pie me-1"></i>
                    Répartition par sexe
                </div>
                <div class="card-body">
                    <canvas id="repartitionSexeChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Répartition par région
                </div>
                <div class="card-body">
                    <canvas id="repartitionRegionChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-xl-6 col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-line me-1"></i>
                    Évolution des sélections
                </div>
                <div class="card-body">
                    <canvas id="evolutionSelectionsChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
        <div class="col-xl-6 col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    Taux de remplissage par filière
                </div>
                <div class="card-body">
                    <canvas id="tauxRemplissageChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-table me-1"></i>
                    Statistiques détaillées
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="statsTable">
                            <thead>
                                <tr>
                                    <th>Filière</th>
                                    <th>Niveau</th>
                                    <th>Places disponibles</th>
                                    <th>Étudiants sélectionnés</th>
                                    <th>Pourcentage hommes</th>
                                    <th>Pourcentage femmes</th>
                                    <th>Date dernière sélection</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statsData ?? [] as $stat)
                                <tr>
                                    <td>{{ $stat->filiere_nom }}</td>
                                    <td>{{ $stat->niveau_nom }}</td>
                                    <td>{{ $stat->places_disponibles }}</td>
                                    <td>{{ $stat->etudiants_selectionnes }}</td>
                                    <td>{{ $stat->pourcentage_hommes }}%</td>
                                    <td>{{ $stat->pourcentage_femmes }}%</td>
                                    <td>{{ $stat->date_selection ? date('d/m/Y H:i', strtotime($stat->date_selection)) : 'N/A' }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 offset-md-3">
            <div class="d-grid gap-2">
                <button id="exportPdfBtn" class="btn btn-danger">
                    <i class="fas fa-file-pdf me-2"></i> Exporter au format PDF
                </button>
                <button id="exportExcelBtn" class="btn btn-success">
                    <i class="fas fa-file-excel me-2"></i> Exporter au format Excel
                </button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser la table
        const dataTable = new simpleDatatables.DataTable('#statsTable');

        // Données pour les graphiques
        const donneesRepartitionSexe = @json($donneesRepartitionSexe ?? ['Hommes' => 0, 'Femmes' => 0]);
        const donneesRepartitionRegion = @json($donneesRepartitionRegion ?? []);
        const donneesEvolutionSelections = @json($donneesEvolutionSelections ?? []);
        const donneesTauxRemplissage = @json($donneesTauxRemplissage ?? []);

        // Graphique répartition par sexe
        const ctxSexe = document.getElementById('repartitionSexeChart').getContext('2d');
        new Chart(ctxSexe, {
            type: 'pie',
            data: {
                labels: ['Hommes', 'Femmes'],
                datasets: [{
                    data: [donneesRepartitionSexe.Hommes, donneesRepartitionSexe.Femmes],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.7)',
                        'rgba(255, 99, 132, 0.7)'
                    ],
                    borderColor: [
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                const percentage = Math.round((context.raw * 100) / total);
                                return `${context.label}: ${context.raw} (${percentage}%)`;
                            }
                        }
                    }
                }
            }
        });

        // Graphique répartition par région
        const ctxRegion = document.getElementById('repartitionRegionChart').getContext('2d');
        new Chart(ctxRegion, {
            type: 'bar',
            data: {
                labels: Object.keys(donneesRepartitionRegion),
                datasets: [{
                    label: 'Nombre d\'étudiants',
                    data: Object.values(donneesRepartitionRegion),
                    backgroundColor: 'rgba(75, 192, 192, 0.7)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique évolution des sélections
        const ctxEvolution = document.getElementById('evolutionSelectionsChart').getContext('2d');
        new Chart(ctxEvolution, {
            type: 'line',
            data: {
                labels: donneesEvolutionSelections.map(item => item.date),
                datasets: [{
                    label: 'Nombre d\'étudiants sélectionnés',
                    data: donneesEvolutionSelections.map(item => item.nombre),
                    fill: false,
                    backgroundColor: 'rgba(153, 102, 255, 0.7)',
                    borderColor: 'rgba(153, 102, 255, 1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Graphique taux de remplissage
        const ctxRemplissage = document.getElementById('tauxRemplissageChart').getContext('2d');
        new Chart(ctxRemplissage, {
            type: 'bar',
            data: {
                labels: donneesTauxRemplissage.map(item => item.filiere),
                datasets: [{
                    label: 'Places disponibles',
                    data: donneesTauxRemplissage.map(item => item.places_disponibles),
                    backgroundColor: 'rgba(255, 159, 64, 0.7)',
                    borderColor: 'rgba(255, 159, 64, 1)',
                    borderWidth: 1
                }, {
                    label: 'Étudiants sélectionnés',
                    data: donneesTauxRemplissage.map(item => item.etudiants_selectionnes),
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Export PDF
        document.getElementById('exportPdfBtn').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Ajouter le titre
            doc.setFontSize(18);
            doc.text('Rapport de statistiques des sélections', 14, 22);

            // Ajouter la date
            doc.setFontSize(12);
            doc.text(`Généré le ${new Date().toLocaleDateString()}`, 14, 30);

            // Ajouter les filtres
            const niveau = document.getElementById('niveau_id').options[document.getElementById('niveau_id').selectedIndex].text;
            const filiere = document.getElementById('filiere_id').options[document.getElementById('filiere_id').selectedIndex].text;
            doc.text(`Filtres: Niveau - ${niveau}, Filière - ${filiere}`, 14, 38);

            // Ajouter la table
            doc.autoTable({
                html: '#statsTable',
                startY: 45,
                theme: 'striped',
                headStyles: { fillColor: [75, 115, 223] }
            });

            // Sauvegarder le PDF
            doc.save('statistiques-selections.pdf');
        });

        // Export Excel
        document.getElementById('exportExcelBtn').addEventListener('click', function() {
            // Créer un tableau qui contient les données de la table
            const table = document.getElementById('statsTable');
            const wb = XLSX.utils.table_to_book(table, { sheet: "Statistiques" });

            // Sauvegarder le fichier Excel
            XLSX.writeFile(wb, 'statistiques-selections.xlsx');
        });
    });
</script>
@endsection
@endsection
