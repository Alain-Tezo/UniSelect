@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-10 col-md-11 col-sm-12">
            <div class="card shadow-lg p-md-5 p-4 mb-5">
                <div class="card-body text-center">
                    <img src="{{ asset('img/uniselect-logo.svg') }}">
                    <h1 class="mb-4">Système de Sélection Universitaire</h1>
                    <p class="lead mb-5">Bienvenue sur l'application UniSelect. Veuillez sélectionner un profil pour continuer.</p>

                    <div class="row mt-4 g-4">
                        <div class="col-md-6">
                            <div class="card h-100 choice-card shadow-sm float-animation">
                                <div class="card-body text-center p-md-5 p-4">
                                    <div class="icon-container mb-3">
                                        <i class="fas fa-user-graduate icon-large fa-3x"></i>
                                    </div>
                                    <h3 class="mb-3">Étudiant</h3>
                                    <p class="mb-4 choice-description">Renseignez les informations d'un étudiant</p>
                                    <a href="{{ route('etudiant.form') }}" class="btn btn-lg btn-choice student-btn w-100">
                                        Accéder <i class="fas fa-arrow-right ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="card h-100 choice-card shadow-sm float-animation">
                                <div class="card-body text-center p-md-5 p-4">
                                    <div class="icon-container mb-3">
                                        <i class="fas fa-user-tie icon-large fa-3x"></i>
                                    </div>
                                    <h3 class="mb-3">Administrateur</h3>
                                    <p class="mb-4 choice-description">Gérez les sélections et les paramètres du système</p>
                                    <a href="{{ route('admin.login') }}" class="btn btn-lg btn-choice student-btn w-100">
                                        Connexion <i class="fas fa-lock ms-2"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
