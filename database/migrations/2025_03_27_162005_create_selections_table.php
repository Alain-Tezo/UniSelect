<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Migration fusionnée : 
     * - Création de la table selections
     * - Modification de critere_selection_id pour permettre NULL (2025_05_03_150000)
     */
    public function up()
    {
        Schema::create('selections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('niveau_id')->constrained();
            $table->foreignId('filiere_id')->constrained();
            $table->foreignId('critere_selection_id')->nullable()->constrained('criteres_selection');
            $table->foreignId('created_by')->constrained('users');
            $table->integer('nombre_etudiants_selectionnes');
            $table->timestamp('date_selection');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('selections');
    }
};
