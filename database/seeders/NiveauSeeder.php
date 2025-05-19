<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Niveau;

class NiveauSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Niveau::create(['nom' => 'Licence 1', 'description' => 'Première année de licence']);
        Niveau::create(['nom' => 'Licence 2', 'description' => 'Deuxième année de licence']);
        Niveau::create(['nom' => 'Licence 3', 'description' => 'Troisième année de licence']);
        Niveau::create(['nom' => 'Master 1', 'description' => 'Première année de master']);
        Niveau::create(['nom' => 'Master 2', 'description' => 'Deuxième année de master']);
    }
}
