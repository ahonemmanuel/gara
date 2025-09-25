<?php
// database/migrations/2024_01_01_create_commandes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommandesTable extends Migration
{
    public function up()
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('casse_id')->constrained('users')->onDelete('cascade');
            $table->string('numero_commande')->unique();
            $table->enum('statut', ['en_attente', 'confirmee', 'preparation', 'expediee', 'livree', 'annulee'])->default('en_attente');
            $table->decimal('total', 10, 2);
            $table->text('adresse_livraison');
            $table->enum('methode_paiement', ['carte', 'paypal', 'virement', 'especes']);
            $table->enum('statut_paiement', ['en_attente', 'paye', 'echec', 'rembourse'])->default('en_attente');
            $table->datetime('date_commande');
            $table->datetime('date_livraison_estimee')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('commandes');
    }
}
