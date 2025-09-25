<?php
// database/migrations/2024_01_01_create_vehicules_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVehiculesTable extends Migration
{
    public function up()
    {
        Schema::create('vehicules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('casse_id')->constrained('users')->onDelete('cascade');
            $table->string('marque');
            $table->string('modele');
            $table->integer('annee');
            $table->string('immatriculation')->unique();
            $table->string('type_vehicule');
            $table->date('date_arrivee');
            $table->enum('etat', ['excellent', 'bon', 'moyen', 'mauvais']);
            $table->json('photos')->nullable();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vehicules');
    }
}
