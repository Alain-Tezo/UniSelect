<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            $table->boolean('est_selective')->default(false)->after('description');
        });
        
        // Mettre à jour les filières existantes pour marquer celles qui sont sélectives
        DB::table('filieres')->where('nom', 'Informatique')->update(['est_selective' => true]);
        DB::table('filieres')->where('nom', 'ICT4D')->update(['est_selective' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('filieres', function (Blueprint $table) {
            $table->dropColumn('est_selective');
        });
    }
};
