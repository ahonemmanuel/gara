{{-- Créer cette vue: resources/views/demandes-epaves/debug.blade.php --}}
@extends('layouts.app')

@section('title', 'Debug Demandes Épaves')

@section('content')
    <div class="container-fluid">
        <h1 class="mb-4">🔍 Debug - Demandes d'épaves/véhicules</h1>

        {{-- Statistiques générales --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h3>{{ $stats['total'] }}</h3>
                        <p class="mb-0">Total annonces</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h3>{{ $stats['disponibles'] }}</h3>
                        <p class="mb-0">Disponibles</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-warning text-white">
                    <div class="card-body">
                        <h3>{{ $stats['reserves'] }}</h3>
                        <p class="mb-0">Réservées</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card bg-danger text-white">
                    <div class="card-body">
                        <h3>{{ $stats['vendues'] }}</h3>
                        <p class="mb-0">Vendues</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Structure de la table --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>📋 Structure de la table demandes_epaves</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                        <tr>
                            <th>Colonne</th>
                            <th>Type</th>
                            <th>Null</th>
                            <th>Default</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($tableStructure as $column)
                            <tr>
                                <td><code>{{ $column->Field }}</code></td>
                                <td>{{ $column->Type }}</td>
                                <td>{{ $column->Null }}</td>
                                <td>{{ $column->Default ?? 'NULL' }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Toutes les annonces --}}
        <div class="card mb-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>📦 Toutes les annonces ({{ $allDemandes->count() }})</h5>
                <div>
                    <span class="badge bg-info">User connecté: {{ auth()->user()->id }}</span>
                </div>
            </div>
            <div class="card-body">
                @if($allDemandes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover table-sm">
                            <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>User ID</th>
                                <th>Type</th>
                                <th>Véhicule</th>
                                <th>Prix</th>
                                <th>Statut</th>
                                <th>Disponible</th>
                                <th>Quantité</th>
                                <th>Créé le</th>
                                <th>Visible?</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($allDemandes as $demande)
                                @php
                                    $isOwn = $demande->user_id === auth()->id();
                                    $isVisible = $demande->disponible &&
                                                 $demande->statut === 'disponible' &&
                                                 !$isOwn;
                                @endphp
                                <tr class="{{ $isOwn ? 'table-warning' : ($isVisible ? 'table-success' : 'table-secondary') }}">
                                    <td>{{ $demande->id }}</td>
                                    <td>
                                        {{ $demande->user_id }}
                                        @if($isOwn)
                                            <span class="badge bg-warning">MOI</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $demande->type === 'vehicule' ? 'primary' : 'danger' }}">
                                            {{ $demande->type }}
                                        </span>
                                    </td>
                                    <td>
                                        <strong>{{ $demande->marque }} {{ $demande->modele }}</strong><br>
                                        <small class="text-muted">{{ $demande->annee }}</small>
                                    </td>
                                    <td>
                                        @if($demande->prix_souhaite)
                                            {{ number_format($demande->prix_souhaite, 0, ',', ' ') }} FCFA
                                        @else
                                            <span class="text-danger">NULL</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-{{ $demande->statut === 'disponible' ? 'success' : 'secondary' }}">
                                            {{ $demande->statut }}
                                        </span>
                                    </td>
                                    <td>
                                        @if($demande->disponible)
                                            <i class="fas fa-check-circle text-success"></i> OUI
                                        @else
                                            <i class="fas fa-times-circle text-danger"></i> NON
                                        @endif
                                    </td>
                                    <td>{{ $demande->quantite ?? 'NULL' }}</td>
                                    <td>{{ $demande->created_at->format('d/m/Y H:i') }}</td>
                                    <td>
                                        @if($isVisible)
                                            <span class="badge bg-success">
                                                <i class="fas fa-eye"></i> VISIBLE
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">
                                                <i class="fas fa-eye-slash"></i> MASQUÉ
                                            </span>
                                            <br>
                                            <small class="text-muted">
                                                @if($isOwn)
                                                    (Ma propre annonce)
                                                @elseif(!$demande->disponible)
                                                    (disponible=false)
                                                @elseif($demande->statut !== 'disponible')
                                                    (statut={{ $demande->statut }})
                                                @endif
                                            </small>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        Aucune annonce dans la base de données !
                    </div>
                @endif
            </div>
        </div>

        {{-- Requêtes SQL --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5>💾 Requêtes SQL pour debug</h5>
            </div>
            <div class="card-body">
                <h6>Voir toutes les annonces :</h6>
                <pre><code>SELECT * FROM demandes_epaves ORDER BY created_at DESC;</code></pre>

                <h6 class="mt-3">Voir les annonces disponibles :</h6>
                <pre><code>SELECT * FROM demandes_epaves
WHERE disponible = 1
AND statut = 'disponible'
ORDER BY created_at DESC;</code></pre>

                <h6 class="mt-3">Voir les annonces des autres :</h6>
                <pre><code>SELECT * FROM demandes_epaves
WHERE user_id != {{ auth()->id() }}
AND disponible = 1
AND statut = 'disponible'
ORDER BY created_at DESC;</code></pre>

                <h6 class="mt-3">Vérifier la structure :</h6>
                <pre><code>DESCRIBE demandes_epaves;</code></pre>
            </div>
        </div>

        {{-- Actions rapides --}}
        <div class="card">
            <div class="card-header">
                <h5>⚡ Actions rapides</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <h6>Créer une annonce de test</h6>
                        <form action="{{ route('demandes-epaves.debug.create-test') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-plus"></i> Créer annonce test
                            </button>
                        </form>
                    </div>

                    <div class="col-md-4 mb-3">
                        <h6>Rendre toutes disponibles</h6>
                        <form action="{{ route('demandes-epaves.debug.make-available') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100"
                                    onclick="return confirm('Rendre toutes les annonces disponibles?')">
                                <i class="fas fa-check"></i> Rendre disponibles
                            </button>
                        </form>
                    </div>

                    <div class="col-md-4 mb-3">
                        <h6>Nettoyer tout</h6>
                        <form action="{{ route('demandes-epaves.debug.truncate') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-danger w-100"
                                    onclick="return confirm('ATTENTION: Cela va supprimer TOUTES les annonces!')">
                                <i class="fas fa-trash"></i> Supprimer tout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4">
            <a href="{{ route('demandes-epaves.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour aux annonces
            </a>
        </div>
    </div>
@endsection
