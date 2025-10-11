<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('demandes_epaves', function (Blueprint $table) {
            // Ajouter les champs pour la vente directe
            $table->boolean('disponible')->default(true)->after('statut');
            $table->integer('quantite')->default(1)->after('disponible');

            // Modifier le statut pour utiliser 'disponible' au lieu de 'en_attente'
            // Note: Vous devrez peut-être mettre à jour les enregistrements existants
        });

        // Mettre à jour les enregistrements existants
        DB::table('demandes_epaves')
            ->where('statut', 'en_attente')
            ->update([
                'statut' => 'disponible',
                'disponible' => true,
                'quantite' => 1
            ]);
    }

    public function down(): void
    {
        Schema::table('demandes_epaves', function (Blueprint $table) {
            $table->dropColumn(['disponible', 'quantite']);
        });
    }
};
