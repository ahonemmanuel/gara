@extends('layouts.app')

@section('title', 'Passer la commande')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Passer la commande</h1>
            <a href="{{ route('panier.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour au panier
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5 class="m-0">Récapitulatif de la commande</h5>
                    </div>
                    <div class="card-body">
                        @if($panier && $panier->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Pièce</th>
                                        <th>Prix unitaire</th>
                                        <th>Quantité</th>
                                        <th>Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($panier->items as $item)
                                        <tr>
                                            <td>
                                                <strong>{{ $item->piece->nom }}</strong><br>
                                                <small class="text-muted">
                                                    {{ $item->piece->vehicle->marque }} {{ $item->piece->vehicle->modele }} •
                                                    {{ $item->piece->vehicle->casse->nom_entreprise }}
                                                </small>
                                            </td>
                                            <td>{{ number_format($item->piece->prix, 2, ',', ' ') }} €</td>
                                            <td>{{ $item->quantite }}</td>
                                            <td>{{ number_format($item->quantite * $item->piece->prix, 2, ',', ' ') }} €</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>{{ number_format($panier->getTotal(), 2, ',', ' ') }} €</strong></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        @else
                            <div class="alert alert-warning">
                                Votre panier est vide.
                            </div>
                        @endif
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="m-0">Informations de livraison</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('commandes.store') }}" method="POST">
                            @csrf

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="adresse_livraison" class="form-label">Adresse de livraison *</label>
                                        <textarea class="form-control" id="adresse_livraison" name="adresse_livraison" rows="3" required>{{ old('adresse_livraison', auth()->user()->adresse) }}</textarea>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="telephone_livraison" class="form-label">Téléphone de livraison *</label>
                                        <input type="text" class="form-control" id="telephone_livraison" name="telephone_livraison" required value="{{ old('telephone_livraison', auth()->user()->telephone) }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="mode_paiement" class="form-label">Mode de paiement *</label>
                                        <select class="form-select" id="mode_paiement" name="mode_paiement" required>
                                            <option value="carte_bancaire">Carte bancaire</option>
                                            <option value="paypal">PayPal</option>
                                            <option value="virement">Virement bancaire</option>
                                            <option value="especes">Espèces (à la livraison)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Notes (optionnel)</label>
                                <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Instructions spéciales...">{{ old('notes') }}</textarea>
                            </div>

                            <div class="alert alert-info">
                                <h6><i class="fas fa-info-circle"></i> Information importante</h6>
                                <p class="mb-0">
                                    Après confirmation de votre commande, vous serez contacté par les casses pour finaliser
                                    les détails de livraison et de paiement.
                                </p>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg"
                                {{ !$panier || $panier->items->count() === 0 ? 'disabled' : '' }}>
                                <i class="fas fa-credit-card"></i> Confirmer la commande
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Récapitulatif -->
                <div class="card shadow">
                    <div class="card-header">
                        <h5 class="m-0">Résumé</h5>
                    </div>
                    <div class="card-body">
                        @if($panier && $panier->items->count() > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span>Sous-total:</span>
                                <span>{{ number_format($panier->getTotal(), 2, ',', ' ') }} €</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Frais de livraison:</span>
                                <span>À déterminer</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Total estimé:</strong>
                                <strong>{{ number_format($panier->getTotal(), 2, ',', ' ') }} €</strong>
                            </div>

                            <div class="small text-muted">
                                <i class="fas fa-info-circle"></i> Les frais de livraison seront confirmés par les casses.
                            </div>
                        @else
                            <div class="text-center text-muted">
                                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                <p>Votre panier est vide</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informations client -->
                <div class="card shadow mt-4">
                    <div class="card-header">
                        <h5 class="m-0">Informations client</h5>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ auth()->user()->name }}</strong></p>
                        <p class="mb-1 text-muted small">{{ auth()->user()->email }}</p>
                        <p class="mb-1 text-muted small">{{ auth()->user()->telephone }}</p>
                        <p class="mb-0 text-muted small">{{ auth()->user()->adresse }}</p>
                        <p class="mb-0 text-muted small">{{ auth()->user()->code_postal }} {{ auth()->user()->ville }}</p>

                        <a href="{{ route('profile.edit') }}" class="btn btn-outline-primary btn-sm mt-2">
                            <i class="fas fa-edit"></i> Modifier
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
