@extends('layouts.app')

@section('title', 'Ajouter une pièce détachée')

@section('content')
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1>Ajouter une nouvelle pièce</h1>
            <a href="{{ route('pieces.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Retour à la liste
            </a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-plus-circle me-2"></i>Informations de la pièce</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('pieces.store') }}" method="POST" enctype="multipart/form-data" id="pieceForm">
                            @csrf

                            <!-- Sélection du véhicule -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="vehicle_id" class="form-label required">Véhicule associé</label>
                                        <select class="form-select @error('vehicle_id') is-invalid @enderror" id="vehicle_id" name="vehicle_id" required>
                                            <option value="">Sélectionner un véhicule</option>
                                            @foreach($vehicles as $vehicle)
                                                <option value="{{ $vehicle->id }}"
                                                    {{ (old('vehicle_id') == $vehicle->id || ($selectedVehicle && $selectedVehicle->id == $vehicle->id)) ? 'selected' : '' }}>
                                                    {{ $vehicle->marque }} {{ $vehicle->modele }} ({{ $vehicle->annee }}) - {{ $vehicle->numero_plaque }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('vehicle_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                        <div class="form-text">Sélectionnez le véhicule auquel appartient cette pièce</div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <!-- Informations de base -->
                                    <div class="mb-3">
                                        <label for="nom" class="form-label required">Nom de la pièce</label>
                                        <input type="text" class="form-control @error('nom') is-invalid @enderror"
                                               id="nom" name="nom" value="{{ old('nom') }}" required
                                               placeholder="Ex: Alternateur, Pare-chocs avant, etc.">
                                        @error('nom')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="reference_constructeur" class="form-label">Référence constructeur</label>
                                        <input type="text" class="form-control @error('reference_constructeur') is-invalid @enderror"
                                               id="reference_constructeur" name="reference_constructeur" value="{{ old('reference_constructeur') }}"
                                               placeholder="Ex: 123456789-ABC">
                                        @error('reference_constructeur')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="prix" class="form-label required">Prix (€)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.01" min="0"
                                                   class="form-control @error('prix') is-invalid @enderror"
                                                   id="prix" name="prix" value="{{ old('prix') }}" required
                                                   placeholder="0.00">
                                            <span class="input-group-text">€</span>
                                        </div>
                                        @error('prix')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <!-- Stock et état -->
                                    <div class="mb-3">
                                        <label for="quantite" class="form-label required">Quantité en stock</label>
                                        <input type="number" min="1" class="form-control @error('quantite') is-invalid @enderror"
                                               id="quantite" name="quantite" value="{{ old('quantite', 1) }}" required>
                                        @error('quantite')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3">
                                        <label for="etat" class="form-label required">État de la pièce</label>
                                        <select class="form-select @error('etat') is-invalid @enderror" id="etat" name="etat" required>
                                            <option value="">Sélectionner un état</option>
                                            <option value="neuf" {{ old('etat') == 'neuf' ? 'selected' : '' }}>Neuf</option>
                                            <option value="tres_bon" {{ old('etat') == 'tres_bon' ? 'selected' : '' }}>Très bon état</option>
                                            <option value="bon" {{ old('etat') == 'bon' ? 'selected' : '' }}>Bon état</option>
                                            <option value="moyen" {{ old('etat') == 'moyen' ? 'selected' : '' }}>État moyen</option>
                                            <option value="usage" {{ old('etat') == 'usage' ? 'selected' : '' }}>État d'usage</option>
                                        </select>
                                        @error('etat')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-3 form-check form-switch">
                                        <input class="form-check-input" type="checkbox" id="disponible" name="disponible"
                                            {{ old('disponible', true) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="disponible">Disponible à la vente</label>
                                    </div>
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mb-3">
                                <label for="description" class="form-label required">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          id="description" name="description" rows="4" required
                                          placeholder="Décrivez la pièce en détail...">{{ old('description') }}</textarea>
                                @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Décrivez l'état, les spécificités, les défauts éventuels...</div>
                            </div>

                            <!-- Compatibilité -->
                            <div class="mb-3">
                                <label for="compatible_avec" class="form-label">Véhicules compatibles</label>
                                <textarea class="form-control @error('compatible_avec') is-invalid @enderror"
                                          id="compatible_avec" name="compatible_avec" rows="2"
                                          placeholder="Ex: Renault Clio III 2008-2012, Peugeot 308 2010-2013...">{{ old('compatible_avec') }}</textarea>
                                @error('compatible_avec')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">Listez les modèles compatibles (séparés par des virgules)</div>
                            </div>

                            <!-- Upload de photos -->
                            <div class="mb-4">
                                <label for="photos" class="form-label">Photos de la pièce</label>
                                <input type="file" class="form-control @error('photos.*') is-invalid @enderror"
                                       id="photos" name="photos[]" multiple accept="image/*">
                                @error('photos.*')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('photos')
                                <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <div class="form-text">
                                    Formats acceptés: JPG, PNG, GIF. Taille max: 2MB par image.
                                    Vous pouvez sélectionner plusieurs images.
                                </div>

                                <!-- Aperçu des images -->
                                <div id="imagePreview" class="mt-3 row g-2" style="display: none;"></div>
                            </div>

                            <!-- Boutons de soumission -->
                            <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                                <button type="reset" class="btn btn-outline-secondary me-md-2">
                                    <i class="fas fa-undo"></i> Réinitialiser
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Enregistrer la pièce
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sidebar d'aide -->
            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header bg-info text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Conseils d'ajout</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-lightbulb"></i> Bonnes pratiques</h6>
                            <ul class="small mb-0">
                                <li>Utilisez des photos claires et sous plusieurs angles</li>
                                <li>Décrivez précisément l'état de la pièce</li>
                                <li>Vérifiez la référence constructeur si disponible</li>
                                <li>Indiquez tous les modèles compatibles</li>
                            </ul>
                        </div>

                        <div class="alert alert-warning">
                            <h6><i class="fas fa-exclamation-triangle"></i> Important</h6>
                            <ul class="small mb-0">
                                <li>Les champs marqués d'un <span class="text-danger">*</span> sont obligatoires</li>
                                <li>Vérifiez l'état de la pièce avant mise en vente</li>
                                <li>Assurez-vous du stock disponible</li>
                            </ul>
                        </div>

                        <!-- Véhicule sélectionné (si applicable) -->
                        @if($selectedVehicle)
                            <div class="alert alert-success">
                                <h6><i class="fas fa-car"></i> Véhicule sélectionné</h6>
                                <div class="d-flex align-items-center mt-2">
                                    @if($selectedVehicle->photo_principale)
                                        <img src="{{ asset('storage/' . $selectedVehicle->photo_principale) }}"
                                             class="rounded me-2" width="50" height="50" style="object-fit: cover;">
                                    @else
                                        <div class="bg-light rounded me-2 d-flex align-items-center justify-content-center"
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-car text-muted"></i>
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $selectedVehicle->marque }} {{ $selectedVehicle->modele }}</strong><br>
                                        <small class="text-muted">{{ $selectedVehicle->annee }} • {{ $selectedVehicle->numero_plaque }}</small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Pièces récemment ajoutées -->
                @php
                    $recentPieces = auth()->user()->pieces()->with('vehicle')->latest()->take(3)->get();
                @endphp

                @if($recentPieces->count() > 0)
                    <div class="card shadow mt-4">
                        <div class="card-header">
                            <h6 class="mb-0"><i class="fas fa-clock me-2"></i>Pièces récentes</h6>
                        </div>
                        <div class="card-body">
                            <div class="list-group list-group-flush">
                                @foreach($recentPieces as $recentPiece)
                                    <div class="list-group-item px-0">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ $recentPiece->nom }}</h6>
                                                <small class="text-muted">{{ $recentPiece->vehicle->marque }} {{ $recentPiece->vehicle->modele }}</small><br>
                                                <span class="badge bg-success">{{ number_format($recentPiece->prix, 0, ',', ' ') }} €</span>
                                            </div>
                                            <span class="badge bg-{{ $recentPiece->quantite > 0 ? 'primary' : 'secondary' }}">
                                    {{ $recentPiece->quantite }}
                                </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Aperçu des images avant upload
            const photoInput = document.getElementById('photos');
            const imagePreview = document.getElementById('imagePreview');

            photoInput.addEventListener('change', function(e) {
                imagePreview.innerHTML = '';
                imagePreview.style.display = 'none';

                if (this.files && this.files.length > 0) {
                    imagePreview.style.display = 'block';

                    for (let i = 0; i < this.files.length; i++) {
                        const file = this.files[i];
                        if (file.type.match('image.*')) {
                            const reader = new FileReader();

                            reader.onload = function(e) {
                                const col = document.createElement('div');
                                col.className = 'col-4';
                                col.innerHTML = `
                            <div class="position-relative">
                                <img src="${e.target.result}" class="img-thumbnail" style="height: 100px; object-fit: cover;">
                                <button type="button" class="btn-close position-absolute top-0 end-0 bg-white"
                                        onclick="removeImagePreview(this)" aria-label="Supprimer"></button>
                            </div>
                        `;
                                imagePreview.appendChild(col);
                            }

                            reader.readAsDataURL(file);
                        }
                    }
                }
            });

            // Validation du formulaire
            const form = document.getElementById('pieceForm');
            form.addEventListener('submit', function(e) {
                let valid = true;

                // Validation basique des champs requis
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });

                // Validation du prix
                const prixField = document.getElementById('prix');
                if (prixField.value && parseFloat(prixField.value) < 0) {
                    valid = false;
                    prixField.classList.add('is-invalid');
                }

                if (!valid) {
                    e.preventDefault();
                    showAlert('Veuillez corriger les erreurs dans le formulaire.', 'danger');
                }
            });

            // Auto-format du prix
            const prixInput = document.getElementById('prix');
            prixInput.addEventListener('blur', function() {
                if (this.value) {
                    this.value = parseFloat(this.value).toFixed(2);
                }
            });
        });

        function removeImagePreview(button) {
            const col = button.closest('.col-4');
            col.remove();

            // Mettre à jour l'affichage si plus d'images
            const imagePreview = document.getElementById('imagePreview');
            if (imagePreview.children.length === 0) {
                imagePreview.style.display = 'none';
            }
        }

        function showAlert(message, type) {
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
            alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;

            document.querySelector('.card-body').insertBefore(alertDiv, document.querySelector('.card-body').firstChild);

            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }

        // Génération automatique du nom basé sur le véhicule sélectionné
        document.getElementById('vehicle_id').addEventListener('change', function() {
            const vehicleSelect = this;
            const nomInput = document.getElementById('nom');

            // Si le nom est vide, on peut suggérer un nom basé sur le véhicule
            if (!nomInput.value.trim() && vehicleSelect.value) {
                const selectedOption = vehicleSelect.options[vehicleSelect.selectedIndex];
                const vehicleText = selectedOption.text;

                // Extraire marque et modèle
                const matches = vehicleText.match(/(\w+)\s+(\w+)/);
                if (matches) {
                    // nomInput.placeholder = `Ex: Alternateur ${matches[1]} ${matches[2]}`;
                }
            }
        });
    </script>

    <style>
        .required::after {
            content: " *";
            color: #dc3545;
        }

        .card {
            border: none;
            border-radius: 10px;
        }

        .card-header {
            border-radius: 10px 10px 0 0 !important;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .btn {
            border-radius: 6px;
        }

        #imagePreview .col-4 {
            margin-bottom: 10px;
        }

        #imagePreview .img-thumbnail {
            width: 100%;
            cursor: pointer;
        }

        .list-group-item {
            border: none;
            padding: 0.75rem 0;
        }

        .alert ul {
            padding-left: 1rem;
            margin-bottom: 0;
        }

        .alert ul li {
            margin-bottom: 0.25rem;
        }

        .input-group-text {
            background-color: #f8f9fa;
        }
    </style>
@endsection
