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
                {{-- Récapitulatif de la commande --}}
                <div class="card shadow mb-4">
                    <div class="card-header"><h5 class="m-0">Récapitulatif de la commande</h5></div>
                    <div class="card-body">
                        @if($panier && $panier->items->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                    <tr>
                                        <th>Article</th>
                                        <th>Type</th>
                                        <th>Prix unitaire</th>
                                        <th>Quantité</th>
                                        <th>Total</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($panier->items as $item)
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    @if($item->photo)
                                                        <img src="{{ $item->photo }}"
                                                             alt="{{ $item->nom }}"
                                                             class="rounded me-2"
                                                             style="width: 50px; height: 50px; object-fit: cover;">
                                                    @endif
                                                    <div>
                                                        <strong>{{ $item->nom }}</strong><br>
                                                        <small class="text-muted">
                                                            @if($item->vendeur)
                                                                <i class="fas fa-user"></i> {{ $item->vendeur->name }}
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
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
                                            <td>{{ number_format($item->prix, 0, ',', ' ') }} FCFA</td>
                                            <td>{{ $item->quantite }}</td>
                                            <td>{{ number_format($item->quantite * $item->prix, 0, ',', ' ') }} FCFA</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td colspan="4" class="text-end"><strong>Total:</strong></td>
                                        <td><strong>{{ number_format($panier->getTotal(), 0, ',', ' ') }} FCFA</strong></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>

                            {{-- Résumé par type --}}
                            <div class="alert alert-info mt-3">
                                <h6><i class="fas fa-info-circle"></i> Composition de votre commande</h6>
                                <ul class="mb-0">
                                    @php
                                        $pieces = $panier->items->filter(fn($item) => $item->estPiece());
                                        $vehicules = $panier->items->filter(fn($item) => $item->estVehicule());
                                    @endphp

                                    @if($pieces->count() > 0)
                                        <li>
                                            <strong>{{ $pieces->count() }} pièce(s) détachée(s)</strong>
                                            - Total: {{ number_format($pieces->sum('sous_total'), 0, ',', ' ') }} FCFA
                                        </li>
                                    @endif

                                    @if($vehicules->count() > 0)
                                        <li>
                                            <strong>{{ $vehicules->count() }} véhicule(s)</strong>
                                            - Total: {{ number_format($vehicules->sum('sous_total'), 0, ',', ' ') }} FCFA
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        @else
                            <div class="alert alert-warning">Votre panier est vide.</div>
                        @endif
                    </div>
                </div>

                {{-- Informations de livraison --}}
                <div class="card shadow">
                    <div class="card-header"><h5 class="m-0">Informations de livraison</h5></div>
                    <div class="card-body">
                        <form action="{{ route('commandes.store') }}" method="POST" id="commandeForm">
                            @csrf

                            <div class="mb-3">
                                <label for="adresse_livraison" class="form-label">
                                    Adresse de livraison *
                                    <small class="text-muted">(Indiquez votre adresse complète)</small>
                                </label>
                                <textarea class="form-control @error('adresse_livraison') is-invalid @enderror"
                                          id="adresse_livraison"
                                          name="adresse_livraison"
                                          rows="3"
                                          required>{{ old('adresse_livraison', auth()->user()->adresse ?? '') }}</textarea>
                                @error('adresse_livraison')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="telephone_livraison" class="form-label">Téléphone de livraison *</label>
                                <input type="text"
                                       class="form-control @error('telephone_livraison') is-invalid @enderror"
                                       id="telephone_livraison"
                                       name="telephone_livraison"
                                       required
                                       value="{{ old('telephone_livraison', auth()->user()->telephone ?? '') }}">
                                @error('telephone_livraison')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt"></i> Position GPS (optionnel)
                                </label>
                                <div>
                                    <button type="button" class="btn btn-outline-primary" onclick="openGeoPicker()">
                                        <i class="fas fa-crosshairs"></i> Utiliser ma position actuelle
                                    </button>
                                    <small class="d-block text-muted mt-2">
                                        Cela nous aidera à vous livrer plus facilement
                                    </small>
                                </div>
                                <div id="geoInfo" class="alert alert-success mt-2 d-none">
                                    <i class="fas fa-check-circle"></i> Position GPS enregistrée
                                </div>
                            </div>

                            <input type="hidden" name="latitude" id="latitude">
                            <input type="hidden" name="longitude" id="longitude">

                            <div class="mb-3">
                                <label for="mode_paiement" class="form-label">Mode de paiement *</label>
                                <select class="form-select @error('mode_paiement') is-invalid @enderror"
                                        id="mode_paiement"
                                        name="mode_paiement"
                                        required>
                                    <option value="">-- Sélectionnez un mode de paiement --</option>
                                    <option value="carte_bancaire" {{ old('mode_paiement') == 'carte_bancaire' ? 'selected' : '' }}>
                                        Carte bancaire
                                    </option>
                                    <option value="paypal" {{ old('mode_paiement') == 'paypal' ? 'selected' : '' }}>
                                        PayPal
                                    </option>
                                    <option value="virement" {{ old('mode_paiement') == 'virement' ? 'selected' : '' }}>
                                        Virement bancaire
                                    </option>
                                    <option value="especes" {{ old('mode_paiement') == 'especes' ? 'selected' : '' }}>
                                        Espèces (à la livraison)
                                    </option>
                                </select>
                                @error('mode_paiement')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">
                                    Notes / Instructions particulières (optionnel)
                                </label>
                                <textarea class="form-control"
                                          id="notes"
                                          name="notes"
                                          rows="3"
                                          placeholder="Ex: Sonnez 2 fois, livraison après 18h, etc.">{{ old('notes') }}</textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit"
                                        class="btn btn-success btn-lg"
                                    {{ !$panier || $panier->items->count() === 0 ? 'disabled' : '' }}>
                                    <i class="fas fa-check-circle"></i> Confirmer la commande
                                </button>
                                <a href="{{ route('panier.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-arrow-left"></i> Retour au panier
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Colonne latérale - Résumé --}}
            <div class="col-lg-4">
                <div class="card shadow sticky-top" style="top: 20px;">
                    <div class="card-header bg-primary text-white">
                        <h5 class="m-0"><i class="fas fa-shopping-cart"></i> Résumé</h5>
                    </div>
                    <div class="card-body">
                        @if($panier && $panier->items->count() > 0)
                            @php
                                $pieces = $panier->items->filter(fn($item) => $item->estPiece());
                                $vehicules = $panier->items->filter(fn($item) => $item->estVehicule());
                            @endphp

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Nombre d'articles:</span>
                                    <strong>{{ $panier->items->count() }}</strong>
                                </div>

                                @if($pieces->count() > 0)
                                    <div class="d-flex justify-content-between mb-2 text-muted">
                                        <small>
                                            <i class="fas fa-cog"></i> Pièces ({{ $pieces->count() }})
                                        </small>
                                        <small>{{ number_format($pieces->sum('sous_total'), 0, ',', ' ') }} FCFA</small>
                                    </div>
                                @endif

                                @if($vehicules->count() > 0)
                                    <div class="d-flex justify-content-between mb-2 text-muted">
                                        <small>
                                            <i class="fas fa-car"></i> Véhicules ({{ $vehicules->count() }})
                                        </small>
                                        <small>{{ number_format($vehicules->sum('sous_total'), 0, ',', ' ') }} FCFA</small>
                                    </div>
                                @endif
                            </div>

                            <hr>

                            <div class="d-flex justify-content-between mb-3">
                                <h5>Total à payer</h5>
                                <h5 class="text-success">
                                    {{ number_format($panier->getTotal(), 0, ',', ' ') }} FCFA
                                </h5>
                            </div>

                            <div class="alert alert-info mb-0">
                                <small>
                                    <i class="fas fa-info-circle"></i>
                                    Les frais de livraison seront calculés en fonction de votre adresse
                                </small>
                            </div>
                        @else
                            <div class="alert alert-warning mb-0">
                                Votre panier est vide
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function openGeoPicker() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(pos => {
                    const lat = pos.coords.latitude;
                    const lon = pos.coords.longitude;

                    document.getElementById('latitude').value = lat;
                    document.getElementById('longitude').value = lon;

                    // Afficher l'info de succès
                    document.getElementById('geoInfo').classList.remove('d-none');

                    // Ajouter la position à l'adresse (optionnel)
                    const adresseField = document.getElementById('adresse_livraison');
                    if (adresseField.value.trim()) {
                        adresseField.value += `\n\n[Position GPS: ${lat.toFixed(6)}, ${lon.toFixed(6)}]`;
                    }

                }, err => {
                    alert('Impossible de récupérer votre position: ' + err.message);
                });
            } else {
                alert("Géolocalisation non supportée par votre navigateur.");
            }
        }
    </script>
@endsection
