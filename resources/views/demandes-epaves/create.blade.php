{{-- create.blade.php --}}
@extends('layouts.app')

@section('title', 'Créer une annonce')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-plus-circle"></i> Créer une annonce</h1>
                    <a href="{{ route('demandes-epaves.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <!-- Formulaire -->
                <form action="{{ route('demandes-epaves.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Informations générales</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Type -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Type <span class="text-danger">*</span></label>
                                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                                        <option value="">-- Sélectionner --</option>
                                        <option value="vehicule" {{ old('type') === 'vehicule' ? 'selected' : '' }}>
                                            Véhicule
                                        </option>
                                        <option value="epave" {{ old('type') === 'epave' ? 'selected' : '' }}>
                                            Épave
                                        </option>
                                    </select>
                                    @error('type')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Année -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Année <span class="text-danger">*</span></label>
                                    <input type="number" name="annee"
                                           class="form-control @error('annee') is-invalid @enderror"
                                           value="{{ old('annee') }}"
                                           min="1900" max="{{ date('Y') + 1 }}" required>
                                    @error('annee')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Marque -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Marque <span class="text-danger">*</span></label>
                                    <select name="marque_id" id="marque_id" class="form-select @error('marque_id') is-invalid @enderror" required>
                                        <option value="">-- Sélectionner une marque --</option>
                                        @foreach($marques as $marque)
                                            <option value="{{ $marque->id }}" {{ old('marque_id') == $marque->id ? 'selected' : '' }}>
                                                {{ $marque->nom }}
                                            </option>
                                        @endforeach
                                        <option value="new" {{ old('marque_id') === 'new' ? 'selected' : '' }}>
                                            ➕ Ajouter une nouvelle marque
                                        </option>
                                    </select>
                                    @error('marque_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nouvelle marque (masqué par défaut) -->
                                <div class="col-md-6 mb-3" id="new-marque-container" style="display: none;">
                                    <label class="form-label">Nouvelle marque <span class="text-danger">*</span></label>
                                    <input type="text" name="new_marque" id="new_marque"
                                           class="form-control @error('new_marque') is-invalid @enderror"
                                           value="{{ old('new_marque') }}"
                                           placeholder="Ex: Lexus">
                                    @error('new_marque')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Modèle (sera rempli dynamiquement) -->
                                <div class="col-md-6 mb-3" id="modele-container">
                                    <label class="form-label">Modèle <span class="text-danger">*</span></label>
                                    <select name="modele_id" id="modele_id" class="form-select @error('modele_id') is-invalid @enderror" required disabled>
                                        <option value="">-- Sélectionnez d'abord une marque --</option>
                                    </select>
                                    @error('modele_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nouveau modèle (masqué par défaut) -->
                                <div class="col-md-6 mb-3" id="new-modele-container" style="display: none;">
                                    <label class="form-label">Nouveau modèle <span class="text-danger">*</span></label>
                                    <input type="text" name="new_modele" id="new_modele"
                                           class="form-control @error('new_modele') is-invalid @enderror"
                                           value="{{ old('new_modele') }}"
                                           placeholder="Ex: RX 350">
                                    @error('new_modele')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Couleur -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Couleur <span class="text-danger">*</span></label>
                                    <input type="text" name="couleur"
                                           class="form-control @error('couleur') is-invalid @enderror"
                                           value="{{ old('couleur') }}"
                                           placeholder="Ex: Blanc"
                                           required>
                                    @error('couleur')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Caractéristiques techniques</h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <!-- Carburant -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Carburant <span class="text-danger">*</span></label>
                                    <select name="carburant" class="form-select @error('carburant') is-invalid @enderror" required>
                                        <option value="">-- Sélectionner --</option>
                                        <option value="essence" {{ old('carburant') === 'essence' ? 'selected' : '' }}>Essence</option>
                                        <option value="diesel" {{ old('carburant') === 'diesel' ? 'selected' : '' }}>Diesel</option>
                                        <option value="hybride" {{ old('carburant') === 'hybride' ? 'selected' : '' }}>Hybride</option>
                                        <option value="electrique" {{ old('carburant') === 'electrique' ? 'selected' : '' }}>Électrique</option>
                                    </select>
                                    @error('carburant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Kilométrage -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Kilométrage (km) <span class="text-danger">*</span></label>
                                    <input type="number" name="kilometrage"
                                           class="form-control @error('kilometrage') is-invalid @enderror"
                                           value="{{ old('kilometrage') }}"
                                           min="0"
                                           placeholder="Ex: 50000"
                                           required>
                                    @error('kilometrage')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>


                                <!-- Quantité -->


                                <!-- État -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">État <span class="text-danger">*</span></label>
                                    <select name="etat" class="form-select @error('etat') is-invalid @enderror" required>
                                        <option value="">-- Sélectionner --</option>
                                        <option value="bon" {{ old('etat') === 'bon' ? 'selected' : '' }}>Bon</option>
                                        <option value="moyen" {{ old('etat') === 'moyen' ? 'selected' : '' }}>Moyen</option>
                                        <option value="mauvais" {{ old('etat') === 'mauvais' ? 'selected' : '' }}>Mauvais</option>
                                        <option value="epave" {{ old('etat') === 'epave' ? 'selected' : '' }}>Épave</option>
                                    </select>
                                    @error('etat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Prix -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Prix souhaité (FCFA) <span class="text-danger">*</span></label>
                                    <input type="number" name="prix_souhaite"
                                           class="form-control @error('prix_souhaite') is-invalid @enderror"
                                           value="{{ old('prix_souhaite') }}"
                                           min="1"
                                           placeholder="Ex: 5000000"
                                           required>
                                    @error('prix_souhaite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Numéro chassis -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Numéro de chassis <span class="text-danger">*</span></label>
                                    <input type="text" name="numero_chassis"
                                           class="form-control @error('numero_chassis') is-invalid @enderror"
                                           value="{{ old('numero_chassis') }}"
                                           placeholder="Ex: JT2BG12K123456789"
                                           required>
                                    @error('numero_chassis')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Numéro plaque -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Numéro de plaque <span class="text-danger">*</span></label>
                                    <input type="text" name="numero_plaque"
                                           class="form-control @error('numero_plaque') is-invalid @enderror"
                                           value="{{ old('numero_plaque') }}"
                                           placeholder="Ex: AB-1234-CD"
                                           required>
                                    @error('numero_plaque')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">Description et photos</h5>
                        </div>
                        <div class="card-body">
                            <!-- Description -->
                            <div class="mb-3">
                                <label class="form-label">Description détaillée <span class="text-danger">*</span></label>
                                <textarea name="description" rows="5"
                                          class="form-control @error('description') is-invalid @enderror"
                                          placeholder="Décrivez l'état du véhicule, les équipements, l'historique..."
                                          required>{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Décrivez l'état du véhicule, les équipements, l'historique, etc.
                                </small>
                            </div>

                            <!-- Photos -->
                            <div class="mb-3">
                                <label class="form-label">Photos</label>
                                <input type="file" name="photos[]"
                                       class="form-control @error('photos.*') is-invalid @enderror"
                                       accept="image/*" multiple>
                                @error('photos.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Vous pouvez ajouter plusieurs photos (max 2 Mo par photo). Format acceptés: JPG, PNG
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between mb-5">
                        <a href="{{ route('demandes-epaves.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check"></i> Créer l'annonce
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const marqueSelect = document.getElementById('marque_id');
                const modeleSelect = document.getElementById('modele_id');
                const newMarqueContainer = document.getElementById('new-marque-container');
                const newModeleContainer = document.getElementById('new-modele-container');
                const newMarqueInput = document.getElementById('new_marque');
                const newModeleInput = document.getElementById('new_modele');

                // Gestion du changement de marque
                marqueSelect.addEventListener('change', function() {
                    const marqueId = this.value;

                    // Réinitialiser le modèle
                    modeleSelect.innerHTML = '<option value="">-- Sélectionner un modèle --</option>';
                    newModeleContainer.style.display = 'none';
                    newModeleInput.removeAttribute('required');
                    newModeleInput.value = '';

                    if (marqueId === 'new') {
                        // Afficher le champ nouvelle marque
                        newMarqueContainer.style.display = 'block';
                        newMarqueInput.setAttribute('required', 'required');

                        // Désactiver le select modèle et afficher le champ nouveau modèle
                        modeleSelect.disabled = true;
                        modeleSelect.removeAttribute('required');
                        newModeleContainer.style.display = 'block';
                        newModeleInput.setAttribute('required', 'required');
                    } else if (marqueId) {
                        // Masquer le champ nouvelle marque
                        newMarqueContainer.style.display = 'none';
                        newMarqueInput.removeAttribute('required');
                        newMarqueInput.value = '';

                        // Activer et charger les modèles
                        modeleSelect.disabled = false;
                        modeleSelect.setAttribute('required', 'required');
                        chargerModeles(marqueId);
                    } else {
                        // Aucune marque sélectionnée
                        newMarqueContainer.style.display = 'none';
                        newMarqueInput.removeAttribute('required');
                        modeleSelect.disabled = true;
                        modeleSelect.removeAttribute('required');
                    }
                });

                // Gestion du changement de modèle
                modeleSelect.addEventListener('change', function() {
                    if (this.value === 'new') {
                        newModeleContainer.style.display = 'block';
                        newModeleInput.setAttribute('required', 'required');
                    } else {
                        newModeleContainer.style.display = 'none';
                        newModeleInput.removeAttribute('required');
                        newModeleInput.value = '';
                    }
                });

                // Fonction pour charger les modèles via AJAX
                function chargerModeles(marqueId) {
                    modeleSelect.innerHTML = '<option value="">Chargement...</option>';
                    modeleSelect.disabled = true;

                    fetch(`/api/marques/${marqueId}/modeles-epave`)
                        .then(response => response.json())
                        .then(modeles => {
                            modeleSelect.innerHTML = '<option value="">-- Sélectionner un modèle --</option>';

                            if (modeles.length > 0) {
                                modeles.forEach(modele => {
                                    const option = document.createElement('option');
                                    option.value = modele.id;
                                    option.textContent = modele.nom;
                                    modeleSelect.appendChild(option);
                                });
                            }

                            // Ajouter l'option "Nouveau modèle"
                            const optionNew = document.createElement('option');
                            optionNew.value = 'new';
                            optionNew.textContent = '➕ Ajouter un nouveau modèle';
                            modeleSelect.appendChild(optionNew);

                            modeleSelect.disabled = false;
                        })
                        .catch(error => {
                            console.error('Erreur lors du chargement des modèles:', error);
                            modeleSelect.innerHTML = '<option value="">Erreur de chargement</option>';

                            // En cas d'erreur, permettre d'ajouter un nouveau modèle
                            const optionNew = document.createElement('option');
                            optionNew.value = 'new';
                            optionNew.textContent = '➕ Ajouter un nouveau modèle';
                            modeleSelect.appendChild(optionNew);

                            modeleSelect.disabled = false;
                        });
                }

                // Au chargement de la page, vérifier si des valeurs sont déjà sélectionnées (après erreur de validation)
                if (marqueSelect.value === 'new') {
                    newMarqueContainer.style.display = 'block';
                    newMarqueInput.setAttribute('required', 'required');
                    newModeleContainer.style.display = 'block';
                    newModeleInput.setAttribute('required', 'required');
                    modeleSelect.disabled = true;
                } else if (marqueSelect.value) {
                    chargerModeles(marqueSelect.value);
                }

                if (modeleSelect.value === 'new') {
                    newModeleContainer.style.display = 'block';
                    newModeleInput.setAttribute('required', 'required');
                }
            });
        </script>
@endsection


