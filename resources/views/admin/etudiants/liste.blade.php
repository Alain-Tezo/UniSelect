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
    <h1 class="mt-4 mb-4">Liste des étudiants</h1>

    @if(session('import_errors'))
    <div class="card mb-4">
        <div class="card-header bg-warning text-dark">
            <i class="fas fa-exclamation-triangle me-1"></i>
            Erreurs d'importation
        </div>
        <div class="card-body">
            <p>Les lignes suivantes n'ont pas pu être importées :</p>
            <ul>
                @foreach(session('import_errors') as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-filter me-1"></i>
                Filtres et actions
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.etudiants.importer') }}" class="btn btn-success">
                    <i class="fas fa-file-import me-1"></i> Importer des étudiants
                </a>
                <!-- Bouton pour réinitialiser la liste des étudiants -->
                <form action="{{ route('admin.etudiants.reinitialiser-liste') }}" method="POST" onsubmit="return confirm('ATTENTION: Cette action va supprimer TOUS les étudiants inscrits. Cette action est irréversible. Êtes-vous absolument sûr de vouloir continuer?')">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt me-1"></i> Réinitialiser tous les étudiants
                    </button>
                </form>
            </div>
        </div>
        <div class="card-body">
            <form id="filtreForm" action="{{ route('admin.etudiants') }}" method="GET">
                <div class="row g-3">
                    <div class="col-md-6 col-lg-3">
                        <label for="niveau_id" class="form-label">Niveau d'étude</label>
                        <select class="form-select" id="niveau_id" name="niveau_id">
                            <option value="">Tous les niveaux</option>
                            @foreach ($niveaux ?? [] as $niveau)
                                <option value="{{ $niveau->id }}" {{ request('niveau_id') == $niveau->id ? 'selected' : '' }}>
                                    {{ $niveau->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="filiere_id" class="form-label">Filière souhaitée</label>
                        <select class="form-select" id="filiere_id" name="filiere_id">
                            <option value="">Toutes les filières</option>
                            @foreach ($filieres ?? [] as $filiere)
                                <option value="{{ $filiere->id }}" {{ request('filiere_id') == $filiere->id ? 'selected' : '' }}>
                                    {{ $filiere->nom }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3">
                        <label for="est_selectionne" class="form-label">Statut</label>
                        <select class="form-select" id="est_selectionne" name="est_selectionne">
                            <option value="">Tous les statuts</option>
                            <option value="1" {{ request('est_selectionne') == '1' ? 'selected' : '' }}>Sélectionnés</option>
                            <option value="0" {{ request('est_selectionne') == '0' ? 'selected' : '' }}>Non sélectionnés</option>
                        </select>
                    </div>
                    <div class="col-md-6 col-lg-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-search me-2"></i> Filtrer
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-table me-1"></i>
            Liste des étudiants inscrits
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm" id="etudiantsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th style="min-width: 100px;">Nom</th>
                            <th style="min-width: 100px;">Prénom</th>
                            <th style="min-width: 180px;">Email</th>
                            <th style="min-width: 100px;">Niveau</th>
                            <th style="min-width: 120px;">Premier choix</th>
                            <th style="min-width: 120px;">Deuxième choix</th>
                            <th style="min-width: 120px;">Troisième choix</th>
                            <th style="min-width: 150px;">Statut</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
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
                            <td>{{ $etudiant->premierChoix->nom }}</td>
                            <td>{{ $etudiant->deuxiemeChoix->nom ?? 'Non spécifié' }}</td>
                            <td>{{ $etudiant->troisiemeChoix->nom ?? 'Non spécifié' }}</td>
                            <td>
                                @if($etudiant->est_selectionne)
                                    <span class="badge bg-success">Sélectionné - {{ $etudiant->filiereSelectionnee->nom ?? 'N/A' }}</span>
                                @else
                                    <span class="badge bg-secondary">Non sélectionné</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1 justify-content-center">
                                    <button type="button" class="btn btn-sm btn-info view-etudiant"
                                            data-id="{{ $etudiant->id }}"
                                            data-bs-toggle="modal"
                                            data-bs-target="#detailEtudiantModal">
                                        <i class="fas fa-eye"></i><span class="d-none d-md-inline ms-1">Détails</span>
                                    </button>

                                    <!-- Bouton pour supprimer un étudiant -->
                                    <form action="{{ route('admin.etudiants.supprimer', ['id' => $etudiant->id]) }}" method="POST"
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer définitivement cet étudiant?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
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
                            <p><strong>Statut:</strong> <span id="detail-statut"></span></p>
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

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser la table
        const dataTable = new simpleDatatables.DataTable('#etudiantsTable');

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

                        // Statut
                        let statutElement = document.getElementById('detail-statut');
                        if (data.est_selectionne) {
                            statutElement.innerHTML = '<span class="badge bg-success">Sélectionné - ' + (data.filiere_selectionnee ? data.filiere_selectionnee.nom : 'N/A') + '</span>';
                        } else {
                            statutElement.innerHTML = '<span class="badge bg-secondary">Non sélectionné</span>';
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
