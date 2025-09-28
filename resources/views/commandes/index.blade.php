@extends('layouts.app')

@section('title', 'Mes commandes')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            @if( auth()->user()->isClient())

            <h1>Mes commandes</h1>
            @endif
            @if( auth()->user()->isCasse())
            <h1> Gestion des commandes </h1>
            @endif
        </div>

        <!-- Filtres -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="en_attente" {{ request('statut') == 'en_attente' ? 'selected' : '' }}>En attente</option>
                            <option value="confirmee" {{ request('statut') == 'confirmee' ? 'selected' : '' }}>Confirmée</option>
                            <option value="en_preparation" {{ request('statut') == 'en_preparation' ? 'selected' : '' }}>En préparation</option>
                            <option value="expedie" {{ request('statut') == 'expedie' ? 'selected' : '' }}>Expédiée</option>
                            <option value="livree" {{ request('statut') == 'livree' ? 'selected' : '' }}>Livrée</option>
                            <option value="annulee" {{ request('statut') == 'annulee' ? 'selected' : '' }}>Annulée</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Filtrer</button>
                    </div>
                    <div class="col-md-2">
                        <a href="{{ route('commandes.index') }}" class="btn btn-secondary w-100">Réinitialiser</a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Liste des commandes -->
        <div class="card shadow">
            <div class="card-body">
                @if($commandes->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>N° Commande</th>
                                <th>Date</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Paiement</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($commandes as $commande)
                                <tr>
                                    <td>
                                        <strong>{{ $commande->numero_commande }}</strong>
                                    </td>
                                    <td>{{ $commande->created_at->format('d/m/Y H:i') }}</td>
                                    <td>{{ number_format($commande->total, 2, ',', ' ') }} €</td>
                                    <td>
                                    <span class="badge bg-{{ $commande->statut === 'livree' ? 'success' :
                                                           ($commande->statut === 'annulee' ? 'danger' :
                                                           ($commande->statut === 'en_attente' ? 'warning' : 'info')) }}">
                                        {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                                    </span>
                                    </td>
                                    <td>
                                    <span class="badge bg-{{ $commande->statut_paiement === 'paye' ? 'success' : 'warning' }}">
                                        {{ ucfirst(str_replace('_', ' ', $commande->statut_paiement)) }}
                                    </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <a href="{{ route('commandes.show', $commande) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Détails
                                            </a>
                                            @if($commande->statut === 'en_attente' && auth()->user()->isClient())
                                                <form action="{{ route('commandes.annuler', $commande) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger"
                                                            onclick="return confirm('Annuler cette commande ?')">
                                                        <i class="fas fa-times"></i> Annuler
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
                        {{ $commandes->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h4>Aucune commande</h4>
                        <p class="text-muted">Vous n'avez pas encore passé de commande</p>
                        <a href="{{ route('pieces.index') }}" class="btn btn-primary">
                            <i class="fas fa-cog"></i> Parcourir les pièces
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
