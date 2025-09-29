@extends('layouts.app')

@section('title', 'Gestion des pièces détachées')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                @if(auth()->user()->role->value === 'casse')
                    Gestion des pièces détachées
                @else
                    Rechercher des pièces détachées
                @endif
            </h1>
            @if(auth()->user()->role->value === 'casse')
                <a href="{{ route('pieces.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter une pièce
                </a>
            @endif
        </div>

        <!-- Filtres -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Nom de la pièce..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-2">
                        <select name="marque" class="form-select">
                            <option value="">Toutes marques</option>
                            @foreach($marques as $marque)
                                <option value="{{ $marque }}" {{ request('marque') == $marque ? 'selected' : '' }}>{{ $marque }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="etat" class="form-select">
                            <option value="">Tous états</option>
                            <option value="neuf" {{ request('etat') == 'neuf' ? 'selected' : '' }}>Neuf</option>
                            <option value="tres_bon" {{ request('etat') == 'tres_bon' ? 'selected' : '' }}>Très bon</option>
                            <option value="bon" {{ request('etat') == 'bon' ? 'selected' : '' }}>Bon</option>
                            <option value="moyen" {{ request('etat') == 'moyen' ? 'selected' : '' }}>Moyen</option>
                            <option value="usage" {{ request('etat') == 'usage' ? 'selected' : '' }}>Usage</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('pieces.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des pièces -->
        <div class="card shadow">
            <div class="card-body">
                @if($pieces->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Photo</th>
                                <th>Pièce</th>
                                <th>Véhicule</th>
                                <th>Prix</th>
                                <th>Quantité</th>
                                <th>État</th>
                                <th>Disponible</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($pieces as $piece)
                                <tr>
                                    <td>
                                        @if($piece->photos && count($piece->photos) > 0)
                                            <img src="{{ asset('storage/' . $piece->photos[0]) }}"
                                                 width="60" height="60" style="object-fit: cover;" class="rounded">
                                        @else
                                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-cog text-muted"></i>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $piece->nom }}</strong><br>
                                        @if($piece->reference_constructeur)
                                            <small class="text-muted">Réf: {{ $piece->reference_constructeur }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            @if($piece->vehicle->photo_principale)
                                                <img src="{{ asset('storage/' . $piece->vehicle->photo_principale) }}"
                                                     width="40" height="40" style="object-fit: cover;" class="rounded me-2">
                                            @else
                                                <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                                     style="width: 40px; height: 40px;">
                                                    <i class="fas fa-car text-muted"></i>
                                                </div>
                                            @endif
                                            <div>
                                                <strong>{{ $piece->vehicle->marque }} {{ $piece->vehicle->modele }}</strong><br>
                                                <small class="text-muted">{{ $piece->vehicle->annee }} • {{ $piece->vehicle->numero_plaque }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ number_format($piece->prix, 2, ',', ' ') }} €</td>
                                    <td>
                                        <span class="badge bg-{{ $piece->quantite > 10 ? 'success' : ($piece->quantite > 0 ? 'warning' : 'danger') }}">
                                            {{ $piece->quantite }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst(str_replace('_', ' ', $piece->etat)) }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $piece->disponible ? 'success' : 'danger' }}">
                                            {{ $piece->disponible ? 'Oui' : 'Non' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('pieces.show', $piece) }}" class="btn btn-sm btn-outline-primary" title="Voir">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            @if(auth()->user()->role->value === 'client')
                                                @if($piece->disponible && $piece->quantite > 0)
                                                    <form action="{{ route('panier.add', $piece) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Ajouter au panier">
                                                            <i class="fas fa-cart-plus"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif

                                            @if(auth()->user()->role->value === 'casse')
                                                <a href="{{ route('pieces.edit', $piece) }}" class="btn btn-sm btn-outline-secondary" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <form action="{{ route('pieces.destroy', $piece) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Supprimer cette pièce ?')" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $pieces->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-cog fa-4x text-muted mb-3"></i>
                        <h4>Aucune pièce enregistrée</h4>

                        @if(auth()->user()->role->value === 'casse')
                            <p class="text-muted">Commencez par ajouter votre première pièce</p>
                            <a href="{{ route('pieces.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Ajouter une pièce
                            </a>
                        @else
                            <p class="text-muted">Aucune pièce ne correspond à vos critères de recherche.</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        <!-- Statistiques pour les casses -->
        @if(auth()->user()->role->value === 'casse')
            <div class="row mt-4">
                <div class="col-md-3">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-car fa-2x text-primary mb-2"></i>
                            <h4>{{ auth()->user()->vehicles->count() }}</h4>
                            <p class="text-muted mb-0">Véhicules</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-cog fa-2x text-success mb-2"></i>
                            <h4>{{ auth()->user()->pieces()->count() }}</h4>
                            <p class="text-muted mb-0">Pièces</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-check-circle fa-2x text-info mb-2"></i>
                            <h4>{{ auth()->user()->pieces()->where('disponible', true)->count() }}</h4>
                            <p class="text-muted mb-0">Disponibles</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow">
                        <div class="card-body text-center">
                            <i class="fas fa-boxes fa-2x text-warning mb-2"></i>
                            <h4>{{ auth()->user()->pieces()->sum('quantite') }}</h4>
                            <p class="text-muted mb-0">Stock total</p>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection
