@extends('layouts.app')

@section('title', 'Gestion des stocks')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Gestion des stocks</h1>
            <div class="btn-group">
                <a href="{{ route('vehicles.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Ajouter un véhicule
                </a>
                <a href="{{ route('pieces.create') }}" class="btn btn-success">
                    <i class="fas fa-cog"></i> Ajouter une pièce
                </a>
            </div>
        </div>

        <!-- Alertes stock faible -->
        @php
            $piecesFaibles = auth()->user()->pieces()->where('quantite', '<', 5)->where('quantite', '>', 0)->get();
            $piecesRupture = auth()->user()->pieces()->where('quantite', 0)->get();
        @endphp

        @if($piecesFaibles->count() > 0)
            <div class="alert alert-warning">
                <h5><i class="fas fa-exclamation-triangle"></i> Stock faible</h5>
                <p class="mb-0">Vous avez {{ $piecesFaibles->count() }} pièce(s) avec un stock faible (< 5 unités)</p>
            </div>
        @endif

        @if($piecesRupture->count() > 0)
            <div class="alert alert-danger">
                <h5><i class="fas fa-times-circle"></i> Rupture de stock</h5>
                <p class="mb-0">Vous avez {{ $piecesRupture->count() }} pièce(s) en rupture de stock</p>
            </div>
        @endif

        <div class="row">
            <!-- Statistiques rapides -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Véhicules en stock</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ auth()->user()->vehicles()->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-car fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Pièces disponibles</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ auth()->user()->pieces()->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cog fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Stock faible</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $piecesFaibles->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Rupture de stock</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $piecesRupture->count() }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pièces en stock faible -->
        @if($piecesFaibles->count() > 0)
            <div class="card shadow mb-4">
                <div class="card-header bg-warning text-dark">
                    <h6 class="m-0 font-weight-bold"><i class="fas fa-exclamation-triangle me-2"></i>Pièces en stock faible</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                            <tr>
                                <th>Pièce</th>
                                <th>Véhicule</th>
                                <th>Stock actuel</th>
                                <th>Prix</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($piecesFaibles as $piece)
                                <tr>
                                    <td>
                                        <strong>{{ $piece->nom }}</strong><br>
                                        <small class="text-muted">{{ $piece->reference_constructeur }}</small>
                                    </td>
                                    <td>{{ $piece->vehicle->marque }} {{ $piece->vehicle->modele }}</td>
                                    <td>
                                        <span class="badge bg-warning">{{ $piece->quantite }}</span>
                                    </td>
                                    <td>{{ number_format($piece->prix, 2, ',', ' ') }} €</td>
                                    <td>
                                        <a href="{{ route('pieces.edit', $piece) }}" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i> Réapprovisionner
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif

        <!-- Dernières pièces ajoutées -->
        <div class="card shadow">
            <div class="card-header">
                <h6 class="m-0 font-weight-bold">Dernières pièces ajoutées</h6>
            </div>

            <div class="card-body">
                @php
                    $recentPieces = auth()->user()->pieces()->with('vehicle')->latest()->take(10)->get();
                @endphp

                @if($recentPieces->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Pièce</th>
                                <th>Véhicule</th>
                                <th>Prix</th>
                                <th>Stock</th>
                                <th>Date d'ajout</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($recentPieces as $piece)
                                <tr>
                                    <td>
                                        <strong>{{ $piece->nom }}</strong><br>
                                        <small class="text-muted">{{ $piece->reference_constructeur }}</small>
                                    </td>
                                    <td>{{ $piece->vehicle->marque }} {{ $piece->vehicle->modele }}</td>
                                    <td>{{ number_format($piece->prix, 2, ',', ' ') }} €</td>
                                    <td>
                                    <span class="badge bg-{{ $piece->quantite > 10 ? 'success' : ($piece->quantite > 0 ? 'warning' : 'danger') }}">
                                        {{ $piece->quantite }}
                                    </span>
                                    </td>
                                    <td>{{ $piece->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('pieces.show', $piece) }}" class="btn btn-outline-primary">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('pieces.edit', $piece) }}" class="btn btn-outline-secondary">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="text-center mt-3">
                        <a href="{{ route('pieces.index') }}" class="btn btn-primary">
                            <i class="fas fa-list"></i> Voir toutes les pièces
                        </a>
                    </div>
                @else
                    <div class="text-center py-4">
                        <i class="fas fa-cog fa-3x text-muted mb-3"></i>
                        <p class="text-muted">Aucune pièce enregistrée</p>
                        <a href="{{ route('pieces.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Ajouter une pièce
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
