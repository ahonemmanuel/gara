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
        Schema::table('demandes_epaves', function (Blueprint $table) {
            // On modifie la colonne 'statut' pour qu'elle soit un ENUM
            // avec une valeur par défaut 'disponible'
            $table->enum('statut', ['disponible', 'vendu', 'en_attente'])
                ->default('disponible')
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('demandes_epaves', function (Blueprint $table) {
            // On remet l'ancienne définition, par exemple un string
            $table->string('statut')->change();
        });
    }
};
