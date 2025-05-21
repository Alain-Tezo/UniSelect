<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Migration fusionnée : 
     * - Création de la table filières
     * - Retrait de places_disponibles (2025_05_03_110000)
     * - Ajout de est_selective (2025_05_03_113740)
     */
    public function up()
    {
        Schema::create('filieres', function (Blueprint $table) {
            $table->id();
            $table->string('nom'); // Informatique, ICT4D, etc.
            $table->text('description')->nullable();
            $table->boolean('est_selective')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('filieres');
    }
};
