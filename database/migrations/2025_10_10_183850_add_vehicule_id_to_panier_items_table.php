<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('panier_items', function (Blueprint $table) {
            // Ajouter le champ vehicule_id
            $table->foreignId('vehicule_id')
                ->nullable()
                ->after('piece_id')
                ->constrained('demandes_epaves')
                ->onDelete('cascade');

            // Rendre piece_id nullable car maintenant on peut avoir soit piece_id soit vehicule_id
            $table->foreignId('piece_id')->nullable()->change();

            // Ajouter un index pour améliorer les performances
            $table->index('vehicule_id');
        });
    }

    public function down(): void
    {
        Schema::table('panier_items', function (Blueprint $table) {
            $table->dropForeign(['vehicule_id']);
            $table->dropIndex(['vehicule_id']);
            $table->dropColumn('vehicule_id');
        });
    }
};
