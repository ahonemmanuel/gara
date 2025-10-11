@extends('layouts.app')

@section('title', auth()->user()->isClient() ? 'Mes commandes' : 'Gestion des commandes')

@section('content')
    <div class="container-fluid">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>{{ auth()->user()->isClient() ? 'Mes commandes' : 'Gestion des commandes' }}</h1>
        </div>

        <!-- Filtres -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            @foreach(['en_attente','confirmee','en_preparation','expedie','livree','annulee'] as $statut)
                                <option value="{{ $statut }}" {{ request('statut') == $statut ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $statut)) }}
                                </option>
                            @endforeach
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
                                <th>Articles</th>
                                <th>Total</th>
                                <th>Statut</th>
                                <th>Paiement</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($commandes as $commande)
                                @php
                                    $pieces = $commande->items->filter(fn($item) => $item->estPiece());
                                    $vehicules = $commande->items->filter(fn($item) => $item->estVehicule());

                                    // Vérifier si la commande concerne l'utilisateur connecté
                                    $concerneUtilisateur = false;

                                    if (auth()->user()->isCasse()) {
                                        // Pour les casses : vérifier si au moins un article leur appartient
                                        foreach($commande->items as $item) {
                                            if ($item->estPiece() && $item->piece && $item->piece->user_id === auth()->id()) {
                                                $concerneUtilisateur = true;
                                                break;
                                            }
                                            if ($item->estVehicule() && $item->vehicule && $item->vehicule->user_id === auth()->id()) {
                                                $concerneUtilisateur = true;
                                                break;
                                            }
                                        }
                                    } else {
                                        // Pour les clients : vérifier si c'est leur commande
                                        $concerneUtilisateur = $commande->user_id === auth()->id();
                                    }
                                @endphp

                                @if($concerneUtilisateur)
                                    <tr>
                                        <td>
                                            <a href="{{ route('commandes.show', $commande) }}">
                                                <strong>{{ $commande->numero_commande }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ $commande->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @if($pieces->count() > 0)
                                                <span class="badge bg-primary">
                                                <i class="fas fa-cog"></i> {{ $pieces->count() }} pièce(s)
                                            </span>
                                            @endif

                                            @if($vehicules->count() > 0)
                                                <span class="badge bg-success">
                                                <i class="fas fa-car"></i> {{ $vehicules->count() }} véhicule(s)
                                            </span>
                                            @endif
                                        </td>
                                        <td>{{ number_format($commande->total, 2, ',', ' ') }} FCFA</td>
                                        <td>
                                        <span class="badge bg-{{
                                            $commande->statut === 'livree' ? 'success' :
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
                                            @if($commande->statut === 'annulee')
                                                <span class="text-danger fw-bold">Commande annulée</span>
                                            @else
                                                <div class="btn-group">
                                                    {{-- ACTIONS POUR LES CASSES --}}
                                                    @if(auth()->user()->isCasse())
                                                        @php
                                                            // Vérifier si la casse a au moins un article dans cette commande
                                                            $aDesArticles = false;
                                                            foreach($commande->items as $item) {
                                                                if (($item->estPiece() && $item->piece && $item->piece->user_id === auth()->id()) ||
                                                                    ($item->estVehicule() && $item->vehicule && $item->vehicule->user_id === auth()->id())) {
                                                                    $aDesArticles = true;
                                                                    break;
                                                                }
                                                            }
                                                        @endphp

                                                        @if($aDesArticles)
                                                            <a href="{{ route('commandes.show', $commande) }}" class="btn btn-sm btn-outline-primary">
                                                                <i class="fas fa-eye"></i> Voir
                                                            </a>
                                                            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editStatutModal{{ $commande->id }}">
                                                                <i class="fas fa-pen"></i> Statut
                                                            </button>
                                                        @endif
                                                    @endif

                                                    {{-- ACTIONS POUR LES CLIENTS --}}
                                                    @if(auth()->user()->isClient() && $commande->user_id === auth()->id())
                                                        <a href="{{ route('commandes.show', $commande) }}" class="btn btn-sm btn-outline-primary">
                                                            <i class="fas fa-eye"></i> Détails
                                                        </a>

                                                        @if(in_array($commande->statut, ['en_attente','confirmee']))
                                                            @php
                                                                $itemsSupprimees = $commande->items->filter(function($item) {
                                                                    return ($item->estPiece() && is_null($item->piece)) ||
                                                                           ($item->estVehicule() && is_null($item->vehicule));
                                                                });
                                                                $peutAnnuler = $itemsSupprimees->isEmpty();
                                                            @endphp

                                                            @if($peutAnnuler)
                                                                <form action="{{ route('commandes.annuler', $commande) }}" method="POST" class="d-inline">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Annuler cette commande ? Les stocks seront restaurés.')">
                                                                        <i class="fas fa-times"></i> Annuler
                                                                    </button>
                                                                </form>
                                                            @else
                                                                <button type="button" class="btn btn-sm btn-outline-secondary" disabled
                                                                        data-bs-toggle="tooltip"
                                                                        title="Impossible d'annuler : certains articles de cette commande ne sont plus disponibles">
                                                                    <i class="fas fa-exclamation-triangle"></i> Non annulable
                                                                </button>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                    </tr>

                                    {{-- Message d'alerte pour articles supprimés --}}
                                    @if(auth()->user()->isClient() && $commande->user_id === auth()->id() && in_array($commande->statut, ['en_attente','confirmee']))
                                        @php
                                            $itemsSupprimees = $commande->items->filter(function($item) {
                                                return ($item->estPiece() && is_null($item->piece)) ||
                                                       ($item->estVehicule() && is_null($item->vehicule));
                                            });
                                        @endphp

                                        @if($itemsSupprimees->isNotEmpty())
                                            <tr>
                                                <td colspan="7" class="bg-warning bg-opacity-10">
                                                    <div class="alert alert-warning mb-0 py-2">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        <strong>Attention :</strong> Cette commande contient {{ $itemsSupprimees->count() }} article(s) qui ne sont plus disponibles dans le catalogue.
                                                        L'annulation n'est plus possible. Veuillez contacter le service client pour plus d'informations.
                                                    </div>
                                                </td>
                                            </tr>
                                        @endif
                                    @endif

                                    {{-- Modal pour modification du statut (uniquement pour casse) --}}
                                    @if(auth()->user()->isCasse() && $concerneUtilisateur)
                                        <div class="modal fade" id="editStatutModal{{ $commande->id }}" tabindex="-1" aria-labelledby="editStatutLabel{{ $commande->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="editStatutLabel{{ $commande->id }}">Modifier le statut</h5>
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
                                                                <label for="statut{{ $commande->id }}" class="form-label">Nouveau statut</label>
                                                                <select id="statut{{ $commande->id }}" name="statut" class="form-select" required>
                                                                    @foreach(['en_attente','confirmee','en_preparation','expedie','livree','annulee'] as $statut)
                                                                        <option value="{{ $statut }}" {{ $commande->statut === $statut ? 'selected' : '' }}>
                                                                            {{ ucfirst(str_replace('_', ' ', $statut)) }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                            </div>
                                                            <div class="mb-3">
                                                                <label for="commentaire{{ $commande->id }}" class="form-label">Commentaire (optionnel)</label>
                                                                <textarea id="commentaire{{ $commande->id }}" name="commentaire" class="form-control" rows="3" placeholder="Ex: Colis prêt pour expédition...">{{ old('commentaire') }}</textarea>
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
                                @endif
                            @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Pagination --}}
                    <div class="d-flex justify-content-center mt-4">
                        {{ $commandes->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-shopping-cart fa-4x text-muted mb-3"></i>
                        <h4>Aucune commande</h4>
                        <p class="text-muted">
                            @if(auth()->user()->isCasse())
                                Aucune commande reçue pour vos articles pour le moment
                            @else
                                Vous n'avez pas encore passé de commande
                            @endif
                        </p>
                        @if(auth()->user()->isClient())
                            <div class="d-flex justify-content-center gap-3">
                                <a href="{{ route('pieces.index') }}" class="btn btn-primary">
                                    <i class="fas fa-cog"></i> Parcourir les pièces
                                </a>
                                <a href="{{ route('demandes-epaves.index') }}" class="btn btn-success">
                                    <i class="fas fa-car"></i> Voir les véhicules
                                </a>
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        </div>

        {{-- Légende pour les casses --}}
        @if(auth()->user()->isCasse() && $commandes->count() > 0)
            <div class="card shadow mt-4">
                <div class="card-body">
                    <h6><i class="fas fa-info-circle text-info"></i> Information</h6>
                    <p class="mb-0 text-muted small">
                        Vous ne voyez que les commandes contenant vos articles (pièces ou véhicules).
                        Les autres articles de ces commandes sont gérés par leurs vendeurs respectifs.
                    </p>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            // Initialiser les tooltips Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            })
        </script>
    @endpush
@endsection
