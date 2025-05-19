<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateSelectionsTableAllowNullCritere extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('selections', function (Blueprint $table) {
            // Modifier la colonne critere_selection_id pour permettre NULL
            $table->unsignedBigInteger('critere_selection_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('selections', function (Blueprint $table) {
            // Rétablir la colonne comme non nullable
            $table->unsignedBigInteger('critere_selection_id')->nullable(false)->change();
        });
    }
}
