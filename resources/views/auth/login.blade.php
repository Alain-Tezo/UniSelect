@extends('layouts.app')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-5 col-md-7 col-sm-10">
            <div class="text-center mb-4">
                <h2 class="brand-title">Uni<span class="accent-text">Select</span></h2>
                <p class="text-muted">Portail d'administration</p>
            </div>
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header text-white text-center py-4">
                    <h3 class="mb-0">Connexion Administrateur</h3>
                    <p class="text-white-50 small mb-0 mt-2">Accédez à votre espace sécurisé</p>
                </div>
                <div class="card-body p-4 p-lg-5">
                    <form method="POST" action="{{ route('login') }}" class="needs-validation" novalidate>
                        @csrf
                        <div class="mb-4">
                            <label for="email" class="form-label fw-bold">Adresse Email</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-primary"></i></span>
                                <input id="email" type="email" class="form-control form-control-lg @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus placeholder="exemple@uniselect.com">
                                @error('email')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                            <div class="form-text small">Entrez l'email associé à votre compte</div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <label for="password" class="form-label fw-bold">Mot de passe</label>
                            </div>
                            <div class="input-group input-group-lg password-container">
                                <span class="input-group-text bg-light"><i class="fas fa-lock text-primary"></i></span>
                                <input id="password" type="password" class="form-control form-control-lg @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">
                                <button type="button" class="btn btn-light border toggle-password" tabindex="-1">
                                    <i class="fas fa-eye"></i>
                                </button>
                                @error('password')
                                <div class="invalid-feedback">
                                    <i class="fas fa-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                                @enderror
                            </div>
                        </div>
                        <div class="row mb-4 align-items-center">
                            <div class="col">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                    <label class="form-check-label" for="remember">
                                        Se souvenir de moi
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg shadow login-btn">
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
            <div class="text-center mt-4">
                <p class="text-muted small">
                    <i class="fas fa-lock-alt me-1 text-primary"></i> Connexion sécurisée | &copy; {{ date('Y') }} Uni<span class="accent-text">Select</span>
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Scripts pour la fonctionnalité de mot de passe -->
<script>
// Script exécuté immédiatement
(function() {
    // Fonctionnalité d'affichage du mot de passe
    var toggleBtn = document.querySelector('.toggle-password');
    var passwordField = document.querySelector('#password');
    
    if (toggleBtn && passwordField) {
        toggleBtn.addEventListener('click', function() {
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleBtn.querySelector('i').classList.remove('fa-eye');
                toggleBtn.querySelector('i').classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                toggleBtn.querySelector('i').classList.remove('fa-eye-slash');
                toggleBtn.querySelector('i').classList.add('fa-eye');
            }
        });
    }
    
    // Validation du formulaire
    var loginForm = document.querySelector('form');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            if (!loginForm.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            loginForm.classList.add('was-validated');
        });
    }
})();
</script>
@endsection

@section('styles')
<style>
    body {
        background: linear-gradient(135deg, #f5f7fa 0%, #e4e8f0 100%);
    }
    
    .brand-title {
        font-size: 2.5rem;
        font-weight: 700;
        color: #333;
        margin-bottom: 0;
    }
    
    .accent-text {
        color: #4361ee;
        font-weight: 700;
    }
    
    .card {
        overflow: hidden;
        transition: all 0.4s ease;
        border-radius: 12px;
    }
    
    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1) !important;
    }
    
    .card-header {
        background: linear-gradient(45deg, #4361ee, #3f37c9);
        border-bottom: none;
    }
    
    .form-control {
        border-radius: 0.5rem;
        border: 1px solid #e0e0e0;
        padding: 0.75rem 1rem;
        transition: all 0.3s;
    }
    
    .form-control:focus {
        box-shadow: 0 0 0 4px rgba(67, 97, 238, 0.1);
        border-color: #4361ee;
    }
    
    .input-group-text {
        border-top-left-radius: 0.5rem;
        border-bottom-left-radius: 0.5rem;
        border: 1px solid #e0e0e0;
        border-right: none;
    }
    
    .toggle-password {
        border-top-right-radius: 0.5rem;
        border-bottom-right-radius: 0.5rem;
        border-left: none;
        cursor: pointer;
    }
    
    .form-check-input:checked {
        background-color: #4361ee;
        border-color: #4361ee;
    }
    
    .login-btn {
        background: linear-gradient(45deg, #4361ee, #3f37c9);
        border: none;
        transition: all 0.3s ease;
        font-weight: 500;
        letter-spacing: 0.5px;
        padding: 12px 20px;
    }
    
    .login-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(67, 97, 238, 0.4) !important;
    }
    
    .back-link {
        color: #6c757d;
        font-weight: 500;
        transition: all 0.3s;
    }
    
    .back-link:hover {
        color: #4361ee;
    }
    
    /* Animations */
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(20px); }
        to { opacity: 1; transform: translateY(0); }
    }
    
    .card {
        animation: fadeIn 0.6s ease-out forwards;
    }
    
    /* Mobile responsiveness adjustments */
    @media (max-width: 576px) {
        .card-body {
            padding: 1.5rem !important;
        }
        
        .brand-title {
            font-size: 2rem;
        }
    }
</style>
@endsection

