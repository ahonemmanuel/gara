@extends('layouts.app')

@section('title', 'Gestion des stocks')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-warehouse"></i> Gestion des stocks</h1>
            <div class="btn-group">
                <a href="{{ route('pieces.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter une pièce
                </a>
                <a href="{{ route('demandes-epaves.create') }}" class="btn btn-success">
                    <i class="fas fa-car"></i> Ajouter un véhicule
                </a>
            </div>
        </div>

        <!-- Onglets -->
        <ul class="nav nav-tabs mb-4" id="stockTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pieces-tab" data-bs-toggle="tab" data-bs-target="#pieces" type="button">
                    <i class="fas fa-cog"></i> Pièces détachées ({{ $statsPieces['total'] }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="vehicules-tab" data-bs-toggle="tab" data-bs-target="#vehicules" type="button">
                    <i class="fas fa-car"></i> Véhicules & Épaves ({{ $statsVehicules['total'] }})
                </button>
            </li>
        </ul>

        <!-- Contenu des onglets -->
        <div class="tab-content" id="stockTabsContent">
            <!-- ONGLET PIÈCES -->
            <div class="tab-pane fade show active" id="pieces" role="tabpanel">
                <!-- Statistiques Pièces -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-cog fa-2x text-primary mb-2"></i>
                                <h3>{{ $statsPieces['total'] }}</h3>
                                <p class="text-muted mb-0">Pièces différentes</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-boxes fa-2x text-info mb-2"></i>
                                <h3>{{ $statsPieces['stock_total'] }}</h3>
                                <p class="text-muted mb-0">Stock total</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-exclamation-triangle fa-2x text-warning mb-2"></i>
                                <h3>{{ $statsPieces['stock_faible']->count() }}</h3>
                                <p class="text-muted mb-0">Stock faible</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des pièces -->
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="mb-0">Stock de Pièces</h5>
                    </div>
                    <div class="card-body">
                        @if($pieces->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Pièce</th>
                                        <th>Marque/Modèle</th>
                                        <th>État</th>
                                        <th>Prix</th>
                                        <th>Stock</th>
                                        <th>Disponible</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($pieces as $piece)
                                        <tr>
                                            <td>
                                                <strong>{{ $piece->nom }}</strong>
                                                @if($piece->reference_constructeur)
                                                    <br><small class="text-muted">Réf: {{ $piece->reference_constructeur }}</small>
                                                @endif
                                            </td>
                                            <td>
                                                <small class="text-muted">
                                                    {{ $piece->marque->nom ?? 'N/A' }} {{ $piece->modele->nom ?? '' }}
                                                </small>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary">
                                                    {{ ucfirst(str_replace('_', ' ', $piece->etat)) }}
                                                </span>
                                            </td>
                                            <td>{{ number_format($piece->prix, 0, ',', ' ') }} FCFA</td>
                                            <td>
                                                <span class="badge bg-{{ $piece->quantite > 10 ? 'success' : ($piece->quantite > 0 ? 'warning' : 'danger') }}">
                                                    {{ $piece->quantite }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge bg-{{ $piece->disponible ? 'success' : 'danger' }}">
                                                    {{ $piece->disponible ? 'Oui' : 'Non' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('pieces.show', $piece) }}" class="btn btn-outline-primary" title="Voir">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('pieces.edit', $piece) }}" class="btn btn-outline-secondary" title="Modifier">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-cog fa-4x text-muted mb-3"></i>
                                <h4>Aucune pièce en stock</h4>
                                <p class="text-muted">Commencez par ajouter vos pièces détachées</p>
                                <a href="{{ route('pieces.create') }}" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Ajouter une pièce
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Alertes de stock faible -->
                @if($statsPieces['stock_faible']->count() > 0 || $statsPieces['stock_vide']->count() > 0)
                    <div class="row mt-4">
                        @if($statsPieces['stock_faible']->count() > 0)
                            <div class="col-md-6">
                                <div class="card shadow border-warning">
                                    <div class="card-header bg-warning text-dark">
                                        <h6 class="mb-0"><i class="fas fa-exclamation-triangle me-2"></i>Stock faible ({{ $statsPieces['stock_faible']->count() }} pièce(s))</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach($statsPieces['stock_faible'] as $piece)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>{{ $piece->nom }}</span>
                                                    <span class="badge bg-warning">{{ $piece->quantite }} restant(s)</span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif

                        @if($statsPieces['stock_vide']->count() > 0)
                            <div class="col-md-6">
                                <div class="card shadow border-danger">
                                    <div class="card-header bg-danger text-white">
                                        <h6 class="mb-0"><i class="fas fa-times-circle me-2"></i>Stock épuisé ({{ $statsPieces['stock_vide']->count() }} pièce(s))</h6>
                                    </div>
                                    <div class="card-body">
                                        <ul class="list-group list-group-flush">
                                            @foreach($statsPieces['stock_vide'] as $piece)
                                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                                    <span>{{ $piece->nom }}</span>
                                                    <a href="{{ route('pieces.edit', $piece) }}" class="btn btn-sm btn-outline-primary">
                                                        Réapprovisionner
                                                    </a>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- ONGLET VÉHICULES -->
            <div class="tab-pane fade" id="vehicules" role="tabpanel">
                <!-- Statistiques Véhicules -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-car fa-2x text-success mb-2"></i>
                                <h3>{{ $statsVehicules['total'] }}</h3>
                                <p class="text-muted mb-0 small">Total annonces</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-check-double fa-2x text-danger mb-2"></i>
                                <h3>{{ $statsVehicules['vendus'] }}</h3>
                                <p class="text-muted mb-0 small">Vendus</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-boxes fa-2x text-info mb-2"></i>
                                <h3>{{ $statsVehicules['stock_total'] }}</h3>
                                <p class="text-muted mb-0 small">Stock total</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm text-center">
                            <div class="card-body">
                                <i class="fas fa-dollar-sign fa-2x text-primary mb-2"></i>
                                <h4 class="small">{{ number_format($statsVehicules['valeur_totale'], 0, ',', ' ') }}</h4>
                                <p class="text-muted mb-0 small">Valeur (FCFA)</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Répartition par type -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6><i class="fas fa-car text-primary"></i> Véhicules: <strong>{{ $parType['vehicules'] }}</strong></h6>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h6><i class="fas fa-car-crash text-danger"></i> Épaves: <strong>{{ $parType['epaves'] }}</strong></h6>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Liste des véhicules -->
                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Stock de Véhicules & Épaves</h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary" onclick="filterVehicules('all')">Tous</button>
                            <button class="btn btn-outline-danger" onclick="filterVehicules('vendu')">Vendus</button>
                        </div>
                    </div>
                    <div class="card-body">
                        @if($vehicules->count() > 0)
                            <div class="row" id="vehiculesGrid">
                                @foreach($vehicules as $vehicule)
                                    <div class="col-md-4 mb-3 vehicule-item" data-statut="{{ $vehicule->statut }}">
                                        <div class="card h-100">
                                            @if($vehicule->premiere_photo)
                                                <img src="{{ $vehicule->premiere_photo }}" class="card-img-top" style="height: 200px; object-fit: cover;" alt="{{ $vehicule->nom_complet }}">
                                            @else
                                                <div class="bg-secondary d-flex align-items-center justify-content-center" style="height: 200px;">
                                                    <i class="fas fa-car fa-4x text-white"></i>
                                                </div>
                                            @endif
                                            <div class="card-body">
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="card-title mb-0">{{ $vehicule->nom_complet }}</h6>
                                                    <span class="badge {{ $vehicule->type_badge_class }}">
                                                        {{ $vehicule->type_libelle }}
                                                    </span>
                                                </div>
                                                <p class="card-text">
                                                    <span class="badge {{ $vehicule->statut_badge_class }}">
                                                        {{ ucfirst($vehicule->statut) }}
                                                    </span>
                                                    <span class="badge bg-info">Qté: {{ $vehicule->quantite }}</span>
                                                </p>
                                                <p class="text-success mb-2">
                                                    <strong>{{ number_format($vehicule->prix_souhaite, 0, ',', ' ') }} FCFA</strong>
                                                </p>
                                                <div class="d-grid gap-2">
                                                    <a href="{{ route('demandes-epaves.show', $vehicule) }}" class="btn btn-sm btn-outline-primary">
                                                        <i class="fas fa-eye"></i> Voir
                                                    </a>
                                                    <a href="{{ route('demandes-epaves.edit', $vehicule) }}" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-edit"></i> Modifier
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-5">
                                <i class="fas fa-car fa-4x text-muted mb-3"></i>
                                <h4>Aucun véhicule en stock</h4>
                                <p class="text-muted">Commencez par ajouter vos véhicules et épaves</p>
                                <a href="{{ route('demandes-epaves.create') }}" class="btn btn-success">
                                    <i class="fas fa-plus"></i> Ajouter un véhicule
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function filterVehicules(statut) {
            const items = document.querySelectorAll('.vehicule-item');
            items.forEach(item => {
                if (statut === 'all' || item.dataset.statut === statut) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        }
    </script>

    <style>
        .card {
            transition: all 0.3s ease;
        }
        .card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            transform: translateY(-2px);
        }
    </style>
@endsection
