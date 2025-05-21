<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Filiere;

class FiliereSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Filiere::create([
            'nom' => 'Informatique',
            'description' => 'Formation en informatique et développement logiciel',
            'est_selective' => true,
        ]);

        Filiere::create([
            'nom' => 'ICT4D',
            'description' => 'Information and Communication Technologies for Development',
            'est_selective' => true,
        ]);

        Filiere::create([
            'nom' => 'Mathématiques',
            'description' => 'Formation en mathématiques fondamentales et appliquées',
        ]);

        Filiere::create([
            'nom' => 'Physique',
            'description' => 'Formation en physique fondamentale et appliquée',
        ]);

        Filiere::create([
            'nom' => 'Chimie',
            'description' => 'Formation en chimie fondamentale et appliquée',
        ]);

        Filiere::create([
            'nom' => 'Biosciences',
            'description' => 'Formation en biologie et sciences de la vie',
        ]);
    }
}
