<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AutoCasse Pro')</title>

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Custom CSS -->
    <style>
        .sidebar {
            min-height: 100vh;
            background: #2c3e50;
        }
        .sidebar .nav-link {
            color: #ecf0f1;
            padding: 0.75rem 1rem;
        }
        .sidebar .nav-link:hover {
            background: #34495e;
            color: #3498db;
        }
        .sidebar .nav-link.active {
            background: #3498db;
            color: white;
        }
        .navbar-brand {
            font-weight: bold;
        }
        .content {
            background: #f8f9fa;
            min-height: 100vh;
        }
    </style>
    @yield('styles')
</head>
<body>
<div class="d-flex">
    <!-- Sidebar -->
    <nav class="sidebar col-md-3 col-lg-2 d-md-block bg-dark sidebar">
        <div class="position-sticky pt-3">
            <div class="text-center text-white mb-4">
                <h4>AutoCasse Pro</h4>
                <small>Gestion de casse automobile</small>
            </div>

            <ul class="nav flex-column">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-2"></i> Tableau de bord
                    </a>
                </li>

                @auth
                    @if(auth()->user()->role->value === 'casse')
                        <!-- Menu Casse uniquement -->
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('vehicles*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                                <i class="fas fa-car me-2"></i> Véhicules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('pieces*') ? 'active' : '' }}" href="{{ route('pieces.index') }}">
                                <i class="fas fa-cog me-2"></i> Pièces détachées
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('gestion/commandes*') ? 'active' : '' }}" href="{{ route('gestion.commandes') }}">
                                <i class="fas fa-shopping-cart me-2"></i> Commandes clients
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('gestion/stocks*') ? 'active' : '' }}" href="{{ route('gestion.stocks') }}">
                                <i class="fas fa-boxes me-2"></i> Gestion des stocks
                            </a>
                        </li>

                            <li class="nav-item">
                                <a class="nav-link {{ Request::is('demandes-epaves*') ? 'active' : '' }}" href="{{ route('demandes-epaves.index') }}">
                                    <i class="fas fa-search-dollar me-2"></i> Demandes d'épaves
                                </a>
                            </li>

                    @elseif(auth()->user()->role->value === 'client')
                        <!-- Menu Client uniquement -->
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('pieces*') ? 'active' : '' }}" href="{{ route('pieces.index') }}">
                                <i class="fas fa-cog me-2"></i> Rechercher pièces
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('vehicles*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                                <i class="fas fa-car me-2"></i> Véhicules d'occasion
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('panier*') ? 'active' : '' }}" href="{{ route('panier.index') }}">
                                <i class="fas fa-shopping-cart me-2"></i> Mon panier
                                @if(auth()->user()->getNombrePiecesPanier() > 0)
                                    <span class="badge bg-primary ms-2">{{ auth()->user()->getNombrePiecesPanier() }}</span>
                                @endif
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('commandes*') ? 'active' : '' }}" href="{{ route('commandes.index') }}">
                                <i class="fas fa-list-alt me-2"></i> Mes commandes
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('demandes-epaves*') ? 'active' : '' }}" href="{{ route('demandes-epaves.index') }}">
                                <i class="fas fa-car-crash me-2"></i> Vendre mon épave
                            </a>
                        </li>


                    @elseif(auth()->user()->role->value === 'admin')
                        <!-- Menu Admin uniquement -->
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/users*') ? 'active' : '' }}" href="{{ route('admin.users.index') }}">
                                <i class="fas fa-users me-2"></i> Utilisateurs
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/statistics*') ? 'active' : '' }}" href="{{ route('admin.statistics') }}">
                                <i class="fas fa-chart-line me-2"></i> Statistiques
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin/settings*') ? 'active' : '' }}" href="{{ route('admin.settings') }}">
                                <i class="fas fa-cogs me-2"></i> Paramètres
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('vehicles*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                                <i class="fas fa-car me-2"></i> Tous les véhicules
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('pieces*') ? 'active' : '' }}" href="{{ route('pieces.index') }}">
                                <i class="fas fa-cog me-2"></i> Toutes les pièces
                            </a>
                        </li>
                    @endif


                    <!-- Menu Commun pour tous les utilisateurs connectés -->
                    <hr class="bg-light my-3">
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('profile*') ? 'active' : '' }}" href="{{ route('profile.show') }}">
                            <i class="fas fa-user me-2"></i> Mon profil
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('notifications*') ? 'active' : '' }}" href="{{ route('notifications.index') }}">
                            <i class="fas fa-bell me-2"></i> Notifications
                            @if(($notificationsNonLues ?? 0) > 0)
                                <span class="badge bg-danger ms-2">{{ $notificationsNonLues }}</span>
                            @endif
                        </a>
                    </li>
                @endauth
            </ul>
        </div>
    </nav>

    <!-- Main content -->
    <main class="content col-md-9 ms-sm-auto col-lg-10 px-md-4">
        <!-- Navbar -->
        <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
            <div class="container-fluid">
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <div class="collapse navbar-collapse" id="navbarContent">
                    <ul class="navbar-nav me-auto">
                        <li class="nav-item">
                            <span class="navbar-text">
                                Bienvenue, <strong>{{ auth()->user()->name }}</strong>
                                <span class="badge bg-{{ auth()->user()->role->value === 'admin' ? 'danger' : (auth()->user()->role->value === 'casse' ? 'success' : 'primary') }} ms-2">
                                    {{ ucfirst(auth()->user()->role->value) }}
                                </span>
                            </span>
                        </li>
                    </ul>

                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <form class="d-flex" action="{{ route('search') }}" method="GET">
                                <input class="form-control me-2" type="search" name="q" placeholder="Rechercher...">
                                <button class="btn btn-outline-primary" type="submit">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="fas fa-user-circle"></i>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item" href="{{ route('profile.show') }}">
                                        <i class="fas fa-user me-2"></i> Profil
                                    </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="dropdown-item">
                                            <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="py-4">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Gestion des messages flash
    document.addEventListener('DOMContentLoaded', function() {
        // Auto-dismiss alerts after 5 seconds
        setTimeout(() => {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    });
</script>
@yield('scripts')
</body>
</html>
