<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('etudiants', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('prenom');
            $table->date('date_naissance');
            $table->string('email')->unique();
            $table->enum('sexe', ['M', 'F']);
            $table->foreignId('niveau_id')->constrained();
            $table->string('etablissement_precedent')->nullable();
            $table->string('serie_bac')->nullable();
            $table->float('moyenne_bac', 5, 2)->nullable();
            $table->float('note_math', 5, 2)->nullable();
            $table->float('note_physique', 5, 2)->nullable();
            $table->float('note_svteehb', 5, 2)->nullable();
            $table->float('note_informatique', 5, 2)->nullable();
            $table->string('universite_precedente')->nullable();
            $table->float('mgp', 4, 2)->nullable();
            $table->foreignId('filiere_precedente_id')->nullable()->constrained('filieres');
            $table->foreignId('premier_choix_id')->constrained('filieres');
            $table->foreignId('deuxieme_choix_id')->constrained('filieres');
            $table->foreignId('troisieme_choix_id')->constrained('filieres');
            $table->enum('region_origine', [
                'nord', 'sud', 'est', 'ouest', 'centre',
                'Adamaoua', 'littoral', 'extrême nord', 'nord-ouest', 'sud-ouest'
            ]);
            $table->boolean('est_selectionne')->default(false);
            $table->foreignId('filiere_selectionnee_id')->nullable()->constrained('filieres');
            $table->float('points_selection', 10, 2)->default(0);
            $table->json('details_selection')->nullable();
            $table->boolean('notification_envoyee')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('etudiants');
    }
};
