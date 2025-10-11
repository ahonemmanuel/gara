<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la colonne n'existe pas déjà dans panier_items
        if (!Schema::hasColumn('panier_items', 'vehicule_id')) {
            Schema::table('panier_items', function (Blueprint $table) {
                $table->unsignedBigInteger('vehicule_id')->nullable()->after('piece_id');
                $table->foreign('vehicule_id')
                    ->references('id')
                    ->on('demandes_epaves')
                    ->onDelete('cascade');
            });
        }

        // Vérifier si la colonne n'existe pas déjà dans commande_items
        if (!Schema::hasColumn('commande_items', 'vehicule_id')) {
            Schema::table('commande_items', function (Blueprint $table) {
                $table->unsignedBigInteger('vehicule_id')->nullable()->after('piece_id');
                $table->foreign('vehicule_id')
                    ->references('id')
                    ->on('demandes_epaves')
                    ->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('panier_items', 'vehicule_id')) {
            Schema::table('panier_items', function (Blueprint $table) {
                $table->dropForeign(['vehicule_id']);
                $table->dropColumn('vehicule_id');
            });
        }

        if (Schema::hasColumn('commande_items', 'vehicule_id')) {
            Schema::table('commande_items', function (Blueprint $table) {
                $table->dropForeign(['vehicule_id']);
                $table->dropColumn('vehicule_id');
            });
        }
    }
};
