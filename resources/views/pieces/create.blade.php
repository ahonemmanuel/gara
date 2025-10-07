@extends('layouts.app')

@section('title', 'Ajouter une pièce détachée')

@section('content')
    <div class="container">

        <h1 class="mb-4">Ajouter une pièce détachée</h1>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <form action="{{ route('pieces.store') }}" method="POST" enctype="multipart/form-data" id="pieceForm">
            @csrf

            {{-- Nom --}}
            <div class="mb-3">
                <label for="nom" class="form-label">Nom de la pièce *</label>
                <input type="text" name="nom" id="nom" class="form-control @error('nom') is-invalid @enderror" value="{{ old('nom') }}">
                @error('nom')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Marque --}}
            <div class="mb-3">
                <label for="marque_id" class="form-label">Marque de la pièce *</label>
                <div class="input-group">
                    <select name="marque_id" id="marque_id" class="form-select @error('marque_id') is-invalid @enderror">
                        <option value="">Sélectionner une marque</option>
                        @foreach($marques as $marque)
                            <option value="{{ $marque->id }}" {{ old('marque_id') == $marque->id ? 'selected' : '' }}>
                                {{ $marque->nom }}
                            </option>
                        @endforeach
                    </select>
                    <button type="button" class="btn btn-outline-secondary" id="toggleNewMarque">
                        <i class="bi bi-plus-lg"></i> Nouvelle
                    </button>
                </div>
                @error('marque_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('new_marque')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nouveau champ pour nouvelle marque --}}
            <div class="mb-3" id="newMarqueField" style="display: none;">
                <label for="new_marque" class="form-label">Nouvelle marque</label>
                <div class="input-group">
                    <input type="text" name="new_marque" id="new_marque" class="form-control" value="{{ old('new_marque') }}" placeholder="Entrez le nom de la nouvelle marque">
                    <button type="button" class="btn btn-outline-danger" id="cancelNewMarque">
                        <i class="bi bi-x-lg"></i> Annuler
                    </button>
                </div>
                <small class="text-muted">Cette nouvelle marque sera ajoutée à la liste</small>
            </div>

            {{-- Modèle --}}
            <div class="mb-3">
                <label for="modele_id" class="form-label">Modèle de la pièce *</label>
                <div class="input-group">
                    <select name="modele_id" id="modele_id" class="form-select @error('modele_id') is-invalid @enderror" disabled>
                        <option value="">Sélectionner d'abord une marque</option>
                    </select>
                    <button type="button" class="btn btn-outline-secondary" id="toggleNewModele" disabled>
                        <i class="bi bi-plus-lg"></i> Nouveau
                    </button>
                </div>
                @error('modele_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @error('new_modele')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

            {{-- Nouveau champ pour nouveau modèle --}}
            <div class="mb-3" id="newModeleField" style="display: none;">
                <label for="new_modele" class="form-label">Nouveau modèle</label>
                <div class="input-group">
                    <input type="text" name="new_modele" id="new_modele" class="form-control" value="{{ old('new_modele') }}" placeholder="Entrez le nom du nouveau modèle">
                    <button type="button" class="btn btn-outline-danger" id="cancelNewModele">
                        <i class="bi bi-x-lg"></i> Annuler
                    </button>
                </div>
                <small class="text-muted">Ce nouveau modèle sera ajouté à la liste</small>
            </div>

            {{-- Description --}}
            <div class="mb-3">
                <label for="description" class="form-label">Description *</label>
                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror" rows="4">{{ old('description') }}</textarea>
                @error('description')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Prix --}}
            <div class="mb-3">
                <label for="prix" class="form-label">Prix (≥ 500 FCFA) *</label>
                <input type="number" name="prix" id="prix" class="form-control @error('prix') is-invalid @enderror" value="{{ old('prix') }}" min="500" step="0.01">
                @error('prix')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Quantité --}}
            <div class="mb-3">
                <label for="quantite" class="form-label">Quantité disponible (≥ 1) *</label>
                <input type="number" name="quantite" id="quantite" class="form-control @error('quantite') is-invalid @enderror" value="{{ old('quantite') }}" min="1">
                @error('quantite')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- État --}}
            <div class="mb-3">
                <label for="etat" class="form-label">État *</label>
                <select name="etat" id="etat" class="form-select @error('etat') is-invalid @enderror" required>
                    <option value="">Sélectionner un état</option>
                    @foreach(['neuf','tres_bon','bon','moyen','usage'] as $etatOption)
                        <option value="{{ $etatOption }}" {{ old('etat') == $etatOption ? 'selected' : '' }}>
                            {{ ucfirst(str_replace('_', ' ', $etatOption)) }}
                        </option>
                    @endforeach
                </select>
                @error('etat')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Référence constructeur --}}
            <div class="mb-3">
                <label for="reference_constructeur" class="form-label">Référence constructeur *</label>
                <input type="text" name="reference_constructeur" id="reference_constructeur" class="form-control @error('reference_constructeur') is-invalid @enderror" value="{{ old('reference_constructeur') }}">
                @error('reference_constructeur')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            {{-- Compatible avec --}}
            <div class="mb-3">
                <label for="compatible_avec" class="form-label">Compatible avec *</label>
                <textarea name="compatible_avec" id="compatible_avec" class="form-control @error('compatible_avec') is-invalid @enderror" rows="2" placeholder="Ex: Toyota Corolla 2015-2020, Honda Civic 2016-2021">{{ old('compatible_avec') }}</textarea>
                @error('compatible_avec')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Listez les véhicules compatibles avec cette pièce</small>
            </div>

            {{-- Photos --}}
            <div class="mb-3">
                <label for="photos" class="form-label">Photos (plusieurs possibles)</label>
                <input type="file" name="photos[]" id="photos" class="form-control @error('photos.*') is-invalid @enderror" multiple accept="image/*">
                @error('photos.*')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
                <small class="text-muted">Format acceptés: JPG, PNG, GIF. Taille max: 2 Mo par image</small>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-save"></i> Ajouter la pièce
                </button>
                <a href="{{ route('pieces.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> Retour
                </a>
            </div>
        </form>
    </div>

    {{-- Select2 CSS et JS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const marqueSelect = document.getElementById('marque_id');
            const modeleSelect = document.getElementById('modele_id');
            const toggleNewMarque = document.getElementById('toggleNewMarque');
            const toggleNewModele = document.getElementById('toggleNewModele');
            const newMarqueField = document.getElementById('newMarqueField');
            const newModeleField = document.getElementById('newModeleField');
            const cancelNewMarque = document.getElementById('cancelNewMarque');
            const cancelNewModele = document.getElementById('cancelNewModele');
            const newMarqueInput = document.getElementById('new_marque');
            const newModeleInput = document.getElementById('new_modele');

            // Activer Select2 sur les listes déroulantes
            $('#marque_id').select2({
                placeholder: "Sélectionner une marque",
                allowClear: true,
                width: '100%'
            });

            $('#modele_id').select2({
                placeholder: "Sélectionner un modèle",
                allowClear: true,
                width: '100%'
            });

            // Gestion de l'affichage du champ nouvelle marque
            toggleNewMarque.addEventListener('click', function() {
                marqueSelect.disabled = true;
                marqueSelect.value = '';
                newMarqueField.style.display = 'block';
                newMarqueInput.focus();

                // Désactiver et réinitialiser le modèle
                modeleSelect.disabled = true;
                modeleSelect.innerHTML = '<option value="">Sélectionner d\'abord une marque</option>';
                toggleNewModele.disabled = true;
                newModeleField.style.display = 'none';
                newModeleInput.value = '';
            });

            // Annuler nouvelle marque
            cancelNewMarque.addEventListener('click', function() {
                marqueSelect.disabled = false;
                newMarqueField.style.display = 'none';
                newMarqueInput.value = '';
            });

            // Gestion de l'affichage du champ nouveau modèle
            toggleNewModele.addEventListener('click', function() {
                modeleSelect.disabled = true;
                modeleSelect.value = '';
                newModeleField.style.display = 'block';
                newModeleInput.focus();
            });

            // Annuler nouveau modèle
            cancelNewModele.addEventListener('click', function() {
                modeleSelect.disabled = false;
                newModeleField.style.display = 'none';
                newModeleInput.value = '';
            });

            // Charger les modèles quand une marque est sélectionnée
            marqueSelect.addEventListener('change', function() {
                const marqueId = this.value;

                if (!marqueId) {
                    modeleSelect.disabled = true;
                    modeleSelect.innerHTML = '<option value="">Sélectionner d\'abord une marque</option>';
                    toggleNewModele.disabled = true;
                    return;
                }

                toggleNewModele.disabled = false;

                fetch(`/api/marques/${marqueId}/modeles`)
                    .then(response => response.json())
                    .then(data => {
                        modeleSelect.innerHTML = '<option value="">Sélectionner un modèle</option>';

                        data.forEach(modele => {
                            const option = document.createElement('option');
                            option.value = modele.id;
                            option.textContent = modele.nom;
                            modeleSelect.appendChild(option);
                        });

                        modeleSelect.disabled = false;

                        // Réinitialiser Select2 pour appliquer la recherche sur les nouvelles options
                        $('#modele_id').select2({
                            placeholder: "Sélectionner un modèle",
                            allowClear: true,
                            width: '100%'
                        });
                    })
                    .catch(error => {
                        console.error('Erreur lors du chargement des modèles:', error);
                        modeleSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    });
            });

            // Si une marque est déjà sélectionnée (old input), charger ses modèles
            if (marqueSelect.value) {
                marqueSelect.dispatchEvent(new Event('change'));
            }
        });
    </script>
@endsection
