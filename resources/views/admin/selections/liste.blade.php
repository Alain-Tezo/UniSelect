@extends('layouts.admin')

@section('styles')
<style>
    /* Styles personnalisés pour la pagination */
    .pagination {
        display: flex;
        justify-content: center;
        margin-top: 1.5rem;
        margin-bottom: 1.5rem;
    }

    .pagination .page-item {
        margin: 0 3px;
    }

    .pagination .page-link {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        padding: 0;
        font-size: 0.9rem;
        color: #495057;
        background-color: #fff;
        border: 1px solid #dee2e6;
        border-radius: 50%;
        transition: all 0.2s ease-in-out;
    }

    .pagination .page-item.active .page-link {
        color: #fff;
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
    }

    .pagination .page-item.disabled .page-link {
        color: #adb5bd;
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }

    .pagination .page-link:hover {
        color: #fff;
        background-color: var(--accent-color);
        border-color: var(--accent-color);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    .pagination .page-item:first-child .page-link,
    .pagination .page-item:last-child .page-link {
        background-color: #f1f3f5;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Liste des étudiants sélectionnés</h1>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-filter me-1"></i>
            Filtres
        </div>
        <div class="card-body">
            <form id="filtreForm" action="{{ route('admin.selections') }}" method="GET">
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

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-table me-1"></i>
                Étudiants sélectionnés
            </div>
            <div>
                <button type="button" class="btn btn-success me-2" id="notifierBtn">
                    <i class="fas fa-envelope me-1"></i> Notifier par email
                </button>
                <button type="button" class="btn btn-warning me-2" id="exportPdfBtn">
                    <i class="fas fa-file-pdf me-1"></i> Exporter en PDF
                </button>
                <button type="button" class="btn btn-primary me-2" id="reinitialiserBtn">
                    <i class="fas fa-undo me-1"></i> Mettre à jour la sélection
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="etudiantsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Nom</th>
                            <th>Prénom</th>
                            <th>Email</th>
                            <th>Niveau</th>
                            <th>Filière attribuée</th>
                            <th>Points</th>
                            <th>Notification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($etudiants as $etudiant)
                        <tr>
                            <td class="text-center">{{ ($etudiants->currentPage() - 1) * $etudiants->perPage() + $loop->iteration }}</td>
                            <td>{{ $etudiant->nom }}</td>
                            <td>{{ $etudiant->prenom }}</td>
                            <td>{{ $etudiant->email }}</td>
                            <td>{{ $etudiant->niveau->nom }}</td>
                            <td>
                                {{ $etudiant->filiereSelectionnee->nom }}
                                @if($etudiant->filiereSelectionnee->est_selective)
                                    <span class="badge bg-primary ms-1">Sélective</span>
                                @else
                                    <span class="badge bg-secondary ms-1">Non-sélective</span>
                                @endif
                            </td>
                            <td>
                                @if($etudiant->filiereSelectionnee->est_selective)
                                    {{ number_format($etudiant->points_selection, 2) }}
                                @else
                                    <span class="text-muted">Admission directe</span>
                                @endif
                            </td>
                            <td>
                                @if($etudiant->notification_envoyee)
                                    <span class="badge bg-success">Envoyée</span>
                                @else
                                    <span class="badge bg-warning">En attente</span>
                                @endif
                            </td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info view-etudiant"
                                        data-id="{{ $etudiant->id }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#detailEtudiantModal">
                                    <i class="fas fa-eye"></i> Détails
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-3 d-flex justify-content-center">
                {{ $etudiants->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Détails Etudiant -->
<div class="modal fade" id="detailEtudiantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Détails de l'étudiant</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="etudiantDetails">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nom:</strong> <span id="detail-nom"></span></p>
                            <p><strong>Prénom:</strong> <span id="detail-prenom"></span></p>
                            <p><strong>Email:</strong> <span id="detail-email"></span></p>
                            <p><strong>Date de naissance:</strong> <span id="detail-date-naissance"></span></p>
                            <p><strong>Sexe:</strong> <span id="detail-sexe"></span></p>
                            <p><strong>Région d'origine:</strong> <span id="detail-region"></span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Niveau:</strong> <span id="detail-niveau"></span></p>
                            <p><strong>Premier choix:</strong> <span id="detail-premier-choix"></span></p>
                            <p><strong>Deuxième choix:</strong> <span id="detail-deuxieme-choix"></span></p>
                            <p><strong>Troisième choix:</strong> <span id="detail-troisieme-choix"></span></p>
                            <p><strong>Filière attribuée:</strong> <span id="detail-filiere-attribuee"></span></p>
                            <p><strong>Points:</strong> <span id="detail-points"></span></p>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6>Informations académiques</h6>
                            <div id="details-academiques"></div>
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

<!-- Formulaires cachés pour les actions -->
<form id="notifierForm" action="{{ route('admin.selections.notifier') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="niveau_id" id="notifier_niveau_id">
    <input type="hidden" name="filiere_id" id="notifier_filiere_id">
</form>

<form id="reinitialiserForm" action="{{ route('admin.selections.reinitialiser') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="niveau_id" id="reinitialiser_niveau_id">
    <input type="hidden" name="filiere_id" id="reinitialiser_filiere_id">
</form>

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/jspdf@2.5.1/dist/jspdf.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jspdf-autotable@3.5.28/dist/jspdf.plugin.autotable.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser la table
        const dataTable = new simpleDatatables.DataTable('#etudiantsTable');

        // Bouton notifier
        document.getElementById('notifierBtn').addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir envoyer des notifications aux étudiants sélectionnés ?')) {
                const form = document.getElementById('notifierForm');
                document.getElementById('notifier_niveau_id').value = document.getElementById('niveau_id').value;
                document.getElementById('notifier_filiere_id').value = document.getElementById('filiere_id').value;
                form.submit();
            }
        });

        // Bouton réinitialiser
        document.getElementById('reinitialiserBtn').addEventListener('click', function() {
            if (confirm('Êtes-vous sûr de vouloir réinitialiser cette sélection ? Cette action est irréversible.')) {
                const form = document.getElementById('reinitialiserForm');
                document.getElementById('reinitialiser_niveau_id').value = document.getElementById('niveau_id').value;
                document.getElementById('reinitialiser_filiere_id').value = document.getElementById('filiere_id').value;
                form.submit();
            }
        });

        // Bouton exporter PDF
        document.getElementById('exportPdfBtn').addEventListener('click', function() {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF();

            // Titre
            doc.setFontSize(18);
            doc.text('Liste des étudiants sélectionnés', 14, 22);

            // Filtres
            const niveau = document.getElementById('niveau_id').options[document.getElementById('niveau_id').selectedIndex].text;
            const filiere = document.getElementById('filiere_id').options[document.getElementById('filiere_id').selectedIndex].text;
            doc.setFontSize(12);
            doc.text(`Filtres: Niveau - ${niveau}, Filière - ${filiere}`, 14, 30);
            doc.text(`Généré le ${new Date().toLocaleDateString()}`, 14, 36);

            // Informations sur l'administrateur
            @if(isset($selections) && $selections->isNotEmpty() && $selections->first()->createur)
            doc.text(`Généré par ${@json($selections->first()->createur->name)}`, 14, 42);
            @else
            doc.text(`Export PDF par ${@json($adminActuel->name)}`, 14, 42);
            @endif

            // Tableau
            doc.autoTable({
                html: '#etudiantsTable',
                startY: 48, // Augmenté pour tenir compte du texte supplémentaire
                theme: 'striped',
                headStyles: { fillColor: [75, 115, 223] },
                columns: [0, 1, 2, 3, 4, 5, 6, 7],
            });

            // Sauvegarde
            doc.save('etudiants-selectionnes.pdf');
        });

        // Affichage des détails d'un étudiant
        document.querySelectorAll('.view-etudiant').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');

                fetch(`/admin/etudiants/${id}`)
                    .then(response => response.json())
                    .then(data => {
                        // Informations de base
                        document.getElementById('detail-nom').textContent = data.nom;
                        document.getElementById('detail-prenom').textContent = data.prenom;
                        document.getElementById('detail-email').textContent = data.email;
                        document.getElementById('detail-date-naissance').textContent = new Date(data.date_naissance).toLocaleDateString();
                        document.getElementById('detail-sexe').textContent = data.sexe === 'M' ? 'Masculin' : 'Féminin';
                        document.getElementById('detail-region').textContent = data.region_origine;

                        // Informations sur la sélection
                        document.getElementById('detail-niveau').textContent = data.niveau.nom;
                        document.getElementById('detail-premier-choix').textContent = data.premier_choix.nom;
                        document.getElementById('detail-deuxieme-choix').textContent = data.deuxieme_choix.nom;
                        document.getElementById('detail-troisieme-choix').textContent = data.troisieme_choix.nom;

                        // Afficher si la filière est sélective
                        const filiereAttribueeText = `${data.filiere_selectionnee.nom} `;
                        const selectiveBadge = data.filiere_selectionnee.est_selective
                            ? '<span class="badge bg-primary">Sélective</span>'
                            : '<span class="badge bg-secondary">Non-sélective</span>';
                        document.getElementById('detail-filiere-attribuee').innerHTML = filiereAttribueeText + selectiveBadge;

                        // Gestion du score avec plus de détails
                        const scoreText = `${data.points_selection.toFixed(2)}`;
                        // Ajouter des détails si disponibles
                        if (data.details_selection) {
                            try {
                                const details = JSON.parse(data.details_selection);
                                document.getElementById('detail-points').innerHTML = `
                                    <span>${scoreText}</span>
                                    <button class="btn btn-sm btn-outline-info ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#scoreDetailsCollapse">
                                        Détails
                                    </button>
                                    <div class="collapse mt-2" id="scoreDetailsCollapse">
                                        <div class="card card-body">
                                            <h6>Détails du score</h6>
                                            <small class="text-muted">Le score peut inclure des bonus pour dépasser les critères minimums</small>
                                        </div>
                                    </div>
                                `;
                            } catch (e) {
                                document.getElementById('detail-points').textContent = scoreText;
                            }
                        } else {
                            document.getElementById('detail-points').textContent = scoreText;
                        }

                        // Informations académiques spécifiques au niveau
                        let detailsAcademiques = '<ul>';

                        if (data.niveau_id == 1) { // Licence 1
                            detailsAcademiques += `<li><strong>Établissement précédent:</strong> ${data.etablissement_precedent}</li>`;
                            detailsAcademiques += `<li><strong>Série Bac:</strong> ${data.serie_bac}</li>`;
                            detailsAcademiques += `<li><strong>Moyenne Bac:</strong> ${data.moyenne_bac}</li>`;
                            detailsAcademiques += `<li><strong>Note Math:</strong> ${data.note_math}</li>`;
                            detailsAcademiques += `<li><strong>Note Physique:</strong> ${data.note_physique}</li>`;
                            detailsAcademiques += `<li><strong>Note SVTEEHB:</strong> ${data.note_svteehb}</li>`;
                            detailsAcademiques += `<li><strong>Note Informatique:</strong> ${data.note_informatique}</li>`;
                        } else { // Autres niveaux
                            detailsAcademiques += `<li><strong>Établissement précédent:</strong> ${data.etablissement_precedent}</li>`;
                            detailsAcademiques += `<li><strong>Filière précédente:</strong> ${data.filiere_precedente ? data.filiere_precedente.nom : 'N/A'}</li>`;
                            detailsAcademiques += `<li><strong>MGP:</strong> ${data.mgp}</li>`;
                        }

                        detailsAcademiques += '</ul>';
                        document.getElementById('details-academiques').innerHTML = detailsAcademiques;
                    })
                    .catch(error => {
                        console.error('Erreur lors de la récupération des détails:', error);
                    });
            });
        });
    });
</script>
@endsection
@endsection
