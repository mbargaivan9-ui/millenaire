<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Subject;

class SubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = [
            [
                'name' => 'Mathématiques',
                'code' => 'MATH',
                'coefficient' => 3.0,
                'department' => 'Sciences',
                'description' => 'Cours de mathématiques fondamentales et avancées'
            ],
            [
                'name' => 'Français',
                'code' => 'FR',
                'coefficient' => 2.5,
                'department' => 'Littéraire',
                'description' => 'Français langue et littérature'
            ],
            [
                'name' => 'Anglais',
                'code' => 'ENG',
                'coefficient' => 2.0,
                'department' => 'Langues',
                'description' => 'Anglais : Communication et littérature'
            ],
            [
                'name' => 'Histoire-Géographie',
                'code' => 'HISTGEO',
                'coefficient' => 2.0,
                'department' => 'Sciences humaines',
                'description' => 'Histoire et géographie du monde'
            ],
            [
                'name' => 'Physique-Chimie',
                'code' => 'PC',
                'coefficient' => 2.5,
                'department' => 'Sciences',
                'description' => 'Physique et chimie expérimentale'
            ],
            [
                'name' => 'Sciences de la Vie',
                'code' => 'SVT',
                'coefficient' => 1.5,
                'department' => 'Sciences',
                'description' => 'Biologie et écologie'
            ],
            [
                'name' => 'Éducation Physique',
                'code' => 'EPS',
                'coefficient' => 1.0,
                'department' => 'Sport',
                'description' => 'Éducation physique et sportive'
            ],
            [
                'name' => 'Philosophie',
                'code' => 'PHIL',
                'coefficient' => 3.0,
                'department' => 'Littéraire',
                'description' => 'Introduction à la philosophie'
            ],
            [
                'name' => 'Informatique',
                'code' => 'INFO',
                'coefficient' => 1.5,
                'department' => 'Sciences',
                'description' => 'Informatique et programmation'
            ],
            [
                'name' => 'Arts Plastiques',
                'code' => 'ART',
                'coefficient' => 1.0,
                'department' => 'Arts',
                'description' => 'Expression artistique et créativité'
            ],
            [
                'name' => 'Musique',
                'code' => 'MUS',
                'coefficient' => 1.0,
                'department' => 'Arts',
                'description' => 'Musique et harmonie'
            ],
            [
                'name' => 'Technologie',
                'code' => 'TECH',
                'coefficient' => 1.5,
                'department' => 'Sciences',
                'description' => 'Technologie et innovation'
            ]
        ];

        foreach ($subjects as $subject) {
            Subject::create($subject);
        }
    }
}
