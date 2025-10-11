@extends('layouts.app')

@section('title', 'Véhicules et Épaves')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-car"></i> Véhicules et Épaves</h1>
            <a href="{{ route('demandes-epaves.create') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> Créer une annonce
            </a>
        </div>

        <!-- Onglets -->
        <ul class="nav nav-tabs mb-4" id="annoncesTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="disponibles-tab" data-bs-toggle="tab"
                        data-bs-target="#disponibles" type="button" role="tab">
                    <i class="fas fa-shopping-cart"></i> Annonces disponibles
                    <span class="badge bg-primary">{{ $autresDemandes->total() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="mes-annonces-tab" data-bs-toggle="tab"
                        data-bs-target="#mes-annonces" type="button" role="tab">
                    <i class="fas fa-list"></i> Mes annonces
                    <span class="badge bg-secondary">{{ $mesDemandes->total() }}</span>
                </button>
            </li>
        </ul>

        <!-- Contenu des onglets -->
        <div class="tab-content" id="annoncesTabContent">

            <!-- ONGLET: Annonces disponibles -->
            <div class="tab-pane fade show active" id="disponibles" role="tabpanel">

                <!-- Filtres -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body">
                        <form method="GET" action="{{ route('demandes-epaves.index') }}" class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label">Marque</label>
                                <input type="text" name="marque" class="form-control"
                                       value="{{ request('marque') }}" placeholder="Ex: Toyota">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="vehicule" {{ request('type') == 'vehicule' ? 'selected' : '' }}>
                                        Véhicule
                                    </option>
                                    <option value="epave" {{ request('type') == 'epave' ? 'selected' : '' }}>
                                        Épave
                                    </option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">État</label>
                                <select name="etat" class="form-select">
                                    <option value="">Tous</option>
                                    <option value="bon" {{ request('etat') == 'bon' ? 'selected' : '' }}>Bon</option>
                                    <option value="moyen" {{ request('etat') == 'moyen' ? 'selected' : '' }}>Moyen</option>
                                    <option value="mauvais" {{ request('etat') == 'mauvais' ? 'selected' : '' }}>Mauvais</option>
                                    <option value="epave" {{ request('etat') == 'epave' ? 'selected' : '' }}>Épave</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Prix maximum (FCFA)</label>
                                <input type="number" name="prix_max" class="form-control"
                                       value="{{ request('prix_max') }}" placeholder="Ex: 5000000">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filtrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($autresDemandes->count() > 0)
                    <div class="row">
                        @foreach($autresDemandes as $demande)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm hover-shadow">
                                    <!-- Image -->
                                    @if($demande->premiere_photo)
                                        <img src="{{ $demande->premiere_photo }}"
                                             class="card-img-top"
                                             style="height: 200px; object-fit: cover;"
                                             alt="{{ $demande->nom_complet }}">
                                    @else
                                        <div class="bg-secondary d-flex align-items-center justify-content-center"
                                             style="height: 200px;">
                                            <i class="fas fa-car fa-4x text-white"></i>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <!-- Badges -->
                                        <div class="mb-2">
                                        <span class="badge {{ $demande->type_badge_class }}">
                                            {{ $demande->type_libelle }}
                                        </span>
                                            <span class="badge {{ $demande->statut_badge_class }}">
                                            {{ ucfirst($demande->statut) }}
                                        </span>
                                        </div>

                                        <!-- Titre -->
                                        <h5 class="card-title">{{ $demande->nom_complet }}</h5>

                                        <!-- Informations -->
                                        <ul class="list-unstyled mb-3">
                                            <li><i class="fas fa-palette text-muted"></i> {{ $demande->couleur }}</li>
                                            <li><i class="fas fa-gas-pump text-muted"></i> {{ ucfirst($demande->carburant) }}</li>
                                            <li><i class="fas fa-tachometer-alt text-muted"></i> {{ number_format($demande->kilometrage, 0, ',', ' ') }} km</li>
                                            <li><i class="fas fa-info-circle text-muted"></i> État: {{ ucfirst($demande->etat) }}</li>
                                        </ul>

                                        <!-- Prix -->
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h4 class="text-success mb-0">
                                                {{ number_format($demande->prix_souhaite, 0, ',', ' ') }} FCFA
                                            </h4>
                                        </div>

                                        <!-- Vendeur -->
                                        @if($demande->vendeur)
                                            <small class="text-muted">
                                                <i class="fas fa-user"></i> {{ $demande->vendeur->name }}
                                            </small>
                                        @endif
                                    </div>

                                    <div class="card-footer bg-white">
                                        <div class="d-grid gap-2">
                                            <a href="{{ route('demandes-epaves.show', $demande) }}"
                                               class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i> Voir les détails
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $autresDemandes->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Aucune annonce disponible pour le moment.
                    </div>
                @endif
            </div>

            <!-- ONGLET: Mes annonces -->
            <div class="tab-pane fade" id="mes-annonces" role="tabpanel">
                @if($mesDemandes->count() > 0)
                    <div class="row">
                        @foreach($mesDemandes as $demande)
                            <div class="col-md-6 col-lg-4 mb-4">
                                <div class="card h-100 shadow-sm">
                                    <!-- Image -->
                                    @if($demande->premiere_photo)
                                        <img src="{{ $demande->premiere_photo }}"
                                             class="card-img-top"
                                             style="height: 200px; object-fit: cover;"
                                             alt="{{ $demande->nom_complet }}">
                                    @else
                                        <div class="bg-secondary d-flex align-items-center justify-content-center"
                                             style="height: 200px;">
                                            <i class="fas fa-car fa-4x text-white"></i>
                                        </div>
                                    @endif

                                    <div class="card-body">
                                        <!-- Badges -->
                                        <div class="mb-2">
                                        <span class="badge {{ $demande->type_badge_class }}">
                                            {{ $demande->type_libelle }}
                                        </span>
                                            <span class="badge {{ $demande->statut_badge_class }}">
                                            {{ ucfirst($demande->statut) }}
                                        </span>
                                        </div>

                                        <!-- Titre -->
                                        <h5 class="card-title">{{ $demande->nom_complet }}</h5>

                                        <!-- Informations -->
                                        <ul class="list-unstyled mb-3">
                                            <li><i class="fas fa-palette text-muted"></i> {{ $demande->couleur }}</li>
                                            <li><i class="fas fa-gas-pump text-muted"></i> {{ ucfirst($demande->carburant) }}</li>
                                            <li><i class="fas fa-tachometer-alt text-muted"></i> {{ number_format($demande->kilometrage, 0, ',', ' ') }} km</li>
                                        </ul>

                                        <!-- Prix -->
                                        <h4 class="text-success mb-3">
                                            {{ number_format($demande->prix_souhaite, 0, ',', ' ') }} FCFA
                                        </h4>

                                        <!-- Date de création -->
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i>
                                            Publié le {{ $demande->created_at->format('d/m/Y') }}
                                        </small>
                                    </div>

                                    <div class="card-footer bg-white">
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('demandes-epaves.show', $demande) }}"
                                               class="btn btn-sm btn-outline-primary flex-fill">
                                                <i class="fas fa-eye"></i> Voir
                                            </a>
                                            <a href="{{ route('demandes-epaves.edit', $demande) }}"
                                               class="btn btn-sm btn-outline-warning flex-fill">
                                                <i class="fas fa-edit"></i> Modifier
                                            </a>
                                            <form action="{{ route('demandes-epaves.destroy', $demande) }}"
                                                  method="POST" class="flex-fill">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger w-100"
                                                        onclick="return confirm('Supprimer cette annonce ?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $mesDemandes->links() }}
                    </div>
                @else
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        Vous n'avez pas encore créé d'annonce.
                        <a href="{{ route('demandes-epaves.create') }}" class="alert-link">
                            Créer une annonce
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('styles')
        <style>
            .hover-shadow {
                transition: all 0.3s ease;
            }
            .hover-shadow:hover {
                transform: translateY(-5px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            }
        </style>
    @endpush
@endsection
