<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Système de sélection universitaire">

    <title>UniSelect - Système de Sélection Universitaire</title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="{{ asset('img/favicon.svg') }}">
    <link rel="apple-touch-icon" href="{{ asset('img/uniselect-logo.svg') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        /* Variables */
        :root {
            --primary: #4361ee;
            --primary-rgb: 67, 97, 238;
            --secondary: #4cc9f0;
            --secondary-rgb: 76, 201, 240;
            --accent: #3a0ca3;
            --accent-rgb: 58, 12, 163;
            --dark: #1a1a2e;
            --light: #f8f9fa;
            --text-color: #333;
            --card-bg: #ffffff;
            --card-border: rgba(0, 0, 0, 0.125);
            --input-bg: #ffffff;
            --shadow-color: rgba(0, 0, 0, 0.1);
            --body-bg: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            --navbar-bg: rgba(255, 255, 255, 0.1);
            --navbar-text: #ffffff;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--body-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            color: var(--text-color);
            transition: all 0.3s ease;
        }

        /* Navbar responsif */
        .navbar {
            background-color: var(--navbar-bg) !important;
            backdrop-filter: blur(10px);
            box-shadow: 0 5px 15px var(--shadow-color);
            padding: 0.8rem 1rem;
        }

        .navbar-brand {
            font-size: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 700;
            letter-spacing: 1px;
            color: white !important;
        }

        .navbar-brand img {
            height: 40px;
            width: auto;
        }



        /* Styles pour le formulaire de connexion */
        .login-btn {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            box-shadow: 0 5px 15px var(--shadow-color);
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px var(--shadow-color);
        }

        .back-link {
            color: var(--primary);
            transition: all 0.3s ease;
        }

        .back-link:hover {
            color: var(--accent);
        }

        .accent-text {
            color: var(--secondary);
            font-style: italic;
        }

        .input-group-text {
            background-color: var(--card-bg);
            color: var(--primary);
            border-right: none;
        }

        .input-group .form-control {
            border-left: none;
        }

        /* Styles responsifs pour les cartes */
        .card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            transition: all 0.3s ease;
        }

        .card-header.text-white {
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%) !important;
        }

        /* Inputs et formulaires */
        .form-control, .form-select {
            background-color: var(--input-bg);
            color: var(--text-color);
            border: 1px solid var(--card-border);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 0.25rem rgba(67, 97, 238, 0.25);
        }

        /* Media queries pour la responsivité */
        @media (max-width: 768px) {
            .navbar-brand {
                font-size: 18px;
            }

            .navbar-brand img {
                height: 30px;
            }

            .card {
                margin-bottom: 1rem;
            }


        }

        .navbar-brand span {
            color: var(--secondary);
            font-style: italic;
        }

        /* Main content */
        .main-content {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 0;
        }

        /* Cards */
        .choice-card {
            background-color: var(--card-bg);
            border-radius: 15px;
            padding: 30px 20px;
            text-align: center;
            box-shadow: 0 10px 25px var(--shadow-color);
            margin-bottom: 30px;
            transition: all 0.3s ease-in-out;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--card-border);
        }

        .choice-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px var(--shadow-color);
        }

        .icon-container {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: linear-gradient(135deg, rgba(var(--primary-rgb), 0.1) 0%, rgba(var(--accent-rgb), 0.1) 100%);
            color: var(--primary);
        }

        .student-btn {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .student-btn:hover {
            background: linear-gradient(135deg, #3a0ca3 0%, #4361ee 100%);
            box-shadow: 0 5px 15px var(--shadow-color);
            transform: translateY(-3px);
        }

        .admin-btn {
            background: linear-gradient(135deg, #06d6a0 0%, #118ab2 100%);
            color: white;
            border: none;
            border-radius: 30px;
            padding: 12px 20px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .admin-btn:hover {
            background: linear-gradient(135deg, #118ab2 0%, #06d6a0 100%);
            box-shadow: 0 5px 15px var(--shadow-color);
            transform: translateY(-3px);
        }

        .choice-icon {
            width: 80px;
            height: 80px;
            line-height: 80px;
            font-size: 36px;
            margin: 0 auto 25px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary) 0%, var(--accent) 100%);
            color: white;
            box-shadow: 0 10px 20px rgba(67, 97, 238, 0.3);
        }

        .student-icon {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
        }

        .admin-icon {
            background: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
        }

        .choice-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
            color: var(--dark);
        }

        .choice-description {
            color: #6c757d;
            margin-bottom: 25px;
        }

        .btn-choice {
            padding: 12px 30px;
            font-weight: 500;
            border-radius: 30px;
            transition: all 0.3s ease;
        }

        .btn-choice:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .btn-student {
            background: linear-gradient(135deg, #4361ee 0%, #3a0ca3 100%);
            border: none;
        }

        .btn-admin {
            background: linear-gradient(135deg, #4cc9f0 0%, #4361ee 100%);
            border: none;
        }

        /* Footer */
        footer {
            background-color: rgba(0, 0, 0, 0.2);
            color: rgba(255, 255, 255, 0.8);
            padding: 20px 0;
            text-align: center;
        }

        /* Animation */
        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        .float-animation {
            animation: float 4s ease-in-out infinite;
        }
    </style>
</head>
<body>
    <div id="app">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-md navbar-dark">
            <div class="container">
                <a class="navbar-brand" href="{{ url('/') }}">
                    <img src="{{ asset('img/uniselect-logo.svg') }}">
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="{{ __('Toggle navigation') }}">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto">
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#">À propos</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link text-white" href="#">Contact</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content">
            <div class="container">
                @yield('content')
            </div>
        </div>

        <!-- Footer -->
        <footer>
            <div class="container">
                <p class="mb-0">&copy; {{ date('Y') }} <b>Uni<span style="color: var(--secondary); font-style: italic;">Select</span></b>. Tous droits réservés.</p>
            </div>
        </footer>



        <!-- Scripts -->
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>


    </body>
</html>
