@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-10">
            <div class="card shadow-lg border-0 rounded-lg mt-4 mb-4">
                <div class="card-header text-white text-center py-4">
                    <h3 class="mb-0">Connexion Administrateur</h3>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label fw-bold">Adresse Email</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="exemple@uniselect.com">
                            </div>
                            @error('email')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4">
                            <label for="password" class="form-label fw-bold">Mot de passe</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                            </div>
                            @error('password')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                        <div class="mb-4 form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label" for="remember">
                                Se souvenir de moi
                            </label>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-lg text-white shadow login-btn">
                                <i class="fas fa-sign-in-alt me-2"></i> Connexion
                            </button>
                        </div>
                    </form>
                </div>
                <div class="card-footer p-3 text-center">
                    <a href="{{ url('/') }}" class="text-decoration-none back-link">
                        <i class="fas fa-arrow-left me-2"></i>Retour à l'accueil
                    </a>
                </div>
            </div>
            <div class="text-center mt-3 mb-4">
                <p class="text-muted small">Connexion sécurisée | Uni<span class="accent-text">Select</span></p>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    body {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1) !important;
    }

    .form-control:focus {
        box-shadow: none;
        border-color: #ced4da;
    }

    .btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(67, 97, 238, 0.4) !important;
    }
</style>
@endsection
