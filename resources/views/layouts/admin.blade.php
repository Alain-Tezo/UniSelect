<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>UniSelect - @yield('title', 'Administration')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/uniselect-logo.svg') }}">

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            /* Palette de couleurs unique */
            --primary-color: #3730A3;       /* Bleu indigo profond - couleur principale */
            --secondary-color: #E97451;     /* Orange terracotta - couleur secondaire/accent */
            --tertiary-color: #64A6BD;      /* Bleu ciel - pour éléments tertiaires */
            --success-color: #2D6A4F;       /* Vert foncé - pour succès/validations */
            --warning-color: #FF9F1C;       /* Orange vif - pour alertes */
            --danger-color: #E63946;        /* Rouge clair - pour erreurs */
            --dark-color: #2D3748;          /* Gris anthracite - texte principal */
            --light-color: #F8F7F4;         /* Blanc cassé - fond principal */
            --muted-color: #94A3B8;         /* Gris moyen - texte secondaire */

            /* Dimensions */
            --header-height: 70px;          /* En-tête plus grande pour plus d'espace */
            --navbar-height: 60px;          /* Barre de navigation plus confortable */
            --border-radius: 12px;          /* Coins arrondis pour cartes et boutons */
            --transition-speed: 0.3s;       /* Vitesse des animations */
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: var(--light-color);
            color: var(--dark-color);
            padding-top: calc(var(--header-height) + var(--navbar-height));
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            line-height: 1.6;
            letter-spacing: 0.01em;
        }

        /* Header */
        .main-header {
            height: var(--header-height);
            background: linear-gradient(135deg, var(--primary-color) 0%, #2E279D 100%);
            color: #fff;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            display: flex;
            align-items: center;
            padding: 0 30px;
            justify-content: space-between;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .brand-logo {
            display: flex;
            align-items: center;
        }

        .brand-logo img {
            height: 40px;
            margin-right: 15px;
        }

        /* Style pour le favicon dans l'onglet du navigateur */
        .favicon-link {
            letter-spacing: 1px;
            font-size: 20px;
        }

        .brand-text span {
            color: #4cc9f0;
            font-style: italic;
        }

        /* User Menu */
        .user-menu {
            display: flex;
            align-items: center;
        }

        .user-info {
            margin-right: 10px;
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 14px;
            color: #fff;
        }

        .user-role {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.7);
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            cursor: pointer;
        }

        .img-upload-preview {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
            margin: 0 auto;
            display: block;
            border: 3px solid var(--primary-color);
        }

        .profile-table th {
            width: 30%;
            background-color: rgba(67, 97, 238, 0.05);
        }

        .avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        /* Navbar */
        .main-navbar {
            height: var(--navbar-height);
            background-color: #fff;
            position: fixed;
            top: var(--header-height);
            left: 0;
            right: 0;
            z-index: 1020;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }

        .nav-container {
            height: 100%;
            display: flex;
            align-items: center;
        }

        .nav-menu {
            display: flex;
            padding: 0;
            margin: 0;
            list-style: none;
            height: 100%;
        }

        .nav-item {
            height: 100%;
            position: relative;
        }

        .nav-link {
            height: 100%;
            display: flex;
            align-items: center;
            padding: 0 15px;
            color: #495057;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
            font-size: 14px;
        }

        .nav-link:hover {
            color: var(--primary-color);
        }

        .nav-link.active {
            color: var(--primary-color);
            background-color: rgba(67, 97, 238, 0.05);
            border-bottom: 2px solid var(--primary-color);
        }

        .nav-link i {
            margin-right: 8px;
        }

        /* Dropdown Menus */
        .nav-dropdown {
            position: relative;
        }

        .nav-dropdown-menu {
            position: absolute;
            top: 100%;
            left: 0;
            min-width: 220px;
            background-color: #fff;
            border-radius: 5px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s;
            z-index: 1020;
            padding: 10px 0;
            list-style: none;
            margin: 0;
        }

        .nav-dropdown:hover .nav-dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        .dropdown-item {
            display: flex;
            align-items: center;
            padding: 8px 15px;
            font-size: 14px;
            color: #495057;
            text-decoration: none;
            transition: all 0.2s;
        }

        .dropdown-item:hover {
            background-color: rgba(67, 97, 238, 0.05);
            color: var(--primary-color);
        }

        .dropdown-item i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex-grow: 1;
            padding: 20px;
        }

        /* Cards */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #fff;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            font-weight: 600;
        }

        /* Footer */
        .main-footer {
            background-color: #fff;
            padding: 15px 0;
            text-align: center;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            margin-top: auto;
        }

        /* Mobile Adjustments */
        .mobile-menu-toggle {
            display: none;
            background: none;
            border: none;
            color: white;
            font-size: 20px;
            cursor: pointer;
        }

        /* Styles personnalisés pour la pagination */
        .pagination-custom {
            display: flex;
            justify-content: center;
            margin-top: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .pagination-custom .page-item {
            margin: 0 3px;
        }

        .pagination-custom .page-link {
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

        .pagination-custom .page-item.active .page-link {
            color: #fff;
            background-color: #1266f1;
            border-color: #1266f1;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }

        .pagination-custom .page-item.disabled .page-link {
            color: #adb5bd;
            background-color: #f8f9fa;
            border-color: #dee2e6;
        }

        .pagination-custom .page-link:hover {
            color: #fff;
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        /* Cards modernes */
        .card {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: transform var(--transition-speed), box-shadow var(--transition-speed);
            overflow: hidden;
            background: white;
            margin-bottom: 1.5rem;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background: linear-gradient(135deg, rgba(55, 48, 163, 0.03) 0%, rgba(55, 48, 163, 0.08) 100%);
            border-bottom: 1px solid rgba(55, 48, 163, 0.1);
            padding: 1.2rem 1.5rem;
            font-weight: 600;
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Tables modernes */
        .table {
            border-collapse: separate;
            border-spacing: 0;
            border: none;
            width: 100%;
            margin-bottom: 1.5rem;
        }

        .table th {
            background-color: rgba(55, 48, 163, 0.04);
            color: var(--primary-color);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.05em;
            padding: 1rem;
            border: none;
        }

        .table td {
            padding: 1rem;
            border-top: 1px solid rgba(0, 0, 0, 0.05);
            vertical-align: middle;
        }

        .table-striped tbody tr:nth-of-type(odd) {
            background-color: rgba(248, 247, 244, 0.7);
        }

        .table-hover tbody tr:hover {
            background-color: rgba(100, 166, 189, 0.05);
        }

        /* Styles modernes pour le tableau d'étudiants */
        .student-data-container {
            position: relative;
        }

        .search-wrapper {
            position: relative;
        }

        .search-icon {
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted-color);
            z-index: 2;
        }

        .custom-search {
            padding-left: 35px;
            border-radius: 30px;
            border: 1px solid rgba(0,0,0,0.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.02);
            transition: all 0.3s;
        }

        .custom-search:focus {
            box-shadow: 0 0 0 3px rgba(55, 48, 163, 0.1);
        }

        .filter-buttons .btn {
            border-radius: 30px;
            padding: 0.4rem 1.2rem;
            transition: all 0.3s;
        }

        .modern-table-container {
            border-radius: var(--border-radius);
            box-shadow: 0 0 10px rgba(0,0,0,0.02);
            overflow: hidden;
            background: white;
        }

        .modern-table-container table thead th {
            background: linear-gradient(45deg, rgba(55, 48, 163, 0.03), rgba(55, 48, 163, 0.06));
            font-weight: 600;
            padding: 1rem;
            vertical-align: middle;
        }

        .student-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 14px;
        }

        .level-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            background-color: rgba(55, 48, 163, 0.1);
            color: var(--primary-color);
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .choices-container {
            display: flex;
            flex-wrap: wrap;
            gap: 5px;
        }

        .choice {
            display: inline-block;
            padding: 0.3rem 0.6rem;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
            cursor: pointer;
        }

        .choice-primary {
            background-color: rgba(55, 48, 163, 0.15);
            color: var(--primary-color);
        }

        .choice-secondary {
            background-color: rgba(233, 116, 81, 0.15);
            color: var(--secondary-color);
        }

        .choice-tertiary {
            background-color: rgba(100, 166, 189, 0.15);
            color: var(--tertiary-color);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.5rem 0.8rem;
            border-radius: 30px;
            font-weight: 500;
            font-size: 0.85rem;
        }

        .status-selected {
            background-color: rgba(45, 106, 79, 0.15);
            color: var(--success-color);
        }

        .status-pending {
            background-color: rgba(148, 163, 184, 0.15);
            color: var(--muted-color);
        }

        .student-row {
            transition: all 0.3s;
        }

        .selected-student {
            border-left: 3px solid var(--success-color);
        }

        .not-selected-student {
            border-left: 3px solid transparent;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(55, 48, 163, 0.15);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--dark-color);
        }

        .input-group {
            border-radius: calc(var(--border-radius) / 2);
            overflow: hidden;
        }

        .input-group-text {
            background-color: rgba(55, 48, 163, 0.05);
            border: 1px solid rgba(0, 0, 0, 0.1);
            color: var(--primary-color);
        }

        /* Badges et alertes */
        .badge {
            padding: 0.5em 0.8em;
            font-weight: 500;
            border-radius: 30px;
        }

        .badge-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .badge-secondary {
            background-color: var(--secondary-color);
            color: white;
        }

        .alert {
            border: none;
            border-radius: var(--border-radius);
            padding: 1.25rem 1.5rem;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
        }

        .alert-primary {
            background-color: rgba(55, 48, 163, 0.1);
            color: var(--primary-color);
        }

        .alert-secondary {
            background-color: rgba(233, 116, 81, 0.1);
            color: var(--secondary-color);
        }

        /* Animations et transitions */
        .fade-in {
            animation: fadeIn var(--transition-speed);
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Ajustements responsifs */
        @media (max-width: 992px) {
            .mobile-menu-toggle {
                display: block;
            }

            .main-navbar {
                height: auto;
                transform: translateY(-100%);
                transition: transform 0.3s;
            }

            .main-navbar.show {
                transform: translateY(0);
            }

            .nav-container {
                flex-direction: column;
                align-items: flex-start;
                padding: 10px 0;
            }

            .nav-menu {
                flex-direction: column;
                width: 100%;
            }

            .nav-item {
                width: 100%;
                height: auto;
            }

            .nav-link {
                padding: 12px 15px;
                border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            }

            .nav-dropdown-menu {
                position: static;
                opacity: 1;
                visibility: visible;
                transform: none;
                box-shadow: none;
                padding-left: 20px;
                display: none;
            }

            .nav-dropdown.show .nav-dropdown-menu {
                display: block;
            }

            .dropdown-item {
                padding: 10px 15px;
            }
        }
    </style>

    @yield('styles')
</head>
<body>
    <!-- Header -->
    <header class="main-header">
        <button class="mobile-menu-toggle" id="mobileMenuToggle">
            <i class="fas fa-bars"></i>
        </button>

        <div class="brand-logo">
            <img src="{{ asset('img/uniselect-logo.svg') }}" alt="UniSelect Logo" class="mr-2">
        </div>

        <div class="user-menu dropdown">
            <div class="user-info d-none d-md-block">
                <div class="user-name">{{ Auth::user()->name }}</div>
                <div class="user-role">{{ Auth::user()->role === 'super_admin' ? 'Super Admin' : 'Administrateur' }}</div>
            </div>

            <div class="avatar dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                @if(Auth::user()->avatar)
                    <img src="{{ asset('storage/' . Auth::user()->avatar) }}"
                         style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                         alt="{{ Auth::user()->name }}">
                @else
                    {{ substr(Auth::user()->name, 0, 1) }}
                @endif
            </div>

            <ul class="dropdown-menu dropdown-menu-end">
                <li>
                    <a class="dropdown-item" href="{{ route('profile.show') }}">
                        <i class="fas fa-user me-2"></i>Mon profil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit') }}">
                        <i class="fas fa-edit me-2"></i>Modifier profil
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="{{ route('profile.edit.password') }}">
                        <i class="fas fa-key me-2"></i>Changer mot de passe
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-danger" href="{{ route('logout') }}"
                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="fas fa-sign-out-alt me-2"></i>Déconnexion
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                        @csrf
                    </form>
                </li>
            </ul>
        </div>
    </header>

    <!-- Navigation Bar -->
    <nav class="main-navbar" id="mainNavbar">
        <div class="container nav-container">
            <ul class="nav-menu">
                <li class="nav-item">
                    <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fas fa-tachometer-alt"></i> Tableau de bord
                    </a>
                </li>

                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.etudiants*') ? 'active' : '' }}">
                        <i class="fas fa-users"></i> Étudiants <i class="fas fa-chevron-down ms-1" style="font-size: 10px;"></i>
                    </a>
                    <ul class="nav-dropdown-menu">
                        <li>
                            <a href="{{ route('admin.etudiants') }}" class="dropdown-item">
                                <i class="fas fa-list"></i> Liste des étudiants
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.etudiants.importer') }}" class="dropdown-item">
                                <i class="fas fa-file-import"></i> Importer des données
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item nav-dropdown">
                    <a href="#" class="nav-link {{ request()->routeIs('admin.criteres*') || request()->routeIs('admin.selections*') ? 'active' : '' }}">
                        <i class="fas fa-cogs"></i> Sélection <i class="fas fa-chevron-down ms-1" style="font-size: 10px;"></i>
                    </a>
                    <ul class="nav-dropdown-menu">
                        <li>
                            <a href="{{ route('admin.criteres') }}" class="dropdown-item">
                                <i class="fas fa-cogs"></i> Critères de sélection
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.selections.generer') }}" class="dropdown-item">
                                <i class="fas fa-bolt"></i> Générer une sélection
                            </a>
                        </li>
                        <li>
                            <a href="{{ route('admin.selections') }}" class="dropdown-item">
                                <i class="fas fa-user-check"></i> Étudiants sélectionnés
                            </a>
                        </li>
                    </ul>
                </li>

                <li class="nav-item">
                    <a href="{{ route('admin.statistiques') }}" class="nav-link {{ request()->routeIs('admin.statistiques') ? 'active' : '' }}">
                        <i class="fas fa-chart-bar"></i> Statistiques
                    </a>
                </li>

                @if(Auth::user()->role === 'super_admin')
                <li class="nav-item">
                    <a href="{{ route('admin.gestion-admins') }}" class="nav-link {{ request()->routeIs('admin.gestion-admins') ? 'active' : '' }}">
                        <i class="fas fa-user-shield"></i> Gestion des admins
                    </a>
                </li>
                @endif
            </ul>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <div class="container">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('info'))
                <div class="alert alert-info alert-dismissible fade show" role="alert">
                    <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="container">
            <div class="text-muted">
                &copy; {{ date('Y') }} <b style="color: #1a1a2e;">Uni<span style="color: #4cc9f0; font-style: italic;">Select</span></b>. Tous droits réservés.
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile menu toggle
            const mobileMenuToggle = document.getElementById('mobileMenuToggle');
            const mainNavbar = document.getElementById('mainNavbar');

            if (mobileMenuToggle) {
                mobileMenuToggle.addEventListener('click', function() {
                    mainNavbar.classList.toggle('show');
                });
            }

            // Handle dropdown menus on mobile
            const dropdowns = document.querySelectorAll('.nav-dropdown');

            dropdowns.forEach(dropdown => {
                const link = dropdown.querySelector('.nav-link');

                link.addEventListener('click', function(e) {
                    if (window.innerWidth < 992) {
                        e.preventDefault();
                        dropdown.classList.toggle('show');
                    }
                });
            });
        });
    </script>

    @yield('scripts')
</body>
</html>
