@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Critères de sélection existants</h1>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-list me-1"></i>
                Configurations enregistrées
            </div>
            <a href="{{ route('admin.criteres.creer') }}" class="btn btn-primary">
                <i class="fas fa-plus me-1"></i> Nouvelle configuration
            </a>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table id="criteresTable" class="table table-bordered table-striped">
                    <thead>
                        <tr>
                            <th>Niveau</th>
                            <th>Filière</th>
                            <th>Places</th>
                            <th>Nb. de critères</th>
                            <th>Nb. de bonus/malus</th>
                            <th>Date de création</th>
                            <th>Date de mise à jour</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($criteres as $critere)
                        @php
                            $criteres_array = json_decode($critere->criteres_json ?? '[]', true);
                            $bonus_array = json_decode($critere->bonus_json ?? '[]', true);
                        @endphp
                        <tr>
                            <td>{{ $critere->niveau->nom }}</td>
                            <td>{{ $critere->filiere->nom }}</td>
                            <td>
                                @php
                                    $places = $critere->filiere->niveaux->where('id', $critere->niveau_id)->first()->pivot->places_disponibles ?? 0;
                                @endphp
                                {{ $places }}
                            </td>
                            <td>{{ count($criteres_array) }}</td>
                            <td>{{ count($bonus_array) }}</td>
                            <td>{{ $critere->created_at->format('d/m/Y H:i') }}</td>
                            <td>{{ $critere->updated_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-critere" data-id="{{ $critere->id }}" data-bs-toggle="modal" data-bs-target="#viewCritereModal">
                                    <i class="fas fa-eye"></i> Voir
                                </button>
                                <a href="{{ route('admin.criteres.modifier', $critere->id) }}" class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i> Modifier
                                </a>
                                <button type="button" class="btn btn-sm btn-danger delete-critere" data-id="{{ $critere->id }}">
                                    <i class="fas fa-trash"></i> Supprimer
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

<!-- Modal de visualisation des critères -->
<div class="modal fade" id="viewCritereModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails des critères de sélection</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4">
                        <h6>Informations générales</h6>
                        <dl class="row">
                            <dt class="col-sm-4">Niveau:</dt>
                            <dd class="col-sm-8" id="detail-niveau"></dd>

                            <dt class="col-sm-4">Filière:</dt>
                            <dd class="col-sm-8" id="detail-filiere"></dd>

                            <dt class="col-sm-4">Places:</dt>
                            <dd class="col-sm-8" id="detail-places"></dd>
                        </dl>
                    </div>
                    <div class="col-md-8">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="criteres-tab" data-bs-toggle="tab" data-bs-target="#criteres" type="button" role="tab" aria-controls="criteres" aria-selected="true">Critères</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="bonus-tab" data-bs-toggle="tab" data-bs-target="#bonus" type="button" role="tab" aria-controls="bonus" aria-selected="false">Bonus/Malus</button>
                            </li>
                        </ul>
                        <div class="tab-content mt-3" id="myTabContent">
                            <div class="tab-pane fade show active" id="criteres" role="tabpanel" aria-labelledby="criteres-tab">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Type de critère</th>
                                                <th>Opérateur</th>
                                                <th>Valeur</th>
                                                <th>Poids (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody id="criteres-table-body">
                                            <!-- Données chargées dynamiquement -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="bonus" role="tabpanel" aria-labelledby="bonus-tab">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm">
                                        <thead>
                                            <tr>
                                                <th>Catégorie</th>
                                                <th>Valeur</th>
                                                <th>Type d'ajustement</th>
                                                <th>Points/Facteur</th>
                                            </tr>
                                        </thead>
                                        <tbody id="bonus-table-body">
                                            <!-- Données chargées dynamiquement -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
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

<!-- Formulaire caché pour la suppression -->
<form id="delete-form" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialiser la table
    const dataTable = new simpleDatatables.DataTable('#criteresTable');

    // Gestion de l'affichage des détails
    document.querySelectorAll('.view-critere').forEach(button => {
        button.addEventListener('click', function() {
            const critereId = this.getAttribute('data-id');

            fetch(`/admin/criteres/${critereId}/details`)
                .then(response => response.json())
                .then(data => {
                    // Informations générales
                    document.getElementById('detail-niveau').textContent = data.niveau.nom;
                    document.getElementById('detail-filiere').textContent = data.filiere.nom;
                    document.getElementById('detail-places').textContent = data.filiere.places_disponibles;

                    // Critères
                    const criteresTableBody = document.getElementById('criteres-table-body');
                    criteresTableBody.innerHTML = '';

                    const criteres = JSON.parse(data.criteres_json || '[]');
                    criteres.forEach(critere => {
                        const row = document.createElement('tr');

                        const typeCell = document.createElement('td');
                        typeCell.textContent = getTypeLabel(critere.type);

                        const operateurCell = document.createElement('td');
                        operateurCell.textContent = getOperateurLabel(critere.operateur);

                        const valeurCell = document.createElement('td');
                        valeurCell.textContent = critere.valeur;

                        const poidsCell = document.createElement('td');
                        poidsCell.textContent = critere.poids + '%';

                        row.appendChild(typeCell);
                        row.appendChild(operateurCell);
                        row.appendChild(valeurCell);
                        row.appendChild(poidsCell);

                        criteresTableBody.appendChild(row);
                    });

                    // Bonus/Malus
                    const bonusTableBody = document.getElementById('bonus-table-body');
                    bonusTableBody.innerHTML = '';

                    const bonus = JSON.parse(data.bonus_json || '[]');
                    bonus.forEach(bonusItem => {
                        const row = document.createElement('tr');

                        const categorieCell = document.createElement('td');
                        categorieCell.textContent = getCategorieLabel(bonusItem.categorie);

                        const valeurCell = document.createElement('td');
                        valeurCell.textContent = getBonusValeurLabel(bonusItem.categorie, bonusItem.valeur);

                        const typeCell = document.createElement('td');
                        typeCell.textContent = getBonusTypeLabel(bonusItem.type);

                        const pointsCell = document.createElement('td');
                        pointsCell.textContent = bonusItem.points;

                        row.appendChild(categorieCell);
                        row.appendChild(valeurCell);
                        row.appendChild(typeCell);
                        row.appendChild(pointsCell);

                        bonusTableBody.appendChild(row);
                    });
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des détails:', error);
                });
        });
    });

    // Gestion de la suppression
    document.querySelectorAll('.delete-critere').forEach(button => {
        button.addEventListener('click', function() {
            const critereId = this.getAttribute('data-id');

            if (confirm('Êtes-vous sûr de vouloir supprimer cette configuration de critères ?')) {
                const form = document.getElementById('delete-form');
                form.action = `/admin/criteres/${critereId}`;
                form.submit();
            }
        });
    });

    // Fonction pour obtenir le libellé d'un type de critère
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

    // Fonction pour obtenir le libellé d'un opérateur
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

    // Fonction pour obtenir le libellé d'une catégorie de bonus
    function getCategorieLabel(categorie) {
        const categories = {
            'serie_bac': 'Série du Baccalauréat',
            'filiere_precedente': 'Filière précédente',
            'region': 'Région d\'origine',
            'sexe': 'Sexe'
        };

        return categories[categorie] || categorie;
    }

    // Fonction pour obtenir le libellé d'une valeur de bonus
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

    // Fonction pour obtenir le libellé d'un type de bonus
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
