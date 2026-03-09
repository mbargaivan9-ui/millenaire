<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        // Create Admin
        User::create([
            'name' => 'Administrateur',
            'email' => 'admin@millénaire.local',
            'password' => Hash::make('password123'),
            'role' => 'admin',
            'gender' => 'M',
            'phoneNumber' => '237671234567',
            'is_active' => true
        ]);

        // Create Teachers (10)
        for ($i = 1; $i <= 10; $i++) {
            User::create([
                'name' => 'Prof ' . $faker->firstName(),
                'email' => 'teacher' . $i . '@millénaire.local',
                'password' => Hash::make('password123'),
                'role' => 'teacher',
                'gender' => $faker->randomElement(['M', 'F']),
                'phoneNumber' => '237' . rand(600000000, 699999999),
                'date_of_birth' => $faker->dateTimeBetween('-50 years', '-25 years'),
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'is_active' => true
            ]);
        }

        // Create Parents (15)
        for ($i = 1; $i <= 15; $i++) {
            User::create([
                'name' => $faker->firstName() . ' ' . $faker->lastName(),
                'email' => 'parent' . $i . '@millénaire.local',
                'password' => Hash::make('password123'),
                'role' => 'parent',
                'gender' => $faker->randomElement(['M', 'F']),
                'phoneNumber' => '237' . rand(600000000, 699999999),
                'date_of_birth' => $faker->dateTimeBetween('-70 years', '-35 years'),
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'is_active' => true
            ]);
        }

        // Create Students (150) with class assignment
        $classIds = \App\Models\Classes::pluck('id')->toArray();
        
        for ($i = 1; $i <= 150; $i++) {
            User::create([
                'name' => $faker->firstName() . ' ' . $faker->lastName(),
                'email' => 'student' . $i . '@millénaire.local',
                'password' => Hash::make('password123'),
                'role' => 'student',
                'gender' => $faker->randomElement(['M', 'F']),
                'phoneNumber' => '237' . rand(600000000, 699999999),
                'date_of_birth' => $faker->dateTimeBetween('-17 years', '-10 years'),
                'address' => $faker->streetAddress(),
                'city' => $faker->city(),
                'class_id' => $faker->randomElement($classIds),
                'is_active' => true
            ]);
        }
    }
}
