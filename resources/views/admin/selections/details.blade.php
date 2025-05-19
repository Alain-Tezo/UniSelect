@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Résultats détaillés de la sélection</h1>

    <div class="alert alert-info">
        <i class="fas fa-info-circle me-2"></i>
        Visualisez les résultats détaillés de la sélection pour la filière <strong>{{ $filiere->nom }}</strong> au niveau <strong>{{ $niveau->nom }}</strong>.
        Cette sélection a été effectuée le <strong>{{ $selection->date_selection->format('d/m/Y à H:i') }}</strong>.
    </div>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card h-100">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Statistiques</h5>
                </div>
                <div class="card-body">
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Places disponibles
                            <span class="badge bg-primary rounded-pill">{{ $filiere->places_disponibles }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Candidats
                            <span class="badge bg-secondary rounded-pill">{{ $statistiques['total_candidats'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Étudiants sélectionnés
                            <span class="badge bg-success rounded-pill">{{ $statistiques['total_selectionnes'] }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Score minimum requis
                            <span class="badge bg-info rounded-pill">{{ number_format($statistiques['score_minimum'], 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Score maximum obtenu
                            <span class="badge bg-info rounded-pill">{{ number_format($statistiques['score_maximum'], 2) }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Score moyen
                            <span class="badge bg-info rounded-pill">{{ number_format($statistiques['score_moyen'], 2) }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card h-100">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Distribution des scores</h5>
                </div>
                <div class="card-body">
                    <canvas id="scoresDistribution" width="100%" height="250"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                Liste des étudiants sélectionnés
            </div>
            <div>
                <button type="button" class="btn btn-primary" id="exportPdfBtn">
                    <i class="fas fa-file-pdf me-1"></i> Exporter en PDF
                </button>
                <button type="button" class="btn btn-success" id="envoyerNotificationsBtn">
                    <i class="fas fa-envelope me-1"></i> Notifier les étudiants
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="etudiantsTable">
                    <thead>
                        <tr>
                            <th>Rang</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Score total</th>
                            <th>Choix</th>
                            <th>Détails</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($etudiantsSelectionnes as $index => $etudiant)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $etudiant->nom }}</td>
                            <td>{{ $etudiant->prenom }}</td>
                            <td>{{ number_format($etudiant->points_selection, 2) }}</td>
                            <td>
                                @if ($etudiant->premier_choix_id == $filiere->id)
                                    <span class="badge bg-success">1er choix</span>
                                @elseif ($etudiant->deuxieme_choix_id == $filiere->id)
                                    <span class="badge bg-warning">2ème choix</span>
                                @elseif ($etudiant->troisieme_choix_id == $filiere->id)
                                    <span class="badge bg-danger">3ème choix</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-details"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailsModal"
                                        data-etudiant="{{ json_encode($etudiant) }}"
                                        data-details="{{ $etudiant->details_selection }}">
                                    <i class="fas fa-chart-bar me-1"></i> Détails des points
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails -->
<div class="modal fade" id="detailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
    <h5 class="modal-title">Détails du calcul des points</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <div class="row mb-3">
        <div class="col-md-6">
            <h6>Informations de l'étudiant</h6>
            <dl class="row">
                <dt class="col-sm-4">Nom complet:</dt>
                <dd class="col-sm-8" id="detail-nom"></dd>

                <dt class="col-sm-4">Email:</dt>
                <dd class="col-sm-8" id="detail-email"></dd>

                <dt class="col-sm-4">Score total:</dt>
                <dd class="col-sm-8" id="detail-score"></dd>
            </dl>
        </div>
        <div class="col-md-6">
            <h6>Répartition des points</h6>
            <canvas id="pointsChart" width="100%" height="200"></canvas>
        </div>
    </div>

    <ul class="nav nav-tabs" id="detailTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="criteres-tab" data-bs-toggle="tab" data-bs-target="#criteres-tab-pane" type="button" role="tab" aria-controls="criteres-tab-pane" aria-selected="true">Critères</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="bonus-tab" data-bs-toggle="tab" data-bs-target="#bonus-tab-pane" type="button" role="tab" aria-controls="bonus-tab-pane" aria-selected="false">Bonus/Malus</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="choix-tab" data-bs-toggle="tab" data-bs-target="#choix-tab-pane" type="button" role="tab" aria-controls="choix-tab-pane" aria-selected="false">Choix de filière</button>
        </li>
    </ul>
    <div class="tab-content pt-3" id="detailTabsContent">
        <div class="tab-pane fade show active" id="criteres-tab-pane" role="tabpanel" aria-labelledby="criteres-tab" tabindex="0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Critère</th>
                            <th>Opérateur</th>
                            <th>Valeur de référence</th>
                            <th>Valeur de l'étudiant</th>
                            <th>Score brut</th>
                            <th>Poids (%)</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody id="criteres-detail-body">
                        <!-- Rempli dynamiquement -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="bonus-tab-pane" role="tabpanel" aria-labelledby="bonus-tab" tabindex="0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead>
                        <tr>
                            <th>Catégorie</th>
                            <th>Valeur</th>
                            <th>Type d'ajustement</th>
                            <th>Points</th>
                        </tr>
                    </thead>
                    <tbody id="bonus-detail-body">
                        <!-- Rempli dynamiquement -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="tab-pane fade" id="choix-tab-pane" role="tabpanel" aria-labelledby="choix-tab" tabindex="0">
            <div class="alert alert-info" id="choix-detail">
                <!-- Rempli dynamiquement -->
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
</div>
</div>
</div>
</div>

<!-- Formulaire caché pour les notifications -->
<form id="notificationsForm" action="{{ route('admin.envoyer-notifications') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="selection_id" value="{{ $selection->id }}">
</form>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser DataTable
    const dataTable = new simpleDatatables.DataTable('#etudiantsTable');

    // Données pour le graphique de distribution
    const scoreLabels = @json($statistiques['intervalles']);
    const scoreData = @json($statistiques['distribution']);

    // Graphique de distribution des scores
    const ctxDistribution = document.getElementById('scoresDistribution').getContext('2d');
    new Chart(ctxDistribution, {
        type: 'bar',
        data: {
            labels: scoreLabels,
            datasets: [{
                label: 'Nombre d\'étudiants',
                data: scoreData,
                backgroundColor: 'rgba(75, 192, 192, 0.7)',
                borderColor: 'rgba(75, 192, 192, 1)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });

    // Détails des points
    document.querySelectorAll('.view-details').forEach(button => {
        button.addEventListener('click', function() {
            const etudiant = JSON.parse(this.getAttribute('data-etudiant'));
            const details = JSON.parse(this.getAttribute('data-details'));

            // Informations de base
            document.getElementById('detail-nom').textContent = `${etudiant.prenom} ${etudiant.nom}`;
            document.getElementById('detail-email').textContent = etudiant.email;
            document.getElementById('detail-score').textContent = etudiant.points_selection.toFixed(2);

            // Tableau des critères
            const criteresBody = document.getElementById('criteres-detail-body');
            criteresBody.innerHTML = '';

            let totalCriteres = 0;
            details.details.criteres.forEach(critere => {
                const row = document.createElement('tr');

                const typeCell = document.createElement('td');
                typeCell.textContent = getTypeLabel(critere.type);

                const operateurCell = document.createElement('td');
                operateurCell.textContent = getOperateurLabel(critere.operateur);

                const valeurRefCell = document.createElement('td');
                valeurRefCell.textContent = critere.valeur_reference;

                const valeurActuelleCell = document.createElement('td');
                valeurActuelleCell.textContent = critere.valeur_actuelle;

                const scoreBrutCell = document.createElement('td');
                scoreBrutCell.textContent = critere.score_brut;

                const poidsCell = document.createElement('td');
                poidsCell.textContent = critere.poids + '%';

                const pointsCell = document.createElement('td');
                pointsCell.textContent = critere.points.toFixed(2);

                row.appendChild(typeCell);
                row.appendChild(operateurCell);
                row.appendChild(valeurRefCell);
                row.appendChild(valeurActuelleCell);
                row.appendChild(scoreBrutCell);
                row.appendChild(poidsCell);
                row.appendChild(pointsCell);

                criteresBody.appendChild(row);
                totalCriteres += critere.points;
            });

            // Tableau des bonus
            const bonusBody = document.getElementById('bonus-detail-body');
            bonusBody.innerHTML = '';

            let totalBonus = 0;
            if (details.details.bonus && details.details.bonus.length > 0) {
                details.details.bonus.forEach(bonus => {
                    const row = document.createElement('tr');

                    const categorieCell = document.createElement('td');
                    categorieCell.textContent = getCategorieLabel(bonus.categorie);

                    const valeurCell = document.createElement('td');
                    valeurCell.textContent = getBonusValeurLabel(bonus.categorie, bonus.valeur);

                    const typeCell = document.createElement('td');
                    typeCell.textContent = getBonusTypeLabel(bonus.type);

                    const pointsCell = document.createElement('td');
                    pointsCell.textContent = bonus.points.toFixed(2);

                    row.appendChild(categorieCell);
                    row.appendChild(valeurCell);
                    row.appendChild(typeCell);
                    row.appendChild(pointsCell);

                    bonusBody.appendChild(row);
                    totalBonus += bonus.points;
                });
            } else {
                bonusBody.innerHTML = '<tr><td colspan="4" class="text-center">Aucun bonus ou malus appliqué</td></tr>';
            }

            // Détails du choix de filière
            const choixDetail = document.getElementById('choix-detail');
            let choixPoints = 0;

            if (details.details.choix_filiere) {
                const choixType = details.details.choix_filiere.type;
                choixPoints = details.details.choix_filiere.points;

                let message = '';
                if (choixType === 'premier_choix') {
                    message = `L'étudiant a sélectionné cette filière comme premier choix, recevant <strong>${choixPoints} points</strong> supplémentaires.`;
                } else if (choixType === 'deuxieme_choix') {
                    message = `L'étudiant a sélectionné cette filière comme deuxième choix, recevant <strong>${choixPoints} points</strong> supplémentaires.`;
                } else if (choixType === 'troisieme_choix') {
                    message = `L'étudiant a sélectionné cette filière comme troisième choix, recevant <strong>${choixPoints} points</strong> supplémentaires.`;
                }

                choixDetail.innerHTML = message;
            } else {
                choixDetail.innerHTML = 'Aucun point accordé pour le choix de filière.';
            }

            // Graphique de répartition des points
            const chartCanvas = document.getElementById('pointsChart');

            // Détruire le graphique existant s'il y en a un
            if (window.pointsChart) {
                window.pointsChart.destroy();
            }

            // Créer un nouveau graphique
            window.pointsChart = new Chart(chartCanvas.getContext('2d'), {
                type: 'pie',
                data: {
                    labels: ['Critères', 'Bonus/Malus', 'Choix de filière'],
                    datasets: [{
                        data: [totalCriteres, totalBonus, choixPoints],
                        backgroundColor: [
                            'rgba(54, 162, 235, 0.7)',
                            'rgba(255, 99, 132, 0.7)',
                            'rgba(255, 206, 86, 0.7)'
                        ],
                        borderColor: [
                            'rgba(54, 162, 235, 1)',
                            'rgba(255, 99, 132, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    const value = context.raw;
                                    const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                    const percentage = Math.round((value / total) * 100);
                                    return `${context.label}: ${value.toFixed(2)} points (${percentage}%)`;
                                }
                            }
                        }
                    }
                }
            });
        });
    });

    // Export PDF
    document.getElementById('exportPdfBtn').addEventListener('click', function() {
        const { jsPDF } = window.jspdf;
        const doc = new jsPDF();

        // Titre
        doc.setFontSize(18);
        doc.text('Liste des étudiants sélectionnés', 14, 20);

        // Sous-titre
        doc.setFontSize(12);
        doc.text(`Filière: ${@json($filiere->nom)}, Niveau: ${@json($niveau->nom)}`, 14, 30);
        doc.text(`Date de sélection: ${@json($selection->date_selection->format('d/m/Y'))}`, 14, 38);

        // Statistiques
        doc.setFontSize(14);
        doc.text('Statistiques:', 14, 48);
        doc.setFontSize(10);
        doc.text(`Places disponibles: ${@json($filiere->places_disponibles)}`, 20, 56);
        doc.text(`Candidats: ${@json($statistiques['total_candidats'])}`, 20, 62);
        doc.text(`Étudiants sélectionnés: ${@json($statistiques['total_selectionnes'])}`, 20, 68);
        doc.text(`Score minimum: ${@json($statistiques['score_minimum'].toFixed(2))}`, 20, 74);

        // Table
        doc.autoTable({
            startY: 85,
            head: [['Rang', 'Nom', 'Prénom', 'Score', 'Choix']],
            body: @json($etudiantsSelectionnes->map(function($etudiant, $index) use ($filiere) {
                $choix = '';
                if ($etudiant->premier_choix_id == $filiere->id) {
                    $choix = '1er choix';
                } elseif ($etudiant->deuxieme_choix_id == $filiere->id) {
                    $choix = '2ème choix';
                } elseif ($etudiant->troisieme_choix_id == $filiere->id) {
                    $choix = '3ème choix';
                }
                return [
                    $index + 1,
                    $etudiant->nom,
                    $etudiant->prenom,
                    number_format($etudiant->points_selection, 2),
                    $choix
                ];
            })),
            theme: 'striped',
            styles: { fontSize: 8 }
        });

        // Pied de page
        const pageCount = doc.internal.getNumberOfPages();
        for (let i = 1; i <= pageCount; i++) {
            doc.setPage(i);
            doc.setFontSize(8);
            doc.text(`Page ${i} sur ${pageCount}`, doc.internal.pageSize.width / 2, doc.internal.pageSize.height - 10, { align: 'center' });
            doc.text(`Document généré le ${new Date().toLocaleDateString()}`, 14, doc.internal.pageSize.height - 10);
        }

        // Sauvegarder
        doc.save(`selection-${@json($filiere->nom)}-${@json($niveau->nom)}.pdf`);
    });

    // Envoi de notifications
    document.getElementById('envoyerNotificationsBtn').addEventListener('click', function() {
        if (confirm('Êtes-vous sûr de vouloir envoyer des notifications par email à tous les étudiants sélectionnés ?')) {
            document.getElementById('notificationsForm').submit();
        }
    });

    // Fonctions utilitaires
    function getTypeLabel(type) {
        const types = {
            'moyenne_bac': 'Moyenne du Baccalauréat',
            'note_math': 'Note en Mathématiques',
            'note_physique': 'Note en Physique',
            'note_svteehb': 'Note en SVTEEHB',
            'note_informatique': 'Note en Informatique',
            'mgp': 'Moyenne Générale Pondérée (MGP)',
            'choix_filiere': 'Choix de Filière',
            'age': 'Âge de l\'étudiant'
        };

        return types[type] || type;
    }

    function getOperateurLabel(operateur) {
        const operateurs = {
            'egal': 'égal à (=)',
            'superieur': 'supérieur à (>)',
            'inferieur': 'inférieur à (<)',
            'superieur_egal': 'supérieur ou égal à (>=)',
            'inferieur_egal': 'inférieur ou égal à (<=)',
            'entre': 'compris entre',
            'premier': 'Premier choix',
            'deuxieme': 'Deuxième choix',
            'troisieme': 'Troisième choix'
        };

        return operateurs[operateur] || operateur;
    }

    function getCategorieLabel(categorie) {
        const categories = {
            'serie_bac': 'Série du Baccalauréat',
            'filiere_precedente': 'Filière précédente',
            'region': 'Région d\'origine',
            'sexe': 'Sexe'
        };

        return categories[categorie] || categorie;
    }

    function getBonusValeurLabel(categorie, valeur) {
        if (categorie === 'sexe') {
            return valeur === 'M' ? 'Masculin' : 'Féminin';
        }

        if (categorie === 'region') {
            const regions = {
                'nord': 'Nord',
                'sud': 'Sud',
                'est': 'Est',
                'ouest': 'Ouest',
                'centre': 'Centre',
                'Adamaoua': 'Adamaoua',
                'littoral': 'Littoral',
                'extrême nord': 'Extrême Nord',
                'nord-ouest': 'Nord-Ouest',
                'sud-ouest': 'Sud-Ouest'
            };

            return regions[valeur] || valeur;
        }

        if (categorie === 'serie_bac') {
            return 'Série ' + valeur;
        }

        return valeur;
    }

    function getBonusTypeLabel(type) {
        const types = {
            'bonus': 'Bonus (Points supplémentaires)',
            'malus': 'Malus (Points retirés)',
            'multiplicateur': 'Multiplicateur (x%)'
        };

        return types[type] || type;
    }
});
</script>
@endsection
@endsection
