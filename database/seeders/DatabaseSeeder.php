<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Marque;
use App\Models\Modele;
use App\Models\NomPiece;
use App\Models\Vehicle;
use App\Models\Piece;
use App\Models\DemandeEpave;
use App\Models\OffreEpave;
use App\Models\Panier;
use App\Enums\UserRole;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer les marques
        $this->createMarques();

        // 2. Créer les modèles
        $this->createModeles();

        // 3. Créer les noms de pièces
        $this->createNomsPieces();

        // 4. Créer les utilisateurs
        $this->createUsers();

        // 5. Créer les véhicules
        $this->createVehicles();

        // 6. Créer les pièces
        $this->createPieces();

        // 7. Créer les demandes d'épaves
        $this->createDemandesEpaves();

        // 8. Créer les offres
        $this->createOffres();

        $this->command->info('✅ Toutes les données de test ont été créées avec succès!');
    }

    private function createMarques(): void
    {
        $marques = [
            'Toyota', 'Peugeot', 'Renault', 'Volkswagen', 'Mercedes-Benz',
            'BMW', 'Audi', 'Ford', 'Nissan', 'Hyundai',
            'Citroën', 'Opel', 'Fiat', 'Honda', 'Mazda',
            'Kia', 'Chevrolet', 'Seat', 'Skoda', 'Volvo'
        ];

        foreach ($marques as $marque) {
            Marque::create([
                'nom' => $marque,
                'is_active' => true
            ]);
        }

        $this->command->info('✓ Marques créées');
    }

    private function createModeles(): void
    {
        $modelesParMarque = [
            'Toyota' => ['Corolla', 'Camry', 'RAV4', 'Yaris', 'Hilux', 'Land Cruiser'],
            'Peugeot' => ['208', '308', '3008', '5008', '2008', '508'],
            'Renault' => ['Clio', 'Megane', 'Captur', 'Kadjar', 'Scenic', 'Twingo'],
            'Volkswagen' => ['Golf', 'Polo', 'Passat', 'Tiguan', 'Touareg', 'Jetta'],
            'Mercedes-Benz' => ['Classe A', 'Classe C', 'Classe E', 'GLA', 'GLC', 'GLE'],
            'BMW' => ['Série 1', 'Série 3', 'Série 5', 'X1', 'X3', 'X5'],
            'Audi' => ['A3', 'A4', 'A6', 'Q3', 'Q5', 'Q7'],
            'Ford' => ['Fiesta', 'Focus', 'Mondeo', 'Kuga', 'Ranger', 'Explorer'],
            'Nissan' => ['Micra', 'Qashqai', 'Juke', 'X-Trail', 'Navara', 'Patrol'],
            'Hyundai' => ['i10', 'i20', 'i30', 'Tucson', 'Santa Fe', 'Kona'],
        ];

        foreach ($modelesParMarque as $marqueNom => $modeles) {
            $marque = Marque::where('nom', $marqueNom)->first();

            if ($marque) {
                foreach ($modeles as $modeleNom) {
                    Modele::create([
                        'marque_id' => $marque->id,
                        'nom' => $modeleNom,
                        'is_active' => true
                    ]);
                }
            }
        }

        $this->command->info('✓ Modèles créés');
    }

    private function createNomsPieces(): void
    {
        $pieces = [
            // Moteur
            ['nom' => 'Moteur complet', 'categorie' => 'Moteur'],
            ['nom' => 'Culasse', 'categorie' => 'Moteur'],
            ['nom' => 'Bloc moteur', 'categorie' => 'Moteur'],
            ['nom' => 'Turbocompresseur', 'categorie' => 'Moteur'],
            ['nom' => 'Alternateur', 'categorie' => 'Moteur'],
            ['nom' => 'Démarreur', 'categorie' => 'Moteur'],

            // Transmission
            ['nom' => 'Boîte de vitesses', 'categorie' => 'Transmission'],
            ['nom' => 'Embrayage', 'categorie' => 'Transmission'],
            ['nom' => 'Cardan', 'categorie' => 'Transmission'],
            ['nom' => 'Différentiel', 'categorie' => 'Transmission'],

            // Freinage
            ['nom' => 'Étrier de frein', 'categorie' => 'Freinage'],
            ['nom' => 'Disque de frein', 'categorie' => 'Freinage'],
            ['nom' => 'Maître-cylindre', 'categorie' => 'Freinage'],

            // Suspension
            ['nom' => 'Amortisseur', 'categorie' => 'Suspension'],
            ['nom' => 'Triangle de suspension', 'categorie' => 'Suspension'],
            ['nom' => 'Rotule de direction', 'categorie' => 'Suspension'],

            // Carrosserie
            ['nom' => 'Capot', 'categorie' => 'Carrosserie'],
            ['nom' => 'Pare-chocs avant', 'categorie' => 'Carrosserie'],
            ['nom' => 'Pare-chocs arrière', 'categorie' => 'Carrosserie'],
            ['nom' => 'Aile avant', 'categorie' => 'Carrosserie'],
            ['nom' => 'Porte avant', 'categorie' => 'Carrosserie'],
            ['nom' => 'Porte arrière', 'categorie' => 'Carrosserie'],
            ['nom' => 'Hayon', 'categorie' => 'Carrosserie'],

            // Optique
            ['nom' => 'Phare avant', 'categorie' => 'Optique'],
            ['nom' => 'Feu arrière', 'categorie' => 'Optique'],
            ['nom' => 'Rétroviseur', 'categorie' => 'Optique'],

            // Intérieur
            ['nom' => 'Siège avant', 'categorie' => 'Intérieur'],
            ['nom' => 'Siège arrière', 'categorie' => 'Intérieur'],
            ['nom' => 'Tableau de bord', 'categorie' => 'Intérieur'],
            ['nom' => 'Volant', 'categorie' => 'Intérieur'],

            // Pneumatiques
            ['nom' => 'Jante aluminium', 'categorie' => 'Pneumatiques'],
            ['nom' => 'Pneu', 'categorie' => 'Pneumatiques'],
        ];

        foreach ($pieces as $piece) {
            NomPiece::create($piece + ['is_active' => true]);
        }

        $this->command->info('✓ Noms de pièces créés');
    }

    private function createUsers(): void
    {
        // Admin
        User::create([
            'name' => 'Administrateur',
            'email' => 'admin@casse.tg',
            'password' => Hash::make('password'),
            'role' => UserRole::ADMIN,
            'ville' => 'Lomé',
            'telephone' => '+228 90 00 00 00',
            'latitude' => 6.1319,
            'longitude' => 1.2228,
            'email_verified_at' => now(),
            'approved' => true,
            'approved_at' => now(),
        ]);

        // Casses (5)
        $villesLome = [
            ['nom' => 'Bè', 'lat' => 6.1401, 'lon' => 1.2314],
            ['nom' => 'Hédzranawoé', 'lat' => 6.1567, 'lon' => 1.2115],
            ['nom' => 'Nyékonakpoé', 'lat' => 6.1286, 'lon' => 1.2342],
            ['nom' => 'Adidogomé', 'lat' => 6.1542, 'lon' => 1.2089],
            ['nom' => 'Agoè', 'lat' => 6.1956, 'lon' => 1.1981],
        ];

        foreach ($villesLome as $index => $ville) {
            $user = User::create([
                'name' => "Casse Auto {$ville['nom']}",
                'email' => "casse{$index}@example.com",
                'password' => Hash::make('password'),
                'role' => UserRole::CASSE,
                'ville' => $ville['nom'] . ', Lomé',
                'telephone' => '+228 90 ' . str_pad($index + 10, 2, '0', STR_PAD_LEFT) . ' 00 00',
                'latitude' => $ville['lat'],
                'longitude' => $ville['lon'],
                'email_verified_at' => now(),
                'approved' => true,
                'approved_at' => now(),
            ]);
        }

        // Clients (10)
        $prenoms = ['Jean', 'Marie', 'Pierre', 'Sophie', 'Kofi', 'Ama', 'Kwame', 'Akossiwa', 'Edem', 'Kafui'];
        $noms = ['Dupont', 'Martin', 'Bernard', 'Petit', 'Agbéko', 'Koffi', 'Mensah', 'Addo', 'Tété', 'Dzifa'];

        for ($i = 0; $i < 10; $i++) {
            $user = User::create([
                'name' => $prenoms[$i] . ' ' . $noms[$i],
                'email' => "client{$i}@example.com",
                'password' => Hash::make('password'),
                'role' => UserRole::CLIENT,
                'ville' => 'Lomé',
                'telephone' => '+228 90 ' . str_pad($i + 20, 2, '0', STR_PAD_LEFT) . ' 00 00',
                'latitude' => 6.1319 + (rand(-100, 100) / 1000),
                'longitude' => 1.2228 + (rand(-100, 100) / 1000),
                'email_verified_at' => now(),
                'approved' => true,
                'approved_at' => now(),
            ]);

            // Créer un panier pour chaque client
            Panier::create(['user_id' => $user->id]);
        }

        $this->command->info('✓ Utilisateurs créés (1 admin, 5 casses, 10 clients)');
    }

    private function createVehicles(): void
    {
        $casses = User::where('role', UserRole::CASSE)->get();
        $marques = Marque::with('modeles')->get();
        $etats = ['bon', 'moyen', 'mauvais', 'epave'];
        $carburants = ['essence', 'diesel', 'hybride'];
        $couleurs = ['Blanc', 'Noir', 'Gris', 'Rouge', 'Bleu', 'Vert'];

        foreach ($casses as $casse) {
            // Créer 3-5 véhicules par casse
            $nbVehicules = rand(3, 5);

            for ($i = 0; $i < $nbVehicules; $i++) {
                $marque = $marques->random();
                $modele = $marque->modeles->random();

                Vehicle::create([
                    'casse_id' => $casse->id,
                    'marque' => $marque->nom,
                    'modele' => $modele->nom,
                    'annee' => rand(2000, 2023),
                    'numero_chassis' => 'VIN' . strtoupper(substr(md5(rand()), 0, 14)),
                    'numero_plaque' => 'TG-' . rand(1000, 9999) . '-' . chr(rand(65, 90)) . chr(rand(65, 90)),
                    'couleur' => $couleurs[array_rand($couleurs)],
                    'carburant' => $carburants[array_rand($carburants)],
                    'transmission' => rand(0, 1) ? 'Manuelle' : 'Automatique',
                    'kilometrage' => rand(50000, 300000),
                    'etat' => $etats[array_rand($etats)],
                    'date_arrivee' => now()->subDays(rand(1, 180)),
                    'prix_epave' => rand(500000, 5000000),
                    'vendu' => rand(0, 10) > 8, // 20% vendus
                    'description' => 'Véhicule en état ' . $etats[array_rand($etats)] . '. Pièces disponibles.',
                ]);
            }
        }

        $this->command->info('✓ Véhicules créés');
    }

    private function createPieces(): void
    {
        $vehicles = Vehicle::all();
        $nomsPieces = NomPiece::all();
        $etats = ['neuf', 'bon', 'moyen', 'usage'];

        foreach ($vehicles as $vehicle) {
            // Créer 5-15 pièces par véhicule
            $nbPieces = rand(5, 15);

            for ($i = 0; $i < $nbPieces; $i++) {
                $nomPiece = $nomsPieces->random();
                $marque = Marque::where('nom', $vehicle->marque)->first();
                $modele = Modele::where('marque_id', $marque->id)
                    ->where('nom', $vehicle->modele)
                    ->first();

                Piece::create([
                    'user_id' => $vehicle->casse_id,
                    'nom_piece_id' => $nomPiece->id,
                    'marque_id' => $marque->id,
                    'modele_id' => $modele->id,
                    'nom' => $nomPiece->nom,
                    'description' => "Pièce d'origine {$vehicle->marque} {$vehicle->modele} {$vehicle->annee}",
                    'prix' => rand(10000, 500000),
                    'quantite' => rand(0, 10),
                    'etat' => $etats[array_rand($etats)],
                    'reference_constructeur' => 'REF-' . strtoupper(substr(md5(rand()), 0, 8)),
                    'compatible_avec' => "{$vehicle->marque} {$vehicle->modele}",
                    'disponible' => rand(0, 10) > 2, // 80% disponibles
                    'ville' => $vehicle->casse->ville,
                    'photos' => null,
                ]);
            }
        }

        $this->command->info('✓ Pièces créées');
    }

    private function createDemandesEpaves(): void
    {
        $clients = User::where('role', UserRole::CLIENT)->get();
        $casses = User::where('role', UserRole::CASSE)->get();
        $allUsers = $clients->merge($casses);

        $marques = Marque::with('modeles')->get();
        $types = ['vehicule', 'epave'];
        $etats = ['bon', 'moyen', 'mauvais', 'epave'];
        $carburants = ['essence', 'diesel', 'hybride', 'electrique'];
        $couleurs = ['Blanc', 'Noir', 'Gris', 'Rouge', 'Bleu', 'Vert', 'Argent'];
        $statuts = ['en_attente', 'en_attente', 'en_attente', 'vendu']; // Plus de "en_attente"

        foreach ($allUsers->take(8) as $user) {
            // Créer 1-2 demandes par utilisateur
            $nbDemandes = rand(1, 2);

            for ($i = 0; $i < $nbDemandes; $i++) {
                $marque = $marques->random();
                $modele = $marque->modeles->random();
                $type = $types[array_rand($types)];

                DemandeEpave::create([
                    'user_id' => $user->id,
                    'type' => $type,
                    'marque' => $marque->nom,
                    'modele' => $modele->nom,
                    'annee' => rand(2000, 2023),
                    'numero_chassis' => 'VIN' . strtoupper(substr(md5(rand()), 0, 14)),
                    'numero_plaque' => 'TG-' . rand(1000, 9999) . '-' . chr(rand(65, 90)) . chr(rand(65, 90)),
                    'couleur' => $couleurs[array_rand($couleurs)],
                    'carburant' => $carburants[array_rand($carburants)],
                    'kilometrage' => rand(50000, 300000),
                    'etat' => $etats[array_rand($etats)],
                    'prix_souhaite' => rand(500000, 5000000),
                    'description' => $type === 'vehicule'
                        ? "Véhicule en bon état de fonctionnement. Raison de vente : changement de voiture."
                        : "Véhicule accidenté, pièces récupérables. Moteur et transmission en bon état.",
                    'photos' => null,
                    'telephone_contact' => $user->telephone,
                    'adresse' => $user->ville,
                    'statut' => $statuts[array_rand($statuts)],
                    'created_at' => now()->subDays(rand(1, 60)),
                ]);
            }
        }

        $this->command->info('✓ Demandes d\'épaves créées');
    }

    private function createOffres(): void
    {
        $demandes = DemandeEpave::where('statut', 'en_attente')->get();
        $casses = User::where('role', UserRole::CASSE)->get();
        $clients = User::where('role', UserRole::CLIENT)->get();

        // Mélanger casses et clients pour les offres
        $acheteurs = $casses->merge($clients->take(3));

        foreach ($demandes as $demande) {
            // 50% des demandes reçoivent des offres
            if (rand(0, 1) === 0) {
                // Créer 1-3 offres par demande
                $nbOffres = rand(1, 3);
                $offrants = $acheteurs->where('id', '!=', $demande->user_id)
                    ->random(min($nbOffres, $acheteurs->count() - 1));

                foreach ($offrants as $offrant) {
                    $prixOffert = $demande->prix_souhaite
                        ? $demande->prix_souhaite * rand(70, 95) / 100
                        : rand(300000, 4000000);

                    OffreEpave::create([
                        'demande_epave_id' => $demande->id,
                        'user_id' => $offrant->id,
                        'prix_offert' => $prixOffert,
                        'message' => "Je suis intéressé par votre " . ($demande->type === 'vehicule' ? 'véhicule' : 'épave') . ". Prix proposé : " . number_format($prixOffert, 0, ',', ' ') . " FCFA.",
                        'statut' => 'en_attente',
                        'created_at' => $demande->created_at->addDays(rand(1, 5)),
                    ]);
                }
            }
        }

        // Accepter quelques offres pour les demandes "vendu"
        $demandesVendues = DemandeEpave::where('statut', 'vendu')->get();
        foreach ($demandesVendues as $demande) {
            $acheteur = $acheteurs->where('id', '!=', $demande->user_id)->random();

            OffreEpave::create([
                'demande_epave_id' => $demande->id,
                'user_id' => $acheteur->id,
                'prix_offert' => $demande->prix_souhaite * rand(85, 100) / 100,
                'message' => "Offre acceptée pour ce véhicule.",
                'statut' => 'accepte',
                'created_at' => $demande->created_at->addDays(rand(1, 3)),
            ]);
        }

        $this->command->info('✓ Offres créées');
    }
}
