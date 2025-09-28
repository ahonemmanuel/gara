@extends('layouts.app')

@section('title', 'Commande ' . $commande->numero_commande)

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Commande {{ $commande->numero_commande }}</h1>
            <a href="{{ route('commandes.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Détails de la commande -->
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0">Détails de la commande</h5>
                        <span class="badge bg-{{ $commande->statut === 'livree' ? 'success' :
                                           ($commande->statut === 'annulee' ? 'danger' :
                                           ($commande->statut === 'en_attente' ? 'warning' : 'info')) }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                    </span>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                <tr>
                                    <th>Pièce</th>
                                    <th>Casse</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($commande->items as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->piece->nom }}</strong><br>
                                            <small class="text-muted">
                                                {{ $item->piece->vehicle->marque }} {{ $item->piece->vehicle->modele }}
                                            </small>
                                        </td>
                                        <td>{{ $item->piece->vehicle->casse->nom_entreprise }}</td>
                                        <td>{{ number_format($item->prix_unitaire, 2, ',', ' ') }} €</td>
                                        <td>{{ $item->quantite }}</td>
                                        <td>{{ number_format($item->quantite * $item->prix_unitaire, 2, ',', ' ') }} €</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                    <td><strong>{{ number_format($commande->total, 2, ',', ' ') }} €</strong></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Informations de livraison -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="m-0">Informations de livraison</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Adresse de livraison:</h6>
                                <p class="text-muted">{{ $commande->adresse_livraison }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Téléphone:</h6>
                                <p class="text-muted">{{ $commande->telephone_livraison }}</p>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <h6>Mode de paiement:</h6>
                                <p class="text-muted">{{ ucfirst(str_replace('_', ' ', $commande->mode_paiement)) }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6>Statut du paiement:</h6>
                                <p class="text-muted">
                                <span class="badge bg-{{ $commande->statut_paiement === 'paye' ? 'success' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $commande->statut_paiement)) }}
                                </span>
                                </p>
                            </div>
                        </div>
                        @if($commande->notes)
                            <div class="row">
                                <div class="col-12">
                                    <h6>Notes:</h6>
                                    <p class="text-muted">{{ $commande->notes }}</p>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0">Actions</h6>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->isClient() && $commande->statut === 'en_attente')
                            <form action="{{ route('commandes.annuler', $commande) }}" method="POST" class="d-grid">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-outline-danger"
                                        onclick="return confirm('Annuler cette commande ?')">
                                    <i class="fas fa-times"></i> Annuler la commande
                                </button>
                            </form>
                        @endif

                        @if(auth()->user()->isCasse())
                            <!-- Gestion du statut pour la casse -->
                            <form action="{{ route('commandes.update-statut', $commande) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <div class="mb-3">
                                    <label for="statut" class="form-label">Changer le statut</label>
                                    <select class="form-select" id="statut" name="statut" required>
                                        <option value="en_attente" {{ $commande->statut === 'en_attente' ? 'selected' : '' }}>En attente</option>
                                        <option value="confirmee" {{ $commande->statut === 'confirmee' ? 'selected' : '' }}>Confirmée</option>
                                        <option value="en_preparation" {{ $commande->statut === 'en_preparation' ? 'selected' : '' }}>En préparation</option>
                                        <option value="expedie" {{ $commande->statut === 'expedie' ? 'selected' : '' }}>Expédiée</option>
                                        <option value="livree" {{ $commande->statut === 'livree' ? 'selected' : '' }}>Livrée</option>
                                        <option value="annulee" {{ $commande->statut === 'annulee' ? 'selected' : '' }}>Annulée</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Mettre à jour</button>
                            </form>
                        @endif
                    </div>
                </div>

                <!-- Informations client -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0">Informations client</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $commande->user->name }}</strong></p>
                        <p class="mb-1 text-muted small">{{ $commande->user->email }}</p>
                        <p class="mb-0 text-muted small">{{ $commande->user->telephone }}</p>
                    </div>
                </div>

                <!-- Historique des statuts -->
                <div class="card shadow mt-4">
                    <div class="card-header">
                        <h6 class="m-0">Historique</h6>
                    </div>
                    <div class="card-body">
                        <div class="small">
                            <p class="mb-1"><strong>Créée le:</strong> {{ $commande->created_at->format('d/m/Y H:i') }}</p>
                            <p class="mb-0"><strong>Dernière modification:</strong> {{ $commande->updated_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
