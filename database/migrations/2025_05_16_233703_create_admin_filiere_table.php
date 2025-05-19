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
        Schema::create('admin_filiere', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id'); // ID de l'administrateur
            $table->unsignedBigInteger('filiere_id'); // ID de la filière
            $table->timestamps();
            
            // Contraintes de clés étrangères
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('filiere_id')->references('id')->on('filieres')->onDelete('cascade');
            
            // Garantir qu'un admin ne peut pas être associé deux fois à la même filière
            $table->unique(['user_id', 'filiere_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_filiere');
    }
};
