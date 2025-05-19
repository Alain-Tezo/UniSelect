@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Définition des critères de sélection avancés</h1>

    <!-- Explication sur le fonctionnement des filières sélectives et non-sélectives -->
    <div class="alert alert-primary mb-4">
        <h5 class="alert-heading"><i class="fas fa-info-circle me-2"></i>Filières sélectives et non-sélectives</h5>
        <p>Dans ce système, il existe deux types de filières :</p>
        <ul>
            <li><strong>Filières sélectives (Informatique, ICT4D)</strong> : La sélection s'effectue selon les critères définis et le nombre de places disponibles. Seuls les meilleurs candidats selon le classement sont admis.</li>
            <li><strong>Filières non-sélectives (Mathématiques, Physique, Chimie, Biosciences)</strong> : Tous les étudiants qui postulent sont automatiquement acceptés, sans limite de places. <span class="text-danger">Aucun critère de sélection n'est requis pour ces filières.</span></li>
        </ul>
        <p class="mb-0">Lors de la génération de la sélection globale, le système attribuera d'abord les places dans les filières sélectives, puis placera les étudiants restants dans leurs filières non-sélectives de choix.</p>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-sliders-h me-1"></i>
            Configuration de définition des critères
        </div>
        <div class="card-body">
            <form id="criteresForm" action="{{ route('admin.criteres.enregistrer') }}" method="POST">
                @csrf

                <div class="row mb-4">
                    <div class="col-md-4">
                        <label for="niveau_id" class="form-label fw-bold">Niveau d'étude</label>
                        <select class="form-select" id="niveau_id" name="niveau_id" required>
                            <option value="">Sélectionnez un niveau</option>
                            @foreach ($niveaux as $niveau)
                                <option value="{{ $niveau->id }}">{{ $niveau->nom }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="filiere_id" class="form-label fw-bold">Filière</label>
                        <select class="form-select" id="filiere_id" name="filiere_id" required>
                            <option value="">Sélectionnez une filière</option>
                            @foreach ($filieres as $filiere)
                                <option value="{{ $filiere->id }}" {{ !$filiere->est_selective ? 'disabled' : '' }}>
                                    {{ $filiere->nom }}
                                    @if($filiere->est_selective)
                                        <span class="text-danger"> [SÉLECTIVE]</span>
                                    @else
                                        <span class="text-muted"> [NON-SÉLECTIVE - Critères non requis]</span>
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="places_disponibles" class="form-label fw-bold">Nombre de places disponibles</label>
                        <input type="number" class="form-control" id="places_disponibles" name="places_disponibles" min="1" required>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Critères de sélection</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Définissez les critères et leur importance (poids) pour la sélection des étudiants. Vous pouvez attribuer n'importe quel pourcentage à chaque critère - le système normalisera automatiquement ces valeurs pour obtenir un total de 100%. Par exemple, si vous définissez trois critères avec des poids de 40, 80 et 40, ils seront normalisés respectivement à 25%, 50% et 25%.
                        </div>

                        <!-- Conteneur dynamique pour les critères -->
                        <div id="criteres-container">
                            <!-- Les critères seront ajoutés dynamiquement ici -->
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" id="ajouter-critere" class="btn btn-success">
                                <i class="fas fa-plus me-1"></i> Ajouter un critère
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Bonus et malus</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Définissez des bonus ou malus basés sur des critères spécifiques comme la série du baccalauréat, la filière précédente, la région d'origine ou le sexe.
                        </div>

                        <!-- Conteneur dynamique pour les bonus/malus -->
                        <div id="bonus-container">
                            <!-- Les bonus seront ajoutés dynamiquement ici -->
                        </div>

                        <div class="text-center mt-3">
                            <button type="button" id="ajouter-bonus" class="btn btn-info">
                                <i class="fas fa-plus me-1"></i> Ajouter un bonus/malus
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">Aperçu de la configuration</h5>
                    </div>
                    <div class="card-body">
                        <div id="resume-configuration" class="border p-3 rounded bg-light">
                            <p class="fst-italic text-muted text-center">Les détails de votre configuration apparaîtront ici...</p>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save me-2"></i> Enregistrer cette configuration
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modèles pour les critères dynamiques (template) -->
<template id="template-critere">
    <div class="row critere-row mb-3 align-items-center border-bottom pb-3">
        <div class="col-md-3">
            <label class="form-label">Type de critère</label>
            <select class="form-select critere-type" name="criteres[IDX][type]" required>
                <option value="">Sélectionnez un type</option>
                <option value="moyenne_bac">Moyenne du Baccalauréat</option>
                <option value="note_math">Note en Mathématiques</option>
                <option value="note_physique">Note en Physique</option>
                <option value="note_svteehb">Note en SVTEEHB</option>
                <option value="note_informatique">Note en Informatique</option>
                <option value="mgp">Moyenne Générale Pondérée (MGP)</option>
                <option value="choix_filiere">Choix de Filière</option>
                <option value="age">Âge de l'étudiant</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Opérateur</label>
            <select class="form-select critere-operateur" name="criteres[IDX][operateur]" required>
                <option value="egal">égal à (=)</option>
                <option value="superieur">supérieur à (>)</option>
                <option value="inferieur">inférieur à (<)</option>
                <option value="superieur_egal">supérieur ou égal à (>=)</option>
                <option value="inferieur_egal">inférieur ou égal à (<=)</option>
                <option value="entre">compris entre</option>
            </select>
        </div>
        <div class="col-md-3 critere-valeur">
            <label class="form-label">Valeur</label>
            <input type="number" class="form-control" name="criteres[IDX][valeur]" step="0.01" required>
        </div>
        <div class="col-md-2">
            <label class="form-label">Poids (%)</label>
            <input type="number" class="form-control critere-poids" name="criteres[IDX][poids]" min="0" max="100" value="10" required>
        </div>
        <div class="col-md-1">
            <label class="form-label d-block">&nbsp;</label>
            <button type="button" class="btn btn-danger btn-sm supprimer-critere">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>

<template id="template-bonus">
    <div class="row bonus-row mb-3 align-items-center border-bottom pb-3">
        <div class="col-md-3">
            <label class="form-label">Catégorie</label>
            <select class="form-select bonus-categorie" name="bonus[IDX][categorie]" required>
                <option value="">Sélectionnez une catégorie</option>
                <option value="serie_bac">Série du Baccalauréat</option>
                <option value="filiere_precedente">Filière précédente</option>
                <option value="region">Région d'origine</option>
                <option value="sexe">Sexe</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Valeur spécifique</label>
            <select class="form-select bonus-valeur" name="bonus[IDX][valeur]" required>
                <option value="">Sélectionnez une valeur</option>
                <!-- Options dynamiques ajoutées par JavaScript -->
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">Type d'ajustement</label>
            <select class="form-select bonus-type" name="bonus[IDX][type]" required>
                <option value="bonus">Bonus (Points supplémentaires)</option>
                <option value="malus">Malus (Points retirés)</option>
                <option value="multiplicateur">Multiplicateur (x%)</option>
            </select>
        </div>
        <div class="col-md-2">
            <label class="form-label">Points/Facteur</label>
            <input type="number" class="form-control bonus-points" name="bonus[IDX][points]" step="0.01" required>
        </div>
        <div class="col-md-1">
            <label class="form-label d-block">&nbsp;</label>
            <button type="button" class="btn btn-danger btn-sm supprimer-bonus">
                <i class="fas fa-trash"></i>
            </button>
        </div>
    </div>
</template>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-list me-1"></i>
        Critères de sélection existants
    </div>
    <div class="card-body">
        @if($criteres && count($criteres) > 0)
            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>Niveau</th>
                            <th>Filière</th>
                            <th>Places</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($criteres as $critere)
                        <tr>
                            <td>{{ $critere->niveau->nom }}</td>
                            <td>{{ $critere->filiere->nom }}</td>
                            <td>
                                @php
                                    $places = $critere->filiere->niveaux->where('id', $critere->niveau_id)->first()->pivot->places_disponibles ?? 0;
                                @endphp
                                {{ $places }}
                            </td>
                            <td>{{ $critere->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-critere"
                                        data-bs-toggle="modal"
                                        data-bs-target="#viewCritereModal"
                                        data-id="{{ $critere->id }}">
                                    <i class="fas fa-eye"></i> Voir
                                </button>
                                <button type="button" class="btn btn-sm btn-primary edit-critere"
                                        data-id="{{ $critere->id }}">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-critere"
                                        data-id="{{ $critere->id }}">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Aucun critère de sélection n'a encore été défini. Utilisez le formulaire ci-dessus pour créer vos premiers critères.
            </div>
        @endif
    </div>
</div>

<!-- Modal pour voir les détails d'un critère -->
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



@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Données pour les options dynamiques
    const optionsBonus = {
        serie_bac: [
            { value: 'A', label: 'Série A' },
            { value: 'C', label: 'Série C' },
            { value: 'D', label: 'Série D' },
            { value: 'TI', label: 'Série TI' }
        ],
        filiere_precedente: [
            @foreach ($filieres as $filiere)
                { value: '{{ $filiere->id }}', label: '{{ $filiere->nom }}' },
            @endforeach
        ],
        region: [
            { value: 'nord', label: 'Nord' },
            { value: 'sud', label: 'Sud' },
            { value: 'est', label: 'Est' },
            { value: 'ouest', label: 'Ouest' },
            { value: 'centre', label: 'Centre' },
            { value: 'Adamaoua', label: 'Adamaoua' },
            { value: 'littoral', label: 'Littoral' },
            { value: 'extrême nord', label: 'Extrême Nord' },
            { value: 'nord-ouest', label: 'Nord-Ouest' },
            { value: 'sud-ouest', label: 'Sud-Ouest' }
        ],
        sexe: [
            { value: 'M', label: 'Masculin' },
            { value: 'F', label: 'Féminin' }
        ]
    };

    // Gestion dynamique des niveaux et critères associés
    const niveauSelect = document.getElementById('niveau_id');
    const criteresContainer = document.getElementById('criteres-container');
    const bonusContainer = document.getElementById('bonus-container');
    const templateCritere = document.getElementById('template-critere').content;
    const templateBonus = document.getElementById('template-bonus').content;
    const resumeConfig = document.getElementById('resume-configuration');

    let critereCount = 0;
    let bonusCount = 0;

    // Initialiser avec un critère par défaut
    ajouterCritere();

    // Bouton pour ajouter un critère
    document.getElementById('ajouter-critere').addEventListener('click', ajouterCritere);

    // Bouton pour ajouter un bonus
    document.getElementById('ajouter-bonus').addEventListener('click', ajouterBonus);

    // Fonction pour ajouter un critère
    function ajouterCritere() {
        const clone = document.importNode(templateCritere, true);

        // Remplacer l'index par un nombre unique
        updateElementIndexes(clone, 'IDX', critereCount);

        // Ajouter les événements
        clone.querySelector('.supprimer-critere').addEventListener('click', function() {
            this.closest('.critere-row').remove();
            updateResume();
        });

        clone.querySelector('.critere-type').addEventListener('change', function() {
            updateOperateursDisponibles(this);
            updateResume();
        });

        clone.querySelector('.critere-operateur').addEventListener('change', updateResume);
        clone.querySelector('.critere-poids').addEventListener('input', updateResume);

        // Ajouter au conteneur
        criteresContainer.appendChild(clone);
        critereCount++;

        updateResume();
    }

    // Fonction pour ajouter un bonus
    function ajouterBonus() {
        const clone = document.importNode(templateBonus, true);

        // Remplacer l'index par un nombre unique
        updateElementIndexes(clone, 'IDX', bonusCount);

        // Ajouter les événements
        clone.querySelector('.supprimer-bonus').addEventListener('click', function() {
            this.closest('.bonus-row').remove();
            updateResume();
        });

        clone.querySelector('.bonus-categorie').addEventListener('change', function() {
            updateBonusOptions(this);
            updateResume();
        });

        clone.querySelector('.bonus-type').addEventListener('change', updateResume);
        clone.querySelector('.bonus-points').addEventListener('input', updateResume);

        // Ajouter au conteneur
        bonusContainer.appendChild(clone);
        bonusCount++;

        updateResume();
    }

    // Mise à jour des options de bonus en fonction de la catégorie
    function updateBonusOptions(selectElement) {
        const categorie = selectElement.value;
        const optionsSelect = selectElement.closest('.bonus-row').querySelector('.bonus-valeur');

        // Vider les options actuelles
        optionsSelect.innerHTML = '<option value="">Sélectionnez une valeur</option>';

        // Ajouter les nouvelles options
        if (categorie && optionsBonus[categorie]) {
            optionsBonus[categorie].forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.label;
                optionsSelect.appendChild(optionElement);
            });
        }
    }

    // Mise à jour des opérateurs disponibles en fonction du type de critère
    function updateOperateursDisponibles(selectElement) {
        const type = selectElement.value;
        const operateurSelect = selectElement.closest('.critere-row').querySelector('.critere-operateur');

        // Réinitialiser les options
        operateurSelect.innerHTML = '';

        // Options de base pour tous les types
        const options = [
            { value: 'egal', label: 'égal à (=)' },
            { value: 'superieur', label: 'supérieur à (>)' },
            { value: 'inferieur', label: 'inférieur à (<)' },
            { value: 'superieur_egal', label: 'supérieur ou égal à (>=)' },
            { value: 'inferieur_egal', label: 'inférieur ou égal à (<=)' },
            { value: 'entre', label: 'compris entre' }
        ];

        // Options spécifiques pour certains types
        if (type === 'choix_filiere') {
            // Pour le choix de filière, on ne permet que l'égalité
            const options = [
                { value: 'premier', label: 'Premier choix' },
                { value: 'deuxieme', label: 'Deuxième choix' },
                { value: 'troisieme', label: 'Troisième choix' }
            ];

            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.label;
                operateurSelect.appendChild(optionElement);
            });
        } else {
            // Pour les types numériques
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option.value;
                optionElement.textContent = option.label;
                operateurSelect.appendChild(optionElement);
            });
        }
    }

    // Mise à jour du résumé de configuration
    function updateResume() {
        let resume = '<h6>Critères de sélection</h6><ul>';

        // Résumé des critères
        document.querySelectorAll('.critere-row').forEach((row) => {
            const type = row.querySelector('.critere-type').selectedOptions[0]?.text || 'Non défini';
            const operateur = row.querySelector('.critere-operateur').selectedOptions[0]?.text || 'Non défini';
            const valeur = row.querySelector('input[name*="[valeur]"]').value || 'Non défini';
            const poids = row.querySelector('.critere-poids').value || '0';

            resume += `<li><strong>${type}</strong> ${operateur} ${valeur} (Poids: ${poids}%)</li>`;
        });

        resume += '</ul><h6>Bonus et malus</h6><ul>';

        // Résumé des bonus
        document.querySelectorAll('.bonus-row').forEach((row) => {
            const categorie = row.querySelector('.bonus-categorie').selectedOptions[0]?.text || 'Non défini';
            const valeur = row.querySelector('.bonus-valeur').selectedOptions[0]?.text || 'Non défini';
            const type = row.querySelector('.bonus-type').selectedOptions[0]?.text || 'Non défini';
            const points = row.querySelector('.bonus-points').value || '0';

            resume += `<li><strong>${categorie}:</strong> ${valeur} - ${type} de ${points} points</li>`;
        });

        resume += '</ul>';
        resumeConfig.innerHTML = resume;
    }

    // Fonction utilitaire pour mettre à jour les index dans un template cloné
    function updateElementIndexes(element, placeholder, index) {
        const elements = element.querySelectorAll(`[name*="${placeholder}"]`);
        elements.forEach(el => {
            el.name = el.name.replace(placeholder, index);
        });
    }

    // Validation du formulaire
    document.getElementById('criteresForm').addEventListener('submit', function(e) {
        // Calculer la somme des poids pour information seulement
        let totalPoids = 0;
        document.querySelectorAll('.critere-poids').forEach(input => {
            totalPoids += parseFloat(input.value || 0);
        });

        // Afficher un message d'information sur la normalisation (sans bloquer la soumission)
        if (totalPoids > 0) {
            console.log(`Somme des poids avant normalisation: ${totalPoids.toFixed(1)}%. Ces valeurs seront normalisées automatiquement.`);
        } else {
            e.preventDefault();
            alert('Veuillez définir au moins un critère avec un poids supérieur à 0.');
        }
    });

    // Gestion de l'affichage des détails
    document.querySelectorAll('.view-critere').forEach(button => {
        button.addEventListener('click', function() {
            const critereId = this.getAttribute('data-id');

            // Appel AJAX pour récupérer les détails du critère
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
                    if (bonus.length > 0) {
                        bonus.forEach(bonusItem => {
                            const row = document.createElement('tr');

                            const categorieCell = document.createElement('td');
                            categorieCell.textContent = getCategorieLabel(bonusItem.categorie);

                            const valeurCell = document.createElement('td');
                            valeurCell.textContent = bonusItem.valeur;

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
                    } else {
                        bonusTableBody.innerHTML = '<tr><td colspan="4" class="text-center">Aucun bonus ou malus défini</td></tr>';
                    }
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des détails:', error);
                });
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

    // Fonction pour obtenir le libellé d'un type de bonus
    function getBonusTypeLabel(type) {
        const types = {
            'bonus': 'Bonus (Points supplémentaires)',
            'malus': 'Malus (Points retirés)',
            'multiplicateur': 'Multiplicateur (x%)'
        };

        return types[type] || type;
    }

    // Gestion des boutons d'édition et de suppression
    document.querySelectorAll('.edit-critere').forEach(button => {
        button.addEventListener('click', function() {
            const critereId = this.getAttribute('data-id');
            // Rediriger vers la page d'édition ou préremplir le formulaire ci-dessus
            // window.location.href = `/admin/criteres/${critereId}/edit`;

            // Ou pour préremplir le formulaire actuel (à adapter selon votre besoin)
            fetch(`/admin/criteres/${critereId}/details`)
                .then(response => response.json())
                .then(data => {
                    // Préremplir les champs du formulaire
                    document.getElementById('niveau_id').value = data.niveau_id;
                    document.getElementById('filiere_id').value = data.filiere_id;
                    document.getElementById('places_disponibles').value = data.filiere.places_disponibles;
                    // ... autres champs à remplir

                    // Faire défiler jusqu'au formulaire
                    document.getElementById('criteresForm').scrollIntoView({ behavior: 'smooth' });
                })
                .catch(error => {
                    console.error('Erreur lors de la récupération des détails:', error);
                });
        });
    });

    document.querySelectorAll('.delete-critere').forEach(button => {
        button.addEventListener('click', function() {
            const critereId = this.getAttribute('data-id');
            if (confirm('Êtes-vous sûr de vouloir supprimer ces critères de sélection ? Cette action est irréversible.')) {
                // Créer un formulaire de suppression
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/criteres/${critereId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                const method = document.createElement('input');
                method.type = 'hidden';
                method.name = '_method';
                method.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(method);
                document.body.appendChild(form);
                form.submit();
            }
        });
    });
});
</script>
@endsection
@endsection
