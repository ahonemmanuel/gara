@extends('layouts.app')

@section('title', $demandeEpave->nom_complet)

@section('content')
    <div class="container-fluid">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="{{ route('demandes-epaves.index') }}">
                        <i class="fas fa-car"></i> Annonces
                    </a>
                </li>
                <li class="breadcrumb-item active">{{ $demandeEpave->nom_complet }}</li>
            </ol>
        </nav>

        <div class="row">
            <!-- Colonne principale -->
            <div class="col-lg-8">
                <!-- Galerie photos -->
                <div class="card shadow-sm mb-4">
                    <div class="card-body p-0">
                        @if($demandeEpave->photos && count($demandeEpave->photos) > 0)
                            <div id="carouselPhotos" class="carousel slide" data-bs-ride="carousel">
                                <div class="carousel-indicators">
                                    @foreach($demandeEpave->photos as $index => $photo)
                                        <button type="button"
                                                data-bs-target="#carouselPhotos"
                                                data-bs-slide-to="{{ $index }}"
                                                class="{{ $index === 0 ? 'active' : '' }}">
                                        </button>
                                    @endforeach
                                </div>
                                <div class="carousel-inner">
                                    @foreach($demandeEpave->photos as $index => $photo)
                                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                            <img src="{{ asset('storage/' . $photo) }}"
                                                 class="d-block w-100"
                                                 style="height: 500px; object-fit: cover;"
                                                 alt="Photo {{ $index + 1 }}">
                                        </div>
                                    @endforeach
                                </div>
                                @if(count($demandeEpave->photos) > 1)
                                    <button class="carousel-control-prev" type="button"
                                            data-bs-target="#carouselPhotos" data-bs-slide="prev">
                                        <span class="carousel-control-prev-icon"></span>
                                    </button>
                                    <button class="carousel-control-next" type="button"
                                            data-bs-target="#carouselPhotos" data-bs-slide="next">
                                        <span class="carousel-control-next-icon"></span>
                                    </button>
                                @endif
                            </div>
                        @else
                            <div class="bg-secondary d-flex align-items-center justify-content-center"
                                 style="height: 500px;">
                                <i class="fas fa-car fa-5x text-white"></i>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informations détaillées -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-info-circle"></i> Informations détaillées</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-tag text-primary"></i> Marque:</strong>
                                <span class="float-end">{{ $demandeEpave->marque }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-car text-primary"></i> Modèle:</strong>
                                <span class="float-end">{{ $demandeEpave->modele }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-calendar text-primary"></i> Année:</strong>
                                <span class="float-end">{{ $demandeEpave->annee }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-palette text-primary"></i> Couleur:</strong>
                                <span class="float-end">{{ $demandeEpave->couleur }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-gas-pump text-primary"></i> Carburant:</strong>
                                <span class="float-end">{{ ucfirst($demandeEpave->carburant) }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-tachometer-alt text-primary"></i> Kilométrage:</strong>
                                <span class="float-end">{{ number_format($demandeEpave->kilometrage, 0, ',', ' ') }} km</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-wrench text-primary"></i> État:</strong>
                                <span class="float-end badge bg-{{ $demandeEpave->etat === 'bon' ? 'success' : ($demandeEpave->etat === 'moyen' ? 'warning' : 'danger') }}">
                                {{ ucfirst($demandeEpave->etat) }}
                            </span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-barcode text-primary"></i> N° Chassis:</strong>
                                <span class="float-end">{{ $demandeEpave->numero_chassis }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-id-card text-primary"></i> N° Plaque:</strong>
                                <span class="float-end">{{ $demandeEpave->numero_plaque }}</span>
                            </div>
                            <div class="col-md-6 mb-3">
                                <strong><i class="fas fa-box text-primary"></i> Quantité:</strong>
                                <span class="float-end">{{ $demandeEpave->quantite }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <div class="card shadow-sm mb-4">
                    <div class="card-header">
                        <h5 class="mb-0"><i class="fas fa-file-alt"></i> Description</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-0">{{ $demandeEpave->description }}</p>
                    </div>
                </div>

                <!-- Annonces similaires -->
                @if($annancesSimilaires->count() > 0)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-list"></i> Annonces similaires</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                @foreach($annancesSimilaires as $similaire)
                                    <div class="col-md-6 mb-3">
                                        <div class="card h-100">
                                            @if($similaire->premiere_photo)
                                                <img src="{{ $similaire->premiere_photo }}"
                                                     class="card-img-top"
                                                     style="height: 150px; object-fit: cover;"
                                                     alt="{{ $similaire->nom_complet }}">
                                            @endif
                                            <div class="card-body">
                                                <h6>{{ $similaire->nom_complet }}</h6>
                                                <p class="text-success mb-2">
                                                    <strong>{{ number_format($similaire->prix_souhaite, 0, ',', ' ') }} FCFA</strong>
                                                </p>
                                                <a href="{{ route('demandes-epaves.show', $similaire) }}"
                                                   class="btn btn-sm btn-outline-primary w-100">
                                                    Voir
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Colonne latérale -->
            <div class="col-lg-4">
                <!-- Prix et statut -->
                <div class="card shadow-sm mb-4 sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <!-- Badges -->
                        <div class="mb-3">
                        <span class="badge {{ $demandeEpave->type_badge_class }}">
                            <i class="fas fa-{{ $demandeEpave->type === 'vehicule' ? 'car' : 'car-crash' }}"></i>
                            {{ $demandeEpave->type_libelle }}
                        </span>
                            <span class="badge {{ $demandeEpave->statut_badge_class }}">
                            {{ ucfirst($demandeEpave->statut) }}
                        </span>
                        </div>

                        <!-- Titre -->
                        <h3 class="mb-3">{{ $demandeEpave->nom_complet }}</h3>

                        <!-- Prix -->
                        <div class="text-center py-3 mb-3" style="background-color: #f8f9fa; border-radius: 10px;">
                            <small class="text-muted d-block mb-1">Prix</small>
                            <h2 class="text-success mb-0">
                                {{ number_format($demandeEpave->prix_souhaite, 0, ',', ' ') }} FCFA
                            </h2>
                        </div>

                        <!-- Actions -->
                        @if(Auth::id() === $demandeEpave->user_id)
                            <!-- Mes propres annonces -->
                            <div class="d-grid gap-2">
                                <a href="{{ route('demandes-epaves.edit', $demandeEpave) }}"
                                   class="btn btn-warning">
                                    <i class="fas fa-edit"></i> Modifier l'annonce
                                </a>
                                <form action="{{ route('demandes-epaves.destroy', $demandeEpave) }}"
                                      method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="btn btn-outline-danger w-100"
                                            onclick="return confirm('Supprimer cette annonce ?')">
                                        <i class="fas fa-trash"></i> Supprimer
                                    </button>
                                </form>
                            </div>
                        @else
                            <!-- Annonces des autres -->
                            @if($peutAjouterPanier)
                                <form action="{{ route('panier-vehicule.add', $demandeEpave) }}" method="POST">
                                    @csrf
                                    <div class="d-grid">
                                        <button type="submit" class="btn btn-success btn-lg">
                                            <i class="fas fa-shopping-cart"></i> Ajouter au panier
                                        </button>
                                    </div>
                                </form>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    @if($demandeEpave->statut === 'vendu')
                                        Ce véhicule a été vendu.
                                    @elseif($demandeEpave->statut === 'reserve')
                                        Ce véhicule est réservé.
                                    @elseif($demandeEpave->quantite <= 0)
                                        Stock épuisé.
                                    @else
                                        Non disponible.
                                    @endif
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                <!-- Informations vendeur -->
                @if($demandeEpave->vendeur)
                    <div class="card shadow-sm">
                        <div class="card-header">
                            <h5 class="mb-0"><i class="fas fa-user"></i> Vendeur</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-2">
                                <strong>Nom:</strong><br>
                                {{ $demandeEpave->vendeur->name }}
                            </p>
                            @if($demandeEpave->telephone_contact)
                                <p class="mb-2">
                                    <strong>Téléphone:</strong><br>
                                    <a href="tel:{{ $demandeEpave->telephone_contact }}">
                                        {{ $demandeEpave->telephone_contact }}
                                    </a>
                                </p>
                            @endif
                            @if($demandeEpave->adresse)
                                <p class="mb-0">
                                    <strong>Adresse:</strong><br>
                                    {{ $demandeEpave->adresse }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
