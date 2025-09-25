<?php



// 2024_01_06_000001_create_paniers_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paniers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('piece_id')->constrained()->onDelete('cascade');
            $table->integer('quantite');
            $table->timestamps();
        });

        Schema::create('vente_epaves', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('marque');
            $table->string('modele');
            $table->integer('annee');
            $table->string('immatriculation')->nullable();
            $table->text('description');
            $table->enum('etat', ['roulant', 'non_roulant', 'accidente', 'incendie']);
            $table->json('photos')->nullable();
            $table->decimal('prix_souhaite', 10, 2)->nullable();
            $table->enum('statut', ['en_attente', 'evaluee', 'acceptee', 'refusee', 'vendue'])->default('en_attente');
            $table->text('notes_evaluation')->nullable();
            $table->decimal('prix_propose', 10, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->json('data')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        Schema::create('recherches_sauvegardees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->string('nom');
            $table->json('criteres');
            $table->boolean('notifications_activees')->default(false);
            $table->timestamps();
        });
        Schema::create('favoris', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('piece_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->unique(['client_id', 'piece_id']);
        });



    }

    public function down(): void
    {
        Schema::dropIfExists('paniers');
        Schema::dropIfExists('vente_epaves');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('recherches_sauvegardees');
        Schema::dropIfExists('favoris');


    }
};

