@extends('layouts.app')

@section('title', 'Mon Panier')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1><i class="fas fa-shopping-cart"></i> Mon Panier</h1>
            @if($panier && $panier->items->count() > 0)
                <form action="{{ route('panier.clear') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger"
                            onclick="return confirm('Vider tout le panier ?')">
                        <i class="fas fa-trash"></i> Vider le panier
                    </button>
                </form>
            @endif
        </div>

        @if($panier && $panier->items->count() > 0)
            <div class="row">
                <!-- Liste des articles -->
                <div class="col-lg-8">
                    <div class="card shadow">
                        <div class="card-header">
                            <h5 class="m-0">Articles ({{ $panier->items->count() }})</h5>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                    <tr>
                                        <th>Article</th>
                                        <th>Type</th>
                                        <th>Prix unitaire</th>
                                        <th>Quantité</th>
                                        <th>Sous-total</th>
                                        <th>Actions</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($panier->items as $item)
                                        <tr>
                                            <!-- Article -->
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->photo)
                                                        <img src="{{ asset('storage/' . $item->photo) }}"
                                                             alt="{{ $item->nom }}"
                                                             class="rounded me-3"
                                                             style="width: 60px; height: 60px; object-fit: cover;">
                                                    @else
                                                        <div class="bg-secondary rounded me-3 d-flex align-items-center justify-content-center"
                                                             style="width: 60px; height: 60px;">
                                                            <i class="fas fa-{{ $item->estVehicule() ? 'car' : 'cog' }} text-white"></i>
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <strong>{{ $item->nom }}</strong>
                                                        @if($item->vendeur)
                                                            <br>
                                                            <small class="text-muted">
                                                                <i class="fas fa-user"></i> {{ $item->vendeur->name }}
                                                            </small>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- Type -->
                                            <td>
                                                @if($item->estPiece())
                                                    <span class="badge bg-primary">
                                                            <i class="fas fa-cog"></i> Pièce
                                                        </span>
                                                @elseif($item->estVehicule())
                                                    <span class="badge bg-{{ $item->vehicule->type === 'vehicule' ? 'success' : 'danger' }}">
                                                            <i class="fas fa-{{ $item->vehicule->type === 'vehicule' ? 'car' : 'car-crash' }}"></i>
                                                            {{ $item->vehicule->type_libelle }}
                                                        </span>
                                                @endif
                                            </td>

                                            <!-- Prix unitaire -->
                                            <td>
                                                <strong>{{ number_format($item->prix, 0, ',', ' ') }} FCFA</strong>
                                            </td>

                                            <!-- Quantité -->
                                            <td>
                                                @if($item->estPiece())
                                                    <form action="{{ route('panier.update', $item) }}"
                                                          method="POST"
                                                          class="d-flex align-items-center">
                                                        @csrf
                                                        @method('PATCH')
                                                        <input type="number"
                                                               name="quantite"
                                                               value="{{ $item->quantite }}"
                                                               min="1"
                                                               max="{{ $item->piece->quantite }}"
                                                               class="form-control form-control-sm"
                                                               style="width: 70px;"
                                                               onchange="this.form.submit()">
                                                    </form>
                                                @else
                                                    <span class="badge bg-secondary">{{ $item->quantite }}</span>
                                                @endif
                                            </td>

                                            <!-- Sous-total -->
                                            <td>
                                                <strong class="text-success">
                                                    {{ number_format($item->sous_total, 0, ',', ' ') }} FCFA
                                                </strong>
                                            </td>

                                            <!-- Actions -->
                                            <td>
                                                @if($item->estPiece())
                                                    <form action="{{ route('panier.remove', $item) }}"
                                                          method="POST"
                                                          class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Retirer cet article ?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form action="{{ route('panier-vehicule.remove', $item) }}"
                                                          method="POST"
                                                          class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="btn btn-sm btn-outline-danger"
                                                                onclick="return confirm('Retirer ce véhicule ?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Informations importantes -->
                    <div class="alert alert-info mt-4">
                        <h6><i class="fas fa-info-circle"></i> Informations importantes</h6>
                        <ul class="mb-0">
                            <li>Les pièces détachées peuvent être commandées en plusieurs quantités</li>
                            <li>Les véhicules et épaves ne peuvent être commandés qu'à l'unité</li>
                            <li>Les articles réservés dans votre panier seront libérés après 24h si la commande n'est pas finalisée</li>
                        </ul>
                    </div>
                </div>

                <!-- Résumé de la commande -->
                <div class="col-lg-4">
                    <div class="card shadow sticky-top" style="top: 20px;">
                        <div class="card-header">
                            <h5 class="m-0">Résumé de la commande</h5>
                        </div>
                        <div class="card-body">
                            <!-- Détails par type -->
                            <div class="mb-3">
                                @php
                                    $pieces = $panier->items->filter(fn($item) => $item->estPiece());
                                    $vehicules = $panier->items->filter(fn($item) => $item->estVehicule());
                                    $totalPieces = $pieces->sum('sous_total');
                                    $totalVehicules = $vehicules->sum('sous_total');
                                @endphp

                                @if($pieces->count() > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>
                                            <i class="fas fa-cog text-primary"></i>
                                            Pièces ({{ $pieces->count() }})
                                        </span>
                                        <strong>{{ number_format($totalPieces, 0, ',', ' ') }} FCFA</strong>
                                    </div>
                                @endif

                                @if($vehicules->count() > 0)
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>
                                            <i class="fas fa-car text-success"></i>
                                            Véhicules ({{ $vehicules->count() }})
                                        </span>
                                        <strong>{{ number_format($totalVehicules, 0, ',', ' ') }} FCFA</strong>
                                    </div>
                                @endif
                            </div>

                            <hr>

                            <!-- Total général -->
                            <div class="d-flex justify-content-between mb-3">
                                <h5>Total</h5>
                                <h5 class="text-success">
                                    {{ number_format($panier->getTotal(), 0, ',', ' ') }} FCFA
                                </h5>
                            </div>

                            <!-- Bouton commander -->
                            <div class="d-grid">
                                <a href="{{ route('commandes.create') }}"
                                   class="btn btn-success btn-lg">
                                    <i class="fas fa-check-circle"></i>
                                    Passer la commande
                                </a>
                            </div>

                            <div class="d-grid mt-2">
                                <a href="{{ route('pieces.index') }}"
                                   class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    Continuer les achats
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @else
            <!-- Panier vide -->
            <div class="card shadow">
                <div class="card-body text-center py-5">
                    <i class="fas fa-shopping-cart fa-5x text-muted mb-4"></i>
                    <h3>Votre panier est vide</h3>
                    <p class="text-muted mb-4">
                        Parcourez notre catalogue de pièces détachées et de véhicules d'occasion
                    </p>
                    <div class="d-flex justify-content-center gap-3">
                        <a href="{{ route('pieces.index') }}" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Parcourir les pièces
                        </a>
                        <a href="{{ route('demandes-epaves.index', ['tab' => 'disponibles']) }}"
                           class="btn btn-success">
                            <i class="fas fa-car"></i> Voir les véhicules
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
