@extends('layouts.app')

@section('title', 'Mes annonces')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-car"></i> Mes annonces de véhicules</h1>
            <a href="{{ route('demandes-epaves.create') }}" class="btn btn-success">
                <i class="fas fa-plus"></i> Créer une annonce
            </a>
        </div>

        <!-- Statistiques -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-list fa-2x text-primary mb-2"></i>
                        <h3>{{ $stats['total'] }}</h3>
                        <p class="text-muted mb-0">Total annonces</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card shadow-sm text-center">
                    <div class="card-body">
                        <i class="fas fa-check-double fa-2x text-danger mb-2"></i>
                        <h3>{{ $stats['vendus'] }}</h3>
                        <p class="text-muted mb-0">Vendus</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Répartition par type -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-car fa-2x text-primary mb-2"></i>
                        <h4>{{ $parType['vehicules'] }}</h4>
                        <p class="text-muted mb-0">Véhicules</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-body text-center">
                        <i class="fas fa-car-crash fa-2x text-danger mb-2"></i>
                        <h4>{{ $parType['epaves'] }}</h4>
                        <p class="text-muted mb-0">Épaves</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="btn-group w-100">
                    <button class="btn btn-outline-primary active" onclick="filterItems('all')">
                        <i class="fas fa-list"></i> Toutes ({{ $stats['total'] }})
                    </button>

                    <button class="btn btn-outline-danger" onclick="filterItems('vendu')">
                        <i class="fas fa-check-double"></i> Vendues ({{ $stats['vendus'] }})
                    </button>
                </div>
            </div>
        </div>

        <!-- Liste des annonces -->
        <div class="card shadow">
            <div class="card-header">
                <h5 class="mb-0">Liste de mes annonces</h5>
            </div>
            <div class="card-body">
                @if($vehicules->count() > 0)
                    <div class="row" id="annoncesGrid">
                        @foreach($vehicules as $vehicule)
                            <div class="col-md-4 mb-4 annonce-item" data-statut="{{ $vehicule->statut }}">
                                <div class="card h-100 shadow-sm">
                                    <!-- Image -->
                                    @if($vehicule->premiere_photo)
                                        <img src="{{ $vehicule->premiere_photo }}"
                                             class="card-img-top"
                                             style="height: 200px; object-fit: cover;"
                                             alt="{{ $vehicule->nom_complet }}">
                                    @else
                                        <div class="bg-secondary d-flex align-items-center justify-content-center"
                                             style="height: 200px;">
                                            <i class="fas fa-car fa-4x text-white"></i>
                                        </div>
                                    @endif

                                    <!-- Corps de la carte -->
                                    <div class="card-body">
                                        <!-- En-tête avec badges -->
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <h6 class="card-title mb-0">{{ $vehicule->nom_complet }}</h6>
                                            <span class="badge {{ $vehicule->type_badge_class }}">
                                                <i class="fas fa-{{ $vehicule->type === 'vehicule' ? 'car' : 'car-crash' }}"></i>
                                                {{ $vehicule->type_libelle }}
                                            </span>
                                        </div>

                                        <!-- Informations -->
                                        <div class="mb-2">
                                            <small class="text-muted d-block">
                                                <i class="fas fa-palette"></i> {{ $vehicule->couleur }} •
                                                <i class="fas fa-tachometer-alt"></i> {{ number_format($vehicule->kilometrage, 0, ',', ' ') }} km
                                            </small>
                                            <small class="text-muted d-block">
                                                <i class="fas fa-calendar"></i> {{ $vehicule->annee }} •
                                                <i class="fas fa-gas-pump"></i> {{ ucfirst($vehicule->carburant) }}
                                            </small>
                                        </div>

                                        <!-- Statut et quantité -->
                                        <div class="mb-2">
                                            <span class="badge {{ $vehicule->statut_badge_class }}">
                                                {{ ucfirst($vehicule->statut) }}
                                            </span>
                                            <span class="badge bg-info">
                                                <i class="fas fa-box"></i> Qté: {{ $vehicule->quantite }}
                                            </span>
                                        </div>

                                        <!-- Prix -->
                                        <p class="text-success mb-3">
                                            <strong style="font-size: 1.2em;">
                                                {{ number_format($vehicule->prix_souhaite, 0, ',', ' ') }} FCFA
                                            </strong>
                                        </p>

                                        <!-- Actions -->
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('demandes-epaves.show', $vehicule) }}"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Voir l'annonce
                                            </a>

                                            @if($vehicule->statut === 'disponible')
                                                <a href="{{ route('demandes-epaves.edit', $vehicule) }}"
                                                   class="btn btn-sm btn-outline-secondary">
                                                    <i class="fas fa-edit"></i> Modifier
                                                </a>
                                            @endif

                                            @if($vehicule->statut === 'disponible')
                                                <form action="{{ route('demandes-epaves.destroy', $vehicule) }}"
                                                      method="POST"
                                                      onsubmit="return confirm('Supprimer cette annonce ?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                                        <i class="fas fa-trash"></i> Supprimer
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Footer avec date -->
                                    <div class="card-footer text-muted">
                                        <small>
                                            <i class="fas fa-clock"></i>
                                            Publié le {{ $vehicule->created_at->format('d/m/Y') }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-car fa-5x text-muted mb-4"></i>
                        <h3>Aucune annonce</h3>
                        <p class="text-muted mb-4">
                            Vous n'avez pas encore créé d'annonce de véhicule ou d'épave.<br>
                            Créez votre première annonce pour vendre votre véhicule.
                        </p>
                        <a href="{{ route('demandes-epaves.create') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-plus"></i> Créer ma première annonce
                        </a>
                    </div>
                @endif
            </div>
        </div>

        <!-- Informations utiles -->
        @if($vehicules->count() > 0)
            <div class="card shadow mt-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations utiles</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6><i class="fas fa-check-circle text-success"></i> Statuts des annonces</h6>
                            <ul class="list-unstyled">
                                <li><span class="badge bg-success">Disponible</span> - Votre annonce est visible et peut être achetée</li>
                                <li><span class="badge bg-danger">Vendu</span> - Votre véhicule a été vendu</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6><i class="fas fa-lightbulb text-warning"></i> Conseils</h6>
                            <ul class="list-unstyled">
                                <li>✓ Ajoutez plusieurs photos de qualité</li>
                                <li>✓ Soyez précis dans la description</li>
                                <li>✓ Indiquez le kilométrage exact</li>
                                <li>✓ Mettez à jour le statut régulièrement</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script>
        function filterItems(statut) {
            const items = document.querySelectorAll('.annonce-item');
            const buttons = document.querySelectorAll('.btn-group button');

            // Mettre à jour les boutons actifs
            buttons.forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');

            // Filtrer les items
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
            box-shadow: 0 8px 16px rgba(0,0,0,0.15) !important;
            transform: translateY(-5px);
        }
        .annonce-item {
            transition: opacity 0.3s ease;
        }
    </style>
@endsection
