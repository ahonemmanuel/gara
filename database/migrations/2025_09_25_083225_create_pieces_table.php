<?php
// database/migrations/2024_01_01_create_pieces_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePiecesTable extends Migration
{
    public function up()
    {
        Schema::create('pieces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicule_id')->constrained()->onDelete('cascade');
            $table->foreignId('casse_id')->constrained('users')->onDelete('cascade');
            $table->string('nom');
            $table->string('reference')->unique();
            $table->string('categorie');
            $table->decimal('prix', 10, 2);
            $table->integer('quantite')->default(1);
            $table->enum('etat', ['neuf', 'occasion', 'reconditionne']);
            $table->text('description')->nullable();
            $table->json('photos')->nullable();
            $table->boolean('disponible')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pieces');
    }
}
