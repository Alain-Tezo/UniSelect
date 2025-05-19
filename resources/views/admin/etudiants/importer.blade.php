@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4">
    <h1 class="mt-4 mb-4">Importer des étudiants</h1>

    <div class="card">
        <div class="card-header">
            <i class="fas fa-file-import me-1"></i>
            Importation via fichier Excel
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle me-2"></i>
                Utilisez cette fonctionnalité pour importer en masse des étudiants.
                Téléchargez d'abord un modèle, remplissez-le avec vos données, puis importez-le.
            </div>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Télécharger un modèle</div>
                        <div class="card-body">
                            <p>Téléchargez un modèle Excel préformaté pour l'importation :</p>
                            <div class="d-grid gap-2">
                                <a href="{{ route('admin.etudiants.template', 'l1') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> Modèle pour Licence 1
                                </a>
                                <a href="{{ route('admin.etudiants.template', 'l2plus') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-download me-1"></i> Modèle pour niveaux supérieurs
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">Importer un fichier</div>
                        <div class="card-body">
                            <form action="{{ route('admin.etudiants.importer.traiter') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label for="niveau_id" class="form-label">Niveau d'étude</label>
                                    <select class="form-select" id="niveau_id" name="niveau_id" required>
                                        <option value="">Sélectionnez le niveau</option>
                                        @foreach ($niveaux as $niveau)
                                            <option value="{{ $niveau->id }}">{{ $niveau->nom }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label for="fichier_excel" class="form-label">Fichier Excel à importer</label>
                                    <input type="file" class="form-control" id="fichier_excel" name="fichier_excel" required accept=".xlsx,.xls,.csv">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-upload me-1"></i> Importer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle me-2"></i>Important :</h5>
                <ul>
                    <li>Les colonnes du fichier doivent correspondre exactement au modèle fourni</li>
                    <li>Les emails doivent être uniques</li>
                    <li>Formats des données :
                        <ul>
                            <li>Date de naissance : AAAA-MM-JJ</li>
                            <li>Sexe : M ou F</li>
                            <li>Noms des filières : exactement comme dans le système</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
