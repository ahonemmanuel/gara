@extends('layouts.app')

@section('title', 'Modifier la pièce')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Modifier la pièce</h1>
            <a href="{{ route('pieces.show', $piece) }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour
            </a>
        </div>

        <div class="card shadow">
            <div class="card-body">
                <form action="{{ route('pieces.update', $piece) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="vehicle_id" class="form-label">Véhicule *</label>
                                <select class="form-select" id="vehicle_id" name="vehicle_id" required>
                                    @foreach($vehicles as $vehicle)
                                        <option value="{{ $vehicle->id }}" {{ old('vehicle_id', $piece->vehicle_id) == $vehicle->id ? 'selected' : '' }}>
                                            {{ $vehicle->marque }} {{ $vehicle->modele }} ({{ $vehicle->annee }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nom" class="form-label">Nom *</label>
                                <input type="text" class="form-control" id="nom" name="nom" required value="{{ old('nom', $piece->nom) }}">
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="description" class="form-label">Description *</label>
                        <textarea class="form-control" id="description" name="description" rows="3" required>{{ old('description', $piece->description) }}</textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="prix" class="form-label">Prix (€) *</label>
                                <input type="number" step="0.01" class="form-control" id="prix" name="prix" required value="{{ old('prix', $piece->prix) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="quantite" class="form-label">Quantité *</label>
                                <input type="number" class="form-control" id="quantite" name="quantite" required value="{{ old('quantite', $piece->quantite) }}">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label for="etat" class="form-label">État *</label>
                                <select class="form-select" id="etat" name="etat" required>
                                    <option value="neuf" {{ old('etat', $piece->etat) == 'neuf' ? 'selected' : '' }}>Neuf</option>
                                    <option value="tres_bon" {{ old('etat', $piece->etat) == 'tres_bon' ? 'selected' : '' }}>Très bon</option>
                                    <option value="bon" {{ old('etat', $piece->etat) == 'bon' ? 'selected' : '' }}>Bon</option>
                                    <option value="moyen" {{ old('etat', $piece->etat) == 'moyen' ? 'selected' : '' }}>Moyen</option>
                                    <option value="usage" {{ old('etat', $piece->etat) == 'usage' ? 'selected' : '' }}>Usage</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="reference_constructeur" class="form-label">Référence constructeur</label>
                                <input type="text" class="form-control" id="reference_constructeur" name="reference_constructeur" value="{{ old('reference_constructeur', $piece->reference_constructeur) }}">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="compatible_avec" class="form-label">Compatible avec (modèles)</label>
                                <input type="text" class="form-control" id="compatible_avec" name="compatible_avec"
                                       placeholder="Séparer par des virgules" value="{{ old('compatible_avec', $piece->compatible_avec ?? '') }}">
                                <div class="form-text">Liste des modèles compatibles séparés par des virgules</div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="photos" class="form-label">Photos</label>
                        <input type="file" class="form-control" id="photos" name="photos[]" multiple accept="image/*">
                        @if($piece->photos && count($piece->photos) > 0)
                            <div class="mt-2">
                                @foreach($piece->photos as $photo)
                                    <img src="{{ asset('storage/' . $photo) }}" width="80" class="rounded me-2">
                                @endforeach
                                <div class="form-text">Photos actuelles</div>
                            </div>
                        @endif
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="disponible" name="disponible" value="1"
                            {{ old('disponible', $piece->disponible) ? 'checked' : '' }}>
                        <label class="form-check-label" for="disponible">Disponible à la vente</label>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Mettre à jour
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
