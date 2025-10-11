{{-- edit.blade.php --}}
@extends('layouts.app')

@section('title', 'Modifier l\'annonce')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- En-tête -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1><i class="fas fa-edit"></i> Modifier l'annonce</h1>
                    <a href="{{ route('demandes-epaves.show', $demandeEpave) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Retour
                    </a>
                </div>

                <!-- Formulaire -->
                <form action="{{ route('demandes-epaves.update', $demandeEpave) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

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
                                        <option value="vehicule" {{ old('type', $demandeEpave->type) === 'vehicule' ? 'selected' : '' }}>
                                            Véhicule
                                        </option>
                                        <option value="epave" {{ old('type', $demandeEpave->type) === 'epave' ? 'selected' : '' }}>
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
                                           value="{{ old('annee', $demandeEpave->annee) }}"
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
                                            @php
                                                $marqueActuelle = \App\Models\Marque::where('nom', $demandeEpave->marque)->first();
                                                $isSelected = old('marque_id', optional($marqueActuelle)->id) == $marque->id;
                                            @endphp
                                            <option value="{{ $marque->id }}" {{ $isSelected ? 'selected' : '' }}>
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

                                <!-- Nouvelle marque -->
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

                                <!-- Modèle -->
                                <div class="col-md-6 mb-3" id="modele-container">
                                    <label class="form-label">Modèle <span class="text-danger">*</span></label>
                                    <select name="modele_id" id="modele_id" class="form-select @error('modele_id') is-invalid @enderror" required>
                                        <option value="">-- Sélectionner un modèle --</option>
                                        @if($modeles->count() > 0)
                                            @foreach($modeles as $modele)
                                                @php
                                                    $modeleActuel = \App\Models\Modele::where('nom', $demandeEpave->modele)
                                                        ->where('marque_id', optional($marqueActuelle)->id)
                                                        ->first();
                                                    $isSelected = old('modele_id', optional($modeleActuel)->id) == $modele->id;
                                                @endphp
                                                <option value="{{ $modele->id }}" {{ $isSelected ? 'selected' : '' }}>
                                                    {{ $modele->nom }}
                                                </option>
                                            @endforeach
                                        @endif
                                        <option value="new" {{ old('modele_id') === 'new' ? 'selected' : '' }}>
                                            ➕ Ajouter un nouveau modèle
                                        </option>
                                    </select>
                                    @error('modele_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Nouveau modèle -->
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
                                           value="{{ old('couleur', $demandeEpave->couleur) }}" required>
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
                                        <option value="essence" {{ old('carburant', $demandeEpave->carburant) === 'essence' ? 'selected' : '' }}>Essence</option>
                                        <option value="diesel" {{ old('carburant', $demandeEpave->carburant) === 'diesel' ? 'selected' : '' }}>Diesel</option>
                                        <option value="hybride" {{ old('carburant', $demandeEpave->carburant) === 'hybride' ? 'selected' : '' }}>Hybride</option>
                                        <option value="electrique" {{ old('carburant', $demandeEpave->carburant) === 'electrique' ? 'selected' : '' }}>Électrique</option>
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
                                           value="{{ old('kilometrage', $demandeEpave->kilometrage) }}" min="0" required>
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
                                        <option value="bon" {{ old('etat', $demandeEpave->etat) === 'bon' ? 'selected' : '' }}>Bon</option>
                                        <option value="moyen" {{ old('etat', $demandeEpave->etat) === 'moyen' ? 'selected' : '' }}>Moyen</option>
                                        <option value="mauvais" {{ old('etat', $demandeEpave->etat) === 'mauvais' ? 'selected' : '' }}>Mauvais</option>
                                        <option value="epave" {{ old('etat', $demandeEpave->etat) === 'epave' ? 'selected' : '' }}>Épave</option>
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
                                           value="{{ old('prix_souhaite', $demandeEpave->prix_souhaite) }}" min="1" required>
                                    @error('prix_souhaite')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Téléphone contact -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Téléphone de contact <span class="text-danger">*</span></label>
                                    <input type="text" name="telephone_contact"
                                           class="form-control @error('telephone_contact') is-invalid @enderror"
                                           value="{{ old('telephone_contact', $demandeEpave->telephone_contact) }}" required>
                                    @error('telephone_contact')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Adresse -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Adresse <span class="text-danger">*</span></label>
                                    <input type="text" name="adresse"
                                           class="form-control @error('adresse') is-invalid @enderror"
                                           value="{{ old('adresse', $demandeEpave->adresse) }}" required>
                                    @error('adresse')
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
                                          required>{{ old('description', $demandeEpave->description) }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <!-- Photos existantes -->
                            @if($demandeEpave->photos && count($demandeEpave->photos) > 0)
                                <div class="mb-3">
                                    <label class="form-label">Photos actuelles</label>
                                    <div class="row">
                                        @foreach($demandeEpave->photos as $photo)
                                            <div class="col-md-3 mb-2">
                                                <img src="{{ asset('storage/' . $photo) }}"
                                                     class="img-fluid rounded"
                                                     style="height: 150px; object-fit: cover; width: 100%;"
                                                     alt="Photo">
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            <!-- Nouvelles photos -->
                            <div class="mb-3">
                                <label class="form-label">Remplacer les photos (optionnel)</label>
                                <input type="file" name="photos[]"
                                       class="form-control @error('photos.*') is-invalid @enderror"
                                       accept="image/*" multiple>
                                @error('photos.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">
                                    Si vous ajoutez de nouvelles photos, elles remplaceront les anciennes
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Boutons -->
                    <div class="d-flex justify-content-between mb-5">
                        <a href="{{ route('demandes-epaves.show', $demandeEpave) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check"></i> Enregistrer les modifications
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

                // Au chargement de la page
                if (marqueSelect.value === 'new') {
                    newMarqueContainer.style.display = 'block';
                    newMarqueInput.setAttribute('required', 'required');
                    newModeleContainer.style.display = 'block';
                    newModeleInput.setAttribute('required', 'required');
                    modeleSelect.disabled = true;
                } else if (marqueSelect.value) {
                    // Charger les modèles de la marque sélectionnée
                    chargerModeles(marqueSelect.value);
                }

                if (modeleSelect.value === 'new') {
                    newModeleContainer.style.display = 'block';
                    newModeleInput.setAttribute('required', 'required');
                }
            });
        </script>
@endsection
