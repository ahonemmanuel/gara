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

        @php
            $itemsSupprimees = $commande->items->filter(function($item) {
                return ($item->estPiece() && is_null($item->piece)) ||
                       ($item->estVehicule() && is_null($item->vehicule));
            });
            $hasItemsSupprimees = $itemsSupprimees->isNotEmpty();
        @endphp

        {{-- Alerte pour articles supprimés --}}
        @if($hasItemsSupprimees)
            <div class="alert alert-warning shadow mb-4">
                <div class="d-flex align-items-center">
                    <i class="fas fa-exclamation-triangle fa-2x me-3"></i>
                    <div>
                        <h5 class="alert-heading mb-1">Attention : Articles indisponibles</h5>
                        <p class="mb-0">
                            Cette commande contient <strong>{{ $itemsSupprimees->count() }} article(s)</strong> qui ne sont plus disponibles dans le catalogue.
                            Ces articles ont été retirés de la vente par le vendeur.
                            @if(in_array($commande->statut, ['en_attente', 'confirmee']))
                                <br><strong>L'annulation automatique de cette commande n'est plus possible.</strong>
                                Veuillez contacter le service client pour obtenir de l'aide.
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                {{-- Détails de la commande --}}
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5>Détails de la commande</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th>Article</th>
                                    <th>Type</th>
                                    <th>Vendeur</th>
                                    <th>Prix unitaire</th>
                                    <th>Quantité</th>
                                    <th>Total</th>
                                    <th>Statut</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($commande->items as $item)
                                    <tr class="{{ (($item->estPiece() && is_null($item->piece)) || ($item->estVehicule() && is_null($item->vehicule))) ? 'table-warning' : '' }}">
                                        <td>
                                            @if($item->estPiece())
                                                @if($item->piece)
                                                    {{ $item->piece->nom }}
                                                @else
                                                    <span class="text-muted fst-italic">
                                                        <i class="fas fa-exclamation-circle text-warning"></i>
                                                        Pièce supprimée du catalogue
                                                    </span>
                                                @endif
                                            @elseif($item->estVehicule())
                                                @if($item->vehicule)
                                                    {{ $item->vehicule->nom_complet }}
                                                @else
                                                    <span class="text-muted fst-italic">
                                                        <i class="fas fa-exclamation-circle text-warning"></i>
                                                        Véhicule supprimé du catalogue
                                                    </span>
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->estPiece())
                                                <span class="badge bg-primary">
                                                    <i class="fas fa-cog"></i> Pièce
                                                </span>
                                            @elseif($item->estVehicule())
                                                <span class="badge bg-success">
                                                    <i class="fas fa-car"></i>
                                                    {{ $item->vehicule ? $item->vehicule->type_libelle : 'Véhicule' }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($item->vendeur)
                                                {{ $item->vendeur->name }}
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($item->prix_unitaire, 2, ',', ' ') }} FCFA</td>
                                        <td>{{ $item->quantite }}</td>
                                        <td>{{ number_format($item->prix_unitaire * $item->quantite, 2, ',', ' ') }} FCFA</td>
                                        <td>
                                            @if(($item->estPiece() && $item->piece) || ($item->estVehicule() && $item->vehicule))
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Disponible
                                                </span>
                                            @else
                                                <span class="badge bg-warning text-dark">
                                                    <i class="fas fa-exclamation-triangle"></i> Indisponible
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td colspan="5" class="text-end"><strong>Total:</strong></td>
                                    <td colspan="2"><strong>{{ number_format($commande->total, 2, ',', ' ') }} FCFA</strong></td>
                                </tr>
                                </tfoot>
                            </table>
                        </div>

                        @if($hasItemsSupprimees)
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fas fa-info-circle"></i>
                                <strong>Note :</strong> Le montant total de la commande reste inchangé,
                                même si certains articles ne sont plus disponibles.
                                Pour toute question ou demande de remboursement, veuillez contacter notre service client.
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Adresse de livraison --}}
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5>Adresse de livraison</h5>
                    </div>
                    <div class="card-body">
                        <p id="adresse-livraison-display">{{ $commande->adresse_livraison }}</p>
                        <p class="text-muted mb-2">
                            <i class="fas fa-phone"></i> Téléphone : <strong>{{ $commande->telephone_livraison }}</strong>
                        </p>

                        @if(auth()->user()->isClient() && $commande->statut === 'en_attente')
                            <hr>
                            <button type="button" class="btn btn-outline-primary" onclick="openGeoPicker()">
                                <i class="fas fa-map-marker-alt"></i> Ajouter ma position actuelle
                            </button>

                            <form id="geoForm" action="{{ route('commandes.update-adresse', $commande) }}" method="POST" class="mt-2 d-none">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="latitude" id="latitude">
                                <input type="hidden" name="longitude" id="longitude">
                                <button type="submit" class="btn btn-primary">Confirmer la localisation</button>
                            </form>
                        @endif
                    </div>
                </div>

                {{-- Informations de paiement --}}
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5>Informations de paiement</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Mode de paiement :</strong></p>
                                <span class="badge bg-info">
                                    {{ ucfirst(str_replace('_', ' ', $commande->mode_paiement)) }}
                                </span>
                            </div>
                            <div class="col-md-6">
                                <p><strong>Statut du paiement :</strong></p>
                                <span class="badge bg-{{ $commande->statut_paiement === 'paye' ? 'success' : 'warning' }}">
                                    {{ ucfirst(str_replace('_', ' ', $commande->statut_paiement)) }}
                                </span>
                            </div>
                        </div>

                        @if($commande->notes)
                            <hr>
                            <p><strong>Notes :</strong></p>
                            <p class="text-muted">{{ $commande->notes }}</p>
                        @endif
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                {{-- Statut de la commande --}}
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h5>Statut de la commande</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            <span class="badge bg-{{
                                $commande->statut === 'livree' ? 'success' :
                                ($commande->statut === 'annulee' ? 'danger' :
                                ($commande->statut === 'en_attente' ? 'warning' : 'info'))
                            }} p-3 fs-5">
                                {{ ucfirst(str_replace('_', ' ', $commande->statut)) }}
                            </span>
                        </div>

                        <div class="timeline">
                            <div class="timeline-item {{ in_array($commande->statut, ['en_attente','confirmee','en_preparation','expedie','livree']) ? 'active' : '' }}">
                                <i class="fas fa-clock"></i> En attente
                            </div>
                            <div class="timeline-item {{ in_array($commande->statut, ['confirmee','en_preparation','expedie','livree']) ? 'active' : '' }}">
                                <i class="fas fa-check"></i> Confirmée
                            </div>
                            <div class="timeline-item {{ in_array($commande->statut, ['en_preparation','expedie','livree']) ? 'active' : '' }}">
                                <i class="fas fa-box"></i> En préparation
                            </div>
                            <div class="timeline-item {{ in_array($commande->statut, ['expedie','livree']) ? 'active' : '' }}">
                                <i class="fas fa-truck"></i> Expédiée
                            </div>
                            <div class="timeline-item {{ $commande->statut === 'livree' ? 'active' : '' }}">
                                <i class="fas fa-home"></i> Livrée
                            </div>
                        </div>

                        @if($commande->statut === 'annulee')
                            <div class="alert alert-danger mt-3">
                                <i class="fas fa-times-circle"></i> Commande annulée
                            </div>
                        @endif

                        {{-- Actions pour les clients --}}
                        @if(auth()->user()->isClient() && in_array($commande->statut, ['en_attente','confirmee']))
                            <hr>
                            @if(!$hasItemsSupprimees)
                                <form action="{{ route('commandes.annuler', $commande) }}" method="POST" onsubmit="return confirm('Annuler cette commande ? Les stocks seront restaurés.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger w-100">
                                        <i class="fas fa-times"></i> Annuler la commande
                                    </button>
                                </form>
                                <small class="text-muted d-block mt-2 text-center">
                                    <i class="fas fa-info-circle"></i> Les stocks seront automatiquement restaurés
                                </small>
                            @else
                                <div class="alert alert-warning mb-0">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <small>
                                        L'annulation n'est plus possible car certains articles ont été retirés du catalogue.
                                        <br><strong>Contactez le service client.</strong>
                                    </small>
                                </div>
                            @endif
                        @endif

                        {{-- Actions pour les casses --}}
                        @if(auth()->user()->isCasse())
                            @php
                                $aDesArticles = false;
                                foreach($commande->items as $item) {
                                    if (($item->estPiece() && $item->piece && $item->piece->user_id === auth()->id()) ||
                                        ($item->estVehicule() && $item->vehicule && $item->vehicule->user_id === auth()->id())) {
                                        $aDesArticles = true;
                                        break;
                                    }
                                }
                            @endphp

                            @if($aDesArticles && $commande->statut !== 'annulee')
                                <hr>
                                <button class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#editStatutModal">
                                    <i class="fas fa-pen"></i> Modifier le statut
                                </button>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Informations --}}
                <div class="card shadow">
                    <div class="card-header">
                        <h5>Informations</h5>
                    </div>
                    <div class="card-body">
                        <p><strong>Date de commande :</strong><br>{{ $commande->created_at->format('d/m/Y à H:i') }}</p>
                        <p><strong>Dernière mise à jour :</strong><br>{{ $commande->updated_at->format('d/m/Y à H:i') }}</p>

                        @if(auth()->user()->isCasse())
                            <hr>
                            <p><strong>Client :</strong><br>{{ $commande->user->name }}</p>
                            <p><strong>Email :</strong><br>{{ $commande->user->email }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal pour modification du statut (uniquement pour casse) --}}
    @if(auth()->user()->isCasse())
        @php
            $pieces = $commande->items->filter(fn($item) => $item->estPiece());
            $vehicules = $commande->items->filter(fn($item) => $item->estVehicule());
        @endphp

        <div class="modal fade" id="editStatutModal" tabindex="-1" aria-labelledby="editStatutLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editStatutLabel">Modifier le statut</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <form action="{{ route('gestion.commandes.update-statut', $commande) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            {{-- Résumé de la commande --}}
                            <div class="alert alert-info mb-3">
                                <strong>Commande {{ $commande->numero_commande }}</strong><br>
                                <small class="text-muted">Client: {{ $commande->user->name }}</small><br>
                                @if($pieces->count() > 0)
                                    <small><i class="fas fa-cog"></i> {{ $pieces->count() }} pièce(s)</small><br>
                                @endif
                                @if($vehicules->count() > 0)
                                    <small><i class="fas fa-car"></i> {{ $vehicules->count() }} véhicule(s)</small>
                                @endif
                            </div>

                            {{-- Articles concernant cette casse --}}
                            <div class="mb-3">
                                <label class="form-label"><strong>Vos articles dans cette commande:</strong></label>
                                <ul class="list-group list-group-flush">
                                    @foreach($commande->items as $item)
                                        @if(($item->estPiece() && $item->piece && $item->piece->user_id === auth()->id()) ||
                                            ($item->estVehicule() && $item->vehicule && $item->vehicule->user_id === auth()->id()))
                                            <li class="list-group-item">
                                                @if($item->estPiece())
                                                    <i class="fas fa-cog text-primary"></i> {{ $item->nom }}
                                                @else
                                                    <i class="fas fa-car text-success"></i> {{ $item->nom }}
                                                @endif
                                                <span class="badge bg-secondary float-end">x{{ $item->quantite }}</span>
                                            </li>
                                        @endif
                                    @endforeach
                                </ul>
                            </div>

                            <div class="mb-3">
                                <label for="statut" class="form-label">Nouveau statut</label>
                                <select id="statut" name="statut" class="form-select" required>
                                    @foreach(['en_attente','confirmee','en_preparation','expedie','livree','annulee'] as $statut)
                                        <option value="{{ $statut }}" {{ $commande->statut === $statut ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $statut)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="commentaire" class="form-label">Commentaire (optionnel)</label>
                                <textarea id="commentaire" name="commentaire" class="form-control" rows="3" placeholder="Ex: Colis prêt pour expédition...">{{ old('commentaire') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Enregistrer
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }
        .timeline-item {
            position: relative;
            padding: 10px 0;
            color: #6c757d;
        }
        .timeline-item:before {
            content: '';
            position: absolute;
            left: -30px;
            top: 50%;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #e9ecef;
            border: 2px solid #dee2e6;
        }
        .timeline-item.active {
            color: #198754;
            font-weight: bold;
        }
        .timeline-item.active:before {
            background: #198754;
            border-color: #198754;
        }
        .timeline-item:not(:last-child):after {
            content: '';
            position: absolute;
            left: -24px;
            top: 50%;
            width: 2px;
            height: 100%;
            background: #dee2e6;
        }
        .timeline-item.active:not(:last-child):after {
            background: #198754;
        }
    </style>

    @push('scripts')
        <script>
            function openGeoPicker() {
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(pos => {
                        const lat = pos.coords.latitude;
                        const lon = pos.coords.longitude;

                        document.getElementById('latitude').value = lat;
                        document.getElementById('longitude').value = lon;

                        document.getElementById('geoForm').classList.remove('d-none');
                        document.getElementById('adresse-livraison-display').textContent = `Lat: ${lat}, Lon: ${lon}`;
                    }, err => {
                        alert('Impossible de récupérer votre position: ' + err.message);
                    });
                } else {
                    alert("Géolocalisation non supportée par votre navigateur.");
                }
            }

            // Initialiser les tooltips Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    @endpush
@endsection
