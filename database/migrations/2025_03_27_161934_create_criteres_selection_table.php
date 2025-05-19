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
        Schema::create('criteres_selection', function (Blueprint $table) {
            $table->id();
            $table->foreignId('niveau_id')->constrained();
            $table->foreignId('filiere_id')->constrained();

            // Nouveaux champs JSON pour le système de critères dynamiques
            $table->json('criteres_json')->nullable();
            $table->json('bonus_json')->nullable();

            $table->timestamps();

            // Contrainte d'unicité pour éviter les doublons
            $table->unique(['niveau_id', 'filiere_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('criteres_selection');
    }
};
