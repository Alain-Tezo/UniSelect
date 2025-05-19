@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Gestion des administrateurs</h1>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <i class="fas fa-user-shield me-1"></i>
                Liste des administrateurs
            </div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#ajouterAdminModal">
                <i class="fas fa-plus me-1"></i> Ajouter un administrateur
            </button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="adminsTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Nom</th>
                            <th>Email</th>
                            <th>Date de création</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($admins as $admin)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>{{ $admin->name }}</td>
                            <td>{{ $admin->email }}</td>
                            <td>{{ $admin->created_at->format('d/m/Y H:i') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info edit-admin"
                                        data-id="{{ $admin->id }}"
                                        data-name="{{ $admin->name }}"
                                        data-email="{{ $admin->email }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#modifierAdminModal">
                                    <i class="fas fa-edit"></i> Modifier
                                </button>
                                @if($admin->role === 'admin')
                                <button type="button" class="btn btn-sm btn-success manage-permissions"
                                        data-id="{{ $admin->id }}"
                                        data-name="{{ $admin->name }}"
                                        data-bs-toggle="modal"
                                        data-bs-target="#permissionsModal">
                                    <i class="fas fa-key"></i> Gérer les accès
                                </button>
                                <button type="button" class="btn btn-sm btn-danger delete-admin"
                                        data-id="{{ $admin->id }}"
                                        data-name="{{ $admin->name }}">
                                    <i class="fas fa-trash"></i> Supprimer
                                </button>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Ajouter Admin -->
<div class="modal fade" id="ajouterAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.admins.store') }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Ajouter un administrateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Mot de passe</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Confirmer le mot de passe</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Modifier Admin -->
<div class="modal fade" id="modifierAdminModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editAdminForm" method="POST" action="">
                @csrf
                @method('PUT')
                <div class="modal-header">
                    <h5 class="modal-title">Modifier un administrateur</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nom</label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_password" class="form-label">Nouveau mot de passe (laisser vide pour ne pas changer)</label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                    </div>
                    <div class="mb-3">
                        <label for="edit_password_confirmation" class="form-label">Confirmer le nouveau mot de passe</label>
                        <input type="password" class="form-control" id="edit_password_confirmation" name="password_confirmation">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Mettre à jour</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Formulaire de suppression (caché) -->
<form id="deleteAdminForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<!-- Modal Permissions Filières -->
<div class="modal fade" id="permissionsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="permissionsForm" method="POST" action="{{ route('admin.admins.permissions') }}">
                @csrf
                <input type="hidden" id="admin_id" name="admin_id" value="">
                <div class="modal-header">
                    <h5 class="modal-title">Gestion des accès pour <span id="admin_name_display"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i> Sélectionnez les filières que cet administrateur pourra gérer. Seules les filières sélectionnées seront accessibles pour cet administrateur.
                    </p>
                    <div id="filieres-loading" class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Chargement...</span>
                        </div>
                        <p>Chargement des filières...</p>
                    </div>
                    <div id="filieres-container" class="row g-3" style="display: none;">
                        <!-- Les filières seront injectées ici via JavaScript -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer les permissions</button>
                </div>
            </form>
        </div>
    </div>
</div>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser la table
        const dataTable = new simpleDatatables.DataTable('#adminsTable');

        // Gestion de la modification d'admin
        document.querySelectorAll('.edit-admin').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                const email = this.getAttribute('data-email');

                // Remplir le formulaire
                document.getElementById('edit_name').value = name;
                document.getElementById('edit_email').value = email;
                document.getElementById('edit_password').value = '';
                document.getElementById('edit_password_confirmation').value = '';

                // Mettre à jour l'action du formulaire
                document.getElementById('editAdminForm').action = `/admin/admins/${id}`;
            });
        });

        // Gestion des permissions d'admin
        document.querySelectorAll('.manage-permissions').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');
                
                // Mettre à jour le formulaire
                document.getElementById('admin_id').value = id;
                document.getElementById('admin_name_display').textContent = name;
                
                // Afficher le spinner de chargement
                document.getElementById('filieres-loading').style.display = 'block';
                document.getElementById('filieres-container').style.display = 'none';
                
                // Charger les filières avec les autorisations actuelles
                fetch(`/admin/admins/${id}/permissions`)
                    .then(response => response.json())
                    .then(data => {
                        // Cacher le spinner de chargement
                        document.getElementById('filieres-loading').style.display = 'none';
                        
                        // Générer les cases à cocher pour chaque filière
                        const container = document.getElementById('filieres-container');
                        container.innerHTML = '';
                        
                        data.filieres.forEach(filiere => {
                            const checked = data.permissions.includes(filiere.id) ? 'checked' : '';
                            const html = `
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" 
                                               name="filieres[]" value="${filiere.id}" id="filiere_${filiere.id}" ${checked}>
                                        <label class="form-check-label" for="filiere_${filiere.id}">
                                            ${filiere.nom}
                                        </label>
                                    </div>
                                </div>
                            `;
                            container.innerHTML += html;
                        });
                        
                        // Afficher le conteneur des filières
                        container.style.display = 'flex';
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des filières:', error);
                        document.getElementById('filieres-loading').innerHTML = `
                            <div class="alert alert-danger">
                                Une erreur est survenue lors du chargement des filières. Veuillez réessayer.
                            </div>
                        `;
                    });
            });
        });

        // Gestion de la suppression d'admin
        document.querySelectorAll('.delete-admin').forEach(button => {
            button.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const name = this.getAttribute('data-name');

                if (confirm(`Êtes-vous sûr de vouloir supprimer l'administrateur ${name} ?`)) {
                    const form = document.getElementById('deleteAdminForm');
                    form.action = `/admin/admins/${id}`;
                    form.submit();
                }
            });
        });
    });
</script>
@endsection
@endsection
