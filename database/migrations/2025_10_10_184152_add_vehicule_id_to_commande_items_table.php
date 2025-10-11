<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commande_items', function (Blueprint $table) {
            // Ajouter le champ vehicule_id
            $table->foreignId('vehicule_id')
                ->nullable()
                ->after('piece_id')
                ->constrained('demandes_epaves')
                ->onDelete('set null'); // Set null au lieu de cascade pour garder l'historique

            // Rendre piece_id nullable
            $table->foreignId('piece_id')->nullable()->change();

            // Ajouter un index
            $table->index('vehicule_id');
        });
    }

    public function down(): void
    {
        Schema::table('commande_items', function (Blueprint $table) {
            $table->dropForeign(['vehicule_id']);
            $table->dropIndex(['vehicule_id']);
            $table->dropColumn('vehicule_id');
        });
    }
};
