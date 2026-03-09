<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Classes;

class ClassSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            [
                'name' => '6ème A',
                'level' => '6ème',
                'section' => 'A',
                'capacity' => 35,
                'description' => 'Classe 6ème A'
            ],
            [
                'name' => '6ème B',
                'level' => '6ème',
                'section' => 'B',
                'capacity' => 33,
                'description' => 'Classe 6ème B'
            ],
            [
                'name' => '5ème A',
                'level' => '5ème',
                'section' => 'A',
                'capacity' => 34,
                'description' => 'Classe 5ème A'
            ],
            [
                'name' => '5ème B',
                'level' => '5ème',
                'section' => 'B',
                'capacity' => 32,
                'description' => 'Classe 5ème B'
            ],
            [
                'name' => '4ème A',
                'level' => '4ème',
                'section' => 'A',
                'capacity' => 36,
                'description' => 'Classe 4ème A'
            ],
            [
                'name' => '3ème A',
                'level' => '3ème',
                'section' => 'A',
                'capacity' => 30,
                'description' => 'Classe 3ème A'
            ],
            [
                'name' => '2nde A',
                'level' => '2nde',
                'section' => 'A',
                'capacity' => 31,
                'description' => 'Classe 2nde A'
            ],
            [
                'name' => '1ère S',
                'level' => '1ère',
                'section' => 'S',
                'capacity' => 28,
                'description' => 'Classe 1ère Scientifique'
            ],
            [
                'name' => '1ère L',
                'level' => '1ère',
                'section' => 'L',
                'capacity' => 26,
                'description' => 'Classe 1ère Littéraire'
            ],
            [
                'name' => 'Terminale S',
                'level' => 'Terminale',
                'section' => 'S',
                'capacity' => 29,
                'description' => 'Classe Terminale Scientifique'
            ]
        ];

        foreach ($classes as $class) {
            Classes::create($class);
        }
    }
}
