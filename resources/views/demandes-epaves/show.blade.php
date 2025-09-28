@extends('layouts.app')

@section('title', 'Détails de la demande d\'épave')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Détails de la demande</h1>
            <a href="{{ route('demandes-epaves.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <div class="row">
            <!-- Informations principales -->
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="m-0">{{ $demandeEpave->marque }} {{ $demandeEpave->modele }} ({{ $demandeEpave->annee }})</h5>
                        <span class="badge {{ $demandeEpave->statut_badge_class }} fs-6">
                        {{ ucfirst(str_replace('_', ' ', $demandeEpave->statut)) }}
                    </span>
                    </div>
                    <div class="card-body">
                        <!-- Photos -->
                        @if($demandeEpave->photos && count($demandeEpave->photos) > 0)
                            <div class="mb-4">
                                <h6>Photos du véhicule</h6>
                                <div class="row">
                                    @foreach($demandeEpave->photos as $photo)
                                        <div class="col-md-3 mb-3">
                                            <img src="{{ asset('storage/' . $photo) }}"
                                                 class="img-fluid rounded shadow-sm"
                                                 style="cursor: pointer"
                                                 onclick="openModal('{{ asset('storage/' . $photo) }}')">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Informations techniques -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Marque/Modèle:</th>
                                        <td>{{ $demandeEpave->marque }} {{ $demandeEpave->modele }}</td>
                                    </tr>
                                    <tr>
                                        <th>Année:</th>
                                        <td>{{ $demandeEpave->annee }}</td>
                                    </tr>
                                    <tr>
                                        <th>Carburant:</th>
                                        <td>{{ ucfirst($demandeEpave->carburant) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Kilométrage:</th>
                                        <td>{{ number_format($demandeEpave->kilometrage, 0, ',', ' ') }} km</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-sm">
                                    <tr>
                                        <th width="40%">Couleur:</th>
                                        <td>{{ $demandeEpave->couleur }}</td>
                                    </tr>
                                    <tr>
                                        <th>État:</th>
                                        <td>{{ ucfirst($demandeEpave->etat) }}</td>
                                    </tr>
                                    <tr>
                                        <th>Prix souhaité:</th>
                                        <td>
                                            @if($demandeEpave->prix_souhaite)
                                                <strong class="text-primary">{{ number_format($demandeEpave->prix_souhaite, 0, ',', ' ') }} €</strong>
                                            @else
                                                <span class="text-muted">Non spécifié</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Numéro chassis:</th>
                                        <td>{{ $demandeEpave->numero_chassis }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="mb-4">
                            <h6>Description</h6>
                            <p class="text-muted">{{ $demandeEpave->description }}</p>
                        </div>

                        <!-- Informations de contact -->
                        <div class="mb-4">
                            <h6>Informations de contact</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <p><i class="fas fa-phone me-2"></i> {{ $demandeEpave->telephone_contact }}</p>
                                </div>
                                <div class="col-md-6">
                                    <p><i class="fas fa-map-marker-alt me-2"></i> {{ $demandeEpave->adresse }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Offres (visible pour le propriétaire) -->
                @if(auth()->user()->isClient() && $demandeEpave->user_id === auth()->id())
                    <div class="card shadow">
                        <div class="card-header">
                            <h5 class="m-0">Offres reçues ({{ $demandeEpave->offres->count() }})</h5>
                        </div>
                        <div class="card-body">
                            @if($demandeEpave->offres->count() > 0)
                                <div class="list-group">
                                    @foreach($demandeEpave->offres as $offre)
                                        <div class="list-group-item">
                                            <div class="d-flex justify-content-between align-items-start">
                                                <div>
                                                    <h6 class="mb-1">{{ $offre->casse->nom_entreprise }}</h6>
                                                    <p class="mb-1">
                                                        <strong class="text-success">{{ number_format($offre->prix_offert, 0, ',', ' ') }} €</strong>
                                                    </p>
                                                    @if($offre->message)
                                                        <p class="mb-1">{{ $offre->message }}</p>
                                                    @endif
                                                    <small class="text-muted">Offre faite le {{ $offre->created_at->format('d/m/Y H:i') }}</small>
                                                </div>
                                                <div>
                                                    @if($demandeEpave->statut === 'en_attente')
                                                        <form action="{{ route('demandes-epaves.accepter-offre', ['demandeEpave' => $demandeEpave->id, 'offre' => $offre->id]) }}" method="POST" class="d-inline">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm"
                                                                    onclick="return confirm('Accepter cette offre ?')">
                                                                <i class="fas fa-check"></i> Accepter
                                                            </button>
                                                        </form>

                                                    @elseif($offre->statut === 'accepte')
                                                        <span class="badge bg-success">Offre acceptée</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-gavel fa-3x text-muted mb-3"></i>
                                    <p class="text-muted">Aucune offre reçue pour le moment</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header">
                        <h6 class="m-0">Actions</h6>
                    </div>
                    <div class="card-body">
                        @if(auth()->user()->isClient() && $demandeEpave->user_id === auth()->id())
                            <div class="d-grid gap-2">
                                @if($demandeEpave->statut === 'en_attente')
                                    <a href="{{ route('demandes-epaves.edit', $demandeEpave) }}" class="btn btn-outline-primary">
                                        <i class="fas fa-edit"></i> Modifier la demande
                                    </a>

                                    <form action="{{ route('demandes-epaves.destroy', $demandeEpave) }}" method="POST" class="d-grid">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger"
                                                onclick="return confirm('Supprimer cette demande ?')">
                                            <i class="fas fa-trash"></i> Supprimer
                                        </button>
                                    </form>
                                @endif
                            </div>
                        @endif

                        @if(auth()->user()->isCasse() && $peutFaireOffre)
                            <div class="mt-3" id="faire-offre">
                                <h6>Faire une offre</h6>
                                <form action="{{ route('demandes-epaves.faire-offre', $demandeEpave) }}" method="POST">
                                    @csrf
                                    <div class="mb-3">
                                        <label for="prix_offert" class="form-label">Prix offert (€) *</label>
                                        <input type="number" step="0.01" class="form-control" id="prix_offert" name="prix_offert" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="message" class="form-label">Message (optionnel)</label>
                                        <textarea class="form-control" id="message" name="message" rows="3"
                                                  placeholder="Précisions sur l'offre..."></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-warning w-100">
                                        <i class="fas fa-gavel"></i> Soumettre l'offre
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Informations propriétaire -->
                <div class="card shadow">
                    <div class="card-header">
                        <h6 class="m-0">Informations du propriétaire</h6>
                    </div>
                    <div class="card-body">
                        <p class="mb-1"><strong>{{ $demandeEpave->user?->name ?? 'Utilisateur inconnu' }}</strong></p>
                        <p class="mb-1 text-muted small">
                            <i class="fas fa-phone"></i> {{ $demandeEpave->telephone_contact }}
                        </p>
                        <p class="mb-0 text-muted small">
                            <i class="fas fa-map-marker-alt"></i> {{ $demandeEpave->adresse }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal pour les photos -->
    <div class="modal fade" id="imageModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-body text-center">
                    <img src="" id="modalImage" class="img-fluid">
                </div>
            </div>
        </div>
    </div>

    <script>
        function openModal(imageSrc) {
            document.getElementById('modalImage').src = imageSrc;
            new bootstrap.Modal(document.getElementById('imageModal')).show();
        }
    </script>
@endsection
