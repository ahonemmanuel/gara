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
        // Ajouter prix_unitaire à panier_items si elle n'existe pas
        if (!Schema::hasColumn('panier_items', 'prix_unitaire')) {
            Schema::table('panier_items', function (Blueprint $table) {
                $table->decimal('prix_unitaire', 10, 2)->nullable()->after('quantite');
            });
        }

        // Ajouter vehicule_id à panier_items si elle n'existe pas
        if (!Schema::hasColumn('panier_items', 'vehicule_id')) {
            Schema::table('panier_items', function (Blueprint $table) {
                $table->unsignedBigInteger('vehicule_id')->nullable()->after('piece_id');
                $table->foreign('vehicule_id')
                    ->references('id')
                    ->on('demandes_epaves')
                    ->onDelete('cascade');
            });
        }

        // Ajouter vehicule_id à commande_items si elle n'existe pas
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
        if (Schema::hasColumn('panier_items', 'prix_unitaire')) {
            Schema::table('panier_items', function (Blueprint $table) {
                $table->dropColumn('prix_unitaire');
            });
        }

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
