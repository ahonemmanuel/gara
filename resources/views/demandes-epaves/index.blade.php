@extends('layouts.app')

@section('title', 'Demandes d\'épaves')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>
                @if(auth()->user()->isCasse())
                    Demandes d'épaves disponibles
                @else
                    Mes demandes d'épaves
                @endif
            </h1>

            @if(auth()->user()->isClient())
                <a href="{{ route('demandes-epaves.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nouvelle demande
                </a>
            @endif
        </div>

        <!-- Filtres pour les casses -->
        @if(auth()->user()->isCasse())
            <div class="card shadow mb-4">
                <div class="card-body">
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <input type="text" name="marque" class="form-control" placeholder="Marque..." value="{{ request('marque') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="number" name="prix_max" class="form-control" placeholder="Prix max..." value="{{ request('prix_max') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                        </div>
                        <div class="col-md-2">
                            <a href="{{ route('demandes-epaves.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                        </div>
                    </form>
                </div>
            </div>
        @endif

        <!-- Liste des demandes -->
        <div class="card shadow">
            <div class="card-body">
                @if($demandes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Véhicule</th>
                                <th>Propriétaire</th>
                                <th>Prix souhaité</th>
                                <th>État</th>
                                <th>Offres</th>
                                <th>Statut</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($demandes as $demande)
                                <tr>
                                    <td>
                                        <strong>{{ $demande->marque }} {{ $demande->modele }}</strong><br>
                                        <small class="text-muted">
                                            {{ $demande->annee }} • {{ $demande->carburant }} • {{ number_format($demande->kilometrage, 0, ',', ' ') }} km
                                        </small>
                                    </td>
                                    <td>{{ $demande->user->name }}</td>
                                    <td>
                                        @if($demande->prix_souhaite)
                                            <strong>{{ number_format($demande->prix_souhaite, 0, ',', ' ') }} €</strong>
                                        @else
                                            <span class="text-muted">Non spécifié</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">{{ ucfirst($demande->etat) }}</span>
                                    </td>
                                    <td>
                                    <span class="badge bg-{{ $demande->offres->count() > 0 ? 'success' : 'secondary' }}">
                                        {{ $demande->offres->count() }} offre(s)
                                    </span>
                                    </td>
                                    <td>
                                    <span class="badge {{ $demande->statut_badge_class }}">
                                        {{ ucfirst(str_replace('_', ' ', $demande->statut)) }}
                                    </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            @if(auth()->user()->role->value === 'client')

                                                <a href="{{ route('demandes-epaves.show', $demande) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i> Détailss
                                                </a>
                                            @endif


                                            @if(auth()->user()->isCasse() && $demande->statut === 'en_attente')
                                                @if($demande->hasOffreFrom(auth()->id()))
                                                    <span class="btn btn-sm btn-success">Offre faite</span>
                                                @else
                                                    <a href="{{ route('demandes-epaves.show', $demande) }}#faire-offre"
                                                       class="btn btn-sm btn-warning">
                                                        <i class="fas fa-gavel"></i> Faire offre
                                                    </a>
                                                @endif
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
                        {{ $demandes->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        @if(auth()->user()->isCasse())
                            <i class="fas fa-search fa-4x text-muted mb-3"></i>
                            <h4>Aucune demande d'épave disponible</h4>
                            <p class="text-muted">Aucune demande d'épave n'est actuellement disponible</p>
                        @else
                            <i class="fas fa-car-crash fa-4x text-muted mb-3"></i>
                            <h4>Aucune demande d'épave</h4>
                            <p class="text-muted">Vous n'avez pas encore créé de demande de vente d'épave</p>
                            <a href="{{ route('demandes-epaves.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Créer une demande
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
