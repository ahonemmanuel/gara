<?php
// app/Models/Piece.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Piece extends Model
{
    protected $fillable = [
        'marque_id',
        'modele_id',
        'nom',
        'description',
        'prix',
        'quantite',
        'etat',
        'photos',
        'reference_constructeur',
        'compatible_avec',
        'disponible',
        'ville',  // Ajouté
        'user_id',
    ];

    protected $casts = [
        'photos' => 'array',
        'disponible' => 'boolean'
    ];

    public function marque(): BelongsTo
    {
        return $this->belongsTo(Marque::class);
    }

    public function modele(): BelongsTo
    {
        return $this->belongsTo(Modele::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function casse(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function commandeItems(): HasMany
    {
        return $this->hasMany(CommandeItem::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    /**
     * Boot du modèle pour synchroniser la ville lors de la création
     */
    protected static function booted()
    {
        // Lors de la création d'une pièce, récupérer automatiquement la ville de l'utilisateur
        static::creating(function ($piece) {
            if (empty($piece->ville) && $piece->user) {
                $piece->ville = $piece->user->ville;
            }
        });

        // Lors de la mise à jour, si user_id change, mettre à jour la ville
        static::updating(function ($piece) {
            if ($piece->isDirty('user_id') && $piece->user) {
                $piece->ville = $piece->user->ville;
            }
        });
    }
}

// database/migrations/2025_10_07_104356_ensure_ville_in_pieces_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pieces', function (Blueprint $table) {
            // Vérifier si la colonne existe déjà
            if (!Schema::hasColumn('pieces', 'ville')) {
                $table->string('ville')->nullable()->after('compatible_avec')->index();
            }
        });

        // Synchroniser les villes existantes
        DB::statement('
            UPDATE pieces
            SET ville = (SELECT ville FROM users WHERE users.id = pieces.user_id)
            WHERE ville IS NULL
        ');
    }

    public function down(): void
    {
        Schema::table('pieces', function (Blueprint $table) {
            $table->dropColumn('ville');
        });
    }
};
