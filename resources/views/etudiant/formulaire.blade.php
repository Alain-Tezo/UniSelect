<!-- resources/views/etudiant/form.blade.php -->
@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-11">
        <!-- Bouton de retour à l'accueil -->
        <div class="mb-4">
            <a href="{{ url('/') }}" class="btn btn-outline-light btn-back">
                <i class="fas fa-arrow-left me-2"></i>Retour à l'accueil
            </a>
        </div>
        <div class="card border-0 shadow-lg">
            <div class="card-header text-white p-4" style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);">
                <h3 class="mb-0 fw-bold">Formulaire d'inscription</h3>
                <p class="mb-0 opacity-75">Remplissez ce formulaire pour soumettre votre candidature</p>
            </div>

            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger rounded-3 border-0 shadow-sm">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-exclamation-circle me-3 fa-2x"></i>
                            <div>
                                <h5 class="alert-heading mb-1">Erreurs de validation</h5>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <form method="POST" action="{{ route('etudiant.store') }}" id="formInscription">
                    @csrf

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <select class="form-select" id="niveau_id" name="niveau_id" required>
                                    <option value="">Sélectionnez</option>
                                    @foreach ($niveaux as $niveau)
                                        <option value="{{ $niveau->id }}" {{ old('niveau_id') == $niveau->id ? 'selected' : '' }}>
                                            {{ $niveau->nom }}
                                        </option>
                                    @endforeach
                                </select>
                                <label for="niveau_id">Niveau d'étude</label>
                            </div>
                        </div>
                    </div>

                    <!-- Section Informations personnelles -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <h4 class="fw-bold text-primary">
                                <i class="fas fa-user-circle me-2"></i>Informations personnelles
                            </h4>
                            <hr>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="nom" name="nom" value="{{ old('nom') }}" required>
                                    <label for="nom">Nom</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <input type="text" class="form-control" id="prenom" name="prenom" value="{{ old('prenom') }}" required>
                                    <label for="prenom">Prénom</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="date" class="form-control" id="date_naissance" name="date_naissance" value="{{ old('date_naissance') }}" required>
                                    <label for="date_naissance">Date de naissance</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required>
                                    <label for="email">Email</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" id="sexe" name="sexe" required>
                                        <option value="">Sélectionnez</option>
                                        <option value="M" {{ old('sexe') == 'M' ? 'selected' : '' }}>Masculin</option>
                                        <option value="F" {{ old('sexe') == 'F' ? 'selected' : '' }}>Féminin</option>
                                    </select>
                                    <label for="sexe">Sexe</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select" id="region_origine" name="region_origine" required>
                                        <option value="">Sélectionnez</option>
                                        <option value="nord" {{ old('region_origine') == 'nord' ? 'selected' : '' }}>Nord</option>
                                        <option value="sud" {{ old('region_origine') == 'sud' ? 'selected' : '' }}>Sud</option>
                                        <option value="est" {{ old('region_origine') == 'est' ? 'selected' : '' }}>Est</option>
                                        <option value="ouest" {{ old('region_origine') == 'ouest' ? 'selected' : '' }}>Ouest</option>
                                        <option value="centre" {{ old('region_origine') == 'centre' ? 'selected' : '' }}>Centre</option>
                                        <option value="Adamaoua" {{ old('region_origine') == 'Adamaoua' ? 'selected' : '' }}>Adamaoua</option>
                                        <option value="littoral" {{ old('region_origine') == 'littoral' ? 'selected' : '' }}>Littoral</option>
                                        <option value="extrême nord" {{ old('region_origine') == 'extrême nord' ? 'selected' : '' }}>Extrême Nord</option>
                                        <option value="nord-ouest" {{ old('region_origine') == 'nord-ouest' ? 'selected' : '' }}>Nord-Ouest</option>
                                        <option value="sud-ouest" {{ old('region_origine') == 'sud-ouest' ? 'selected' : '' }}>Sud-Ouest</option>
                                    </select>
                                    <label for="region_origine">Région d'origine</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Champs spécifiques pour Licence 1 -->
                    <div id="form-licence1" class="form-niveau-specific">
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h4 class="fw-bold text-primary">
                                    <i class="fas fa-graduation-cap me-2"></i>Informations académiques (Baccalauréat)
                                </h4>
                                <hr>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="etablissement_precedent" name="etablissement_precedent" value="{{ old('etablissement_precedent') }}">
                                        <label for="etablissement_precedent">Établissement fréquenté</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="serie_bac" name="serie_bac">
                                            <option value="">Sélectionnez</option>
                                            <option value="A" {{ old('serie_bac') == 'A' ? 'selected' : '' }}>A</option>
                                            <option value="C" {{ old('serie_bac') == 'C' ? 'selected' : '' }}>C</option>
                                            <option value="D" {{ old('serie_bac') == 'D' ? 'selected' : '' }}>D</option>
                                            <option value="TI" {{ old('serie_bac') == 'TI' ? 'selected' : '' }}>TI</option>
                                        </select>
                                        <label for="serie_bac">Série au Baccalauréat</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0" max="20" class="form-control" id="moyenne_bac" name="moyenne_bac" value="{{ old('moyenne_bac') }}">
                                        <label for="moyenne_bac">Moyenne au Baccalauréat</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0" max="20" class="form-control" id="note_math" name="note_math" value="{{ old('note_math') }}">
                                        <label for="note_math">Note en Mathématiques</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0" max="20" class="form-control" id="note_physique" name="note_physique" value="{{ old('note_physique') }}">
                                        <label for="note_physique">Note en Physique</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0" max="20" class="form-control" id="note_svteehb" name="note_svteehb" value="{{ old('note_svteehb') }}">
                                        <label for="note_svteehb">Note en SVTEEHB</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0" max="20" class="form-control" id="note_informatique" name="note_informatique" value="{{ old('note_informatique') }}">
                                        <label for="note_informatique">Note en Informatique</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Champs spécifiques pour Licence 2 et plus -->
                    <div id="form-niveau-superieur" class="form-niveau-specific">
                        <div class="form-section mb-4">
                            <div class="section-header mb-3">
                                <h4 class="fw-bold text-primary">
                                    <i class="fas fa-school me-2"></i>Informations académiques (Parcours précédent)
                                </h4>
                                <hr>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="text" class="form-control" id="universite_precedente" name="universite_precedente" value="{{ old('universite_precedente') }}">
                                        <label for="universite_precedente">Université précédente</label>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <select class="form-select" id="filiere_precedente_id" name="filiere_precedente_id">
                                            <option value="">Sélectionnez</option>
                                            @foreach ($filieres as $filiere)
                                                <option value="{{ $filiere->id }}" {{ old('filiere_precedente_id') == $filiere->id ? 'selected' : '' }}>
                                                    {{ $filiere->nom }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label for="filiere_precedente_id">Filière précédente</label>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="form-floating">
                                        <input type="number" step="0.01" min="0" max="4" class="form-control" id="mgp" name="mgp" value="{{ old('mgp') }}">
                                        <label for="mgp">Moyenne Générale Pondérée (MGP)</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Choix de filières -->
                    <div class="form-section mb-4">
                        <div class="section-header mb-3">
                            <h4 class="fw-bold text-primary">
                                <i class="fas fa-list-check me-2"></i>Choix de filières
                            </h4>
                            <hr>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" id="premier_choix_id" name="premier_choix_id" required>
                                        <option value="">Sélectionnez</option>
                                        @foreach ($filieres as $filiere)
                                            <option value="{{ $filiere->id }}" {{ old('premier_choix_id') == $filiere->id ? 'selected' : '' }}>
                                                {{ $filiere->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="premier_choix_id">Premier choix</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" id="deuxieme_choix_id" name="deuxieme_choix_id" required>
                                        <option value="">Sélectionnez</option>
                                        @foreach ($filieres as $filiere)
                                            <option value="{{ $filiere->id }}" {{ old('deuxieme_choix_id') == $filiere->id ? 'selected' : '' }}>
                                                {{ $filiere->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="deuxieme_choix_id">Deuxième choix</label>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-floating">
                                    <select class="form-select" id="troisieme_choix_id" name="troisieme_choix_id" required>
                                        <option value="">Sélectionnez</option>
                                        @foreach ($filieres as $filiere)
                                            <option value="{{ $filiere->id }}" {{ old('troisieme_choix_id') == $filiere->id ? 'selected' : '' }}>
                                                {{ $filiere->nom }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <label for="troisieme_choix_id">Troisième choix</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bouton d'action -->
                    <div class="d-flex align-items-center justify-content-between mt-4">
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary px-4 py-2">
                            <i class="fas fa-times me-2"></i>Annuler
                        </a>

                        <button type="submit" class="btn btn-lg text-white px-5 py-3" style="background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%); box-shadow: 0 5px 15px rgba(67, 97, 238, 0.3); transition: all 0.3s ease;">
                            <i class="fas fa-paper-plane me-2"></i>Soumettre ma candidature
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
    .form-section {
        background-color: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .form-floating > .form-control,
    .form-floating > .form-select {
        height: calc(3.5rem + 2px);
        line-height: 1.25;
    }

    .form-floating > label {
        padding: 1rem 0.75rem;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #4361ee;
        box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
    }

    hr {
        background: linear-gradient(to right, #4361ee, #4cc9f0);
        height: 3px;
        opacity: 1;
    }

    .text-primary {
        color: #4361ee !important;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4) !important;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const niveauSelect = document.getElementById('niveau_id');
        const formL1 = document.getElementById('form-licence1');
        const formSuperieur = document.getElementById('form-niveau-superieur');

        // Fonction pour afficher le formulaire approprié selon le niveau
        function toggleFormFields() {
            const niveauId = parseInt(niveauSelect.value);

            // Cache tous les formulaires spécifiques
            document.querySelectorAll('.form-niveau-specific').forEach(form => {
                form.style.display = 'none';
            });

            // Affiche le formulaire approprié
            if (niveauId === 1) { // Licence 1
                formL1.style.display = 'block';
                formSuperieur.style.display = 'none';
            } else if (niveauId >= 2) { // Licence 2 et supérieur
                formL1.style.display = 'none';
                formSuperieur.style.display = 'block';
            }
        }

        // Initialisation
        toggleFormFields();

        // Événement de changement de niveau
        niveauSelect.addEventListener('change', toggleFormFields);
    });
</script>
@endsection
