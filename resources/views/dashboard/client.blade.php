@extends('layouts.app')

@section('title', 'Tableau de bord - Client')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h3">Tableau de bord - Client</h1>
            <div class="btn-group">
                <a href="{{ route('pieces.index') }}" class="btn btn-primary">
                    <i class="fas fa-cog"></i> Parcourir les pièces
                </a>
            </div>
        </div>

        <!-- 📦 STATISTIQUES COMMANDES & PANIER -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-primary mb-3">
                    <i class="fas fa-shopping-cart"></i> Mes achats
                </h4>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Commandes totales
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['commandes_total'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Commandes en cours
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['commandes_en_cours'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Articles en panier
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['panier_items'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-basket fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Favoris
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stats['favoris'] ?? 0 }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-heart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 🚗 STATISTIQUES MES VÉHICULES -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-info mb-3">
                    <i class="fas fa-car"></i> Mes véhicules à vendre
                </h4>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Véhicules
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['mes_vehicules_total'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-car fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Disponibles
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['mes_vehicules_disponibles'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Vendus
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['mes_vehicules_vendus'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-double fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 💥 STATISTIQUES MES ÉPAVES -->
        <div class="row mb-4">
            <div class="col-12">
                <h4 class="text-danger mb-3">
                    <i class="fas fa-car-crash"></i> Mes épaves à vendre
                </h4>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Total Épaves
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['mes_epaves_total'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-car-crash fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Disponibles
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['mes_epaves_disponibles'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6 mb-3">
                <div class="card border-left-secondary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-secondary text-uppercase mb-1">
                                    Vendues
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $stats['mes_epaves_vendues'] ?? 0 }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-double fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Colonne gauche -->
            <div class="col-lg-6">
                <!-- Commandes récentes -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Mes commandes récentes</h6>
                        <a href="{{ route('commandes.index') }}" class="btn btn-sm btn-outline-primary">Voir toutes</a>
                    </div>
                    <div class="card-body">
                        @if(!empty($recentCommandes) && $recentCommandes->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead>
                                    <tr>
                                        <th>N° Commande</th>
                                        <th>Date</th>
                                        <th>Total</th>
                                        <th>Statut</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($recentCommandes as $commande)
                                        <tr>
                                            <td><strong>{{ $commande->numero_commande }}</strong></td>
                                            <td>{{ $commande->created_at->format('d/m/Y') }}</td>
                                            <td>{{ number_format($commande->total, 0, ',', ' ') }} FCFA</td>
                                            <td>
                                            <span class="badge bg-{{ $commande->statut === 'livree' ? 'success' :
                                               ($commande->statut === 'annulee' ? 'danger' :
                                               ($commande->statut === 'en_attente' ? 'warning' : 'info')) }}">
                                                {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                                            </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('commandes.show', $commande) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Aucune commande passée</h5>
                                <p class="text-muted">Vous n'avez pas encore passé de commande</p>
                                <a href="{{ route('pieces.index') }}" class="btn btn-primary">
                                    <i class="fas fa-cog"></i> Parcourir les pièces
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Mes annonces d'épaves/véhicules -->
                <div class="card shadow">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Mes annonces récentes</h6>
                        <a href="{{ route('demandes-epaves.create') }}" class="btn btn-sm btn-primary">
                            <i class="fas fa-plus"></i> Nouvelle annonce
                        </a>
                    </div>
                    <div class="card-body">
                        @php
                            $mesAnnonces = auth()->user()->demandesEpaves()->latest()->take(5)->get();
                        @endphp

                        @if($mesAnnonces->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($mesAnnonces as $annonce)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">
                                                <span class="badge {{ $annonce->type_badge_class }}">
                                                    {{ $annonce->type_libelle }}
                                                </span>
                                                    {{ $annonce->marque }} {{ $annonce->modele }} ({{ $annonce->annee }})
                                                </h6>
                                                <p class="mb-1 text-muted small">{{ Str::limit($annonce->description, 60) }}</p>
                                                <span class="badge {{ $annonce->statut_badge_class }}">
                                                {{ ucfirst(str_replace('_', ' ', $annonce->statut)) }}
                                            </span>
                                                @if($annonce->prix_souhaite)
                                                    <span class="badge bg-info">{{ number_format($annonce->prix_souhaite, 0, ',', ' ') }} FCFA</span>
                                                @endif
                                            </div>
                                            <a href="{{ route('demandes-epaves.show', $annonce) }}" class="btn btn-sm btn-outline-primary">
                                                Voir
                                            </a>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('stocks.index') }}" class="btn btn-sm btn-outline-primary">
                                    Voir toutes mes annonces
                                </a>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-car-crash fa-3x text-muted mb-2"></i>
                                <p class="text-muted">Aucune annonce</p>
                                <a href="{{ route('demandes-epaves.create') }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-plus"></i> Créer une annonce
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Colonne droite -->
            <div class="col-lg-6">
                <!-- Notifications -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">Notifications récentes</h6>
                        <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-outline-primary">Voir toutes</a>
                    </div>
                    <div class="card-body">
                        @if(!empty($notifications) && $notifications->count() > 0)
                            <div class="list-group list-group-flush">
                                @foreach($notifications as $notification)
                                    <div class="list-group-item px-0 {{ $notification->lu ? '' : 'bg-light' }}">
                                        <div class="d-flex w-100 justify-content-between">
                                            <h6 class="mb-1">{{ $notification->titre }}</h6>
                                            <small>{{ $notification->created_at->diffForHumans() }}</small>
                                        </div>
                                        <p class="mb-1">{{ $notification->message }}</p>
                                        @if(!$notification->lu)
                                            <small class="text-primary"><i class="fas fa-circle"></i> Non lue</small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-bell fa-3x text-muted mb-2"></i>
                                <h5 class="text-muted">Aucune notification</h5>
                                <p class="text-muted">Vous n'avez aucune notification pour le moment</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pièces populaires -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Pièces populaires</h6>
                    </div>
                    <div class="card-body">
                        @if(!empty($piecesPopulaires) && $piecesPopulaires->count() > 0)
                            <div class="row">
                                @foreach($piecesPopulaires->take(4) as $piece)
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            @if($piece->photos && count($piece->photos) > 0)
                                                <img src="{{ asset('storage/' . $piece->photos[0]) }}" class="card-img-top" style="height: 120px; object-fit: cover;">
                                            @else
                                                <div class="card-img-top bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                                                    <i class="fas fa-cog fa-2x text-muted"></i>
                                                </div>
                                            @endif
                                            <div class="card-body">
                                                <h6 class="card-title">{{ Str::limit($piece->nom, 25) }}</h6>
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="text-primary fw-bold">{{ number_format($piece->prix, 0, ',', ' ') }} FCFA</span>
                                                    <a href="{{ route('pieces.show', $piece) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-center mt-3">
                                <a href="{{ route('pieces.index') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-cog"></i> Voir toutes les pièces
                                </a>
                            </div>
                        @else
                            <div class="text-center py-3">
                                <i class="fas fa-cog fa-3x text-muted mb-2"></i>
                                <p class="text-muted">Aucune pièce disponible</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Actions rapides -->
                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">Actions rapides</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('panier.index') }}" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-cart"></i> Mon panier
                                @if(($stats['panier_items'] ?? 0) > 0)
                                    <span class="badge bg-danger">{{ $stats['panier_items'] }}</span>
                                @endif
                            </a>
                            <a href="{{ route('pieces.index') }}" class="btn btn-outline-success">
                                <i class="fas fa-cog"></i> Rechercher des pièces
                            </a>
                            <a href="{{ route('demandes-epaves.create') }}" class="btn btn-outline-warning">
                                <i class="fas fa-car-crash"></i> Vendre un véhicule/épave
                            </a>
                            <a href="{{ route('stocks.index') }}" class="btn btn-outline-info">
                                <i class="fas fa-list"></i> Mes annonces
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .border-left-primary { border-left: 4px solid #4e73df !important; }
        .border-left-success { border-left: 4px solid #1cc88a !important; }
        .border-left-info { border-left: 4px solid #36b9cc !important; }
        .border-left-warning { border-left: 4px solid #f6c23e !important; }
        .border-left-danger { border-left: 4px solid #e74a3b !important; }
        .border-left-secondary { border-left: 4px solid #858796 !important; }
        .shadow { box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15) !important; }
        .card { border-radius: 10px; transition: transform 0.2s; border: none; }
        .card:hover { transform: translateY(-2px); }
        .list-group-item { border: 1px solid #e3e6f0; }
    </style>
@endsection
