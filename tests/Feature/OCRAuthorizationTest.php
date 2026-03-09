<?php

use App\Models\User;
use App\Models\Classe;
use App\Models\Teacher;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
});

describe('OCR API Authorization Checks', function () {
    
    test('unauthenticated user cannot upload to ocr', function () {
        $response = $this->post('/teacher/bulletin/ocr/upload', [
            'file' => UploadedFile::fake()->image('bulletin.jpg'),
        ]);

        $response->assertStatus(401);
    });

    test('authenticated non-professor-principal cannot upload ocr', function () {
        $user = User::factory()->create();
        $teacher = Teacher::factory()->create(['user_id' => $user->id, 'is_prof_principal' => false]);

        $response = $this->actingAs($user)->post('/teacher/bulletin/ocr/upload', [
            'file' => UploadedFile::fake()->image('bulletin.jpg'),
        ]);

        $response->assertStatus(403)
            ->assertJson(['message' => expect()->stringContaining('principal')]);
    });

    test('professor principal can upload ocr file', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $response = $this->actingAs($user)->post('/teacher/bulletin/ocr/upload', [
            'file' => UploadedFile::fake()->image('bulletin.jpg', 800, 600),
            'classe_id' => $classe->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['text', 'confidence', 'method']);
    });

    test('admin can upload ocr file', function () {
        $user = User::factory()->create(['is_admin' => true]);
        $classe = Classe::factory()->create();

        $response = $this->actingAs($user)->post('/teacher/bulletin/ocr/upload', [
            'file' => UploadedFile::fake()->image('bulletin.jpg'),
            'classe_id' => $classe->id,
        ]);

        $response->assertStatus(200);
    });

});

describe('OCR Save Structure Authorization', function () {
    
    test('unauthenticated user cannot save ocr structure', function () {
        $response = $this->post('/teacher/bulletin/ocr/save-structure', [
            'field_coordinates' => [
                ['name' => 'test', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 20],
            ],
        ]);

        $response->assertStatus(401);
    });

    test('non-professor-principal cannot save structure', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => false,
        ]);

        $response = $this->actingAs($user)->post('/teacher/bulletin/ocr/save-structure', [
            'classe_id' => $classe->id,
            'field_coordinates' => [
                ['name' => 'test', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 20],
            ],
        ]);

        $response->assertStatus(403);
    });

    test('professor principal cannot save structure for non-owned class', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $otherClasse = Classe::factory()->create();
        
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $response = $this->actingAs($user)->post('/teacher/bulletin/ocr/save-structure', [
            'classe_id' => $otherClasse->id,
            'field_coordinates' => [
                ['name' => 'test', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 20],
            ],
        ]);

        $response->assertStatus(403);
    });

    test('professor principal can save valid structure', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $response = $this->actingAs($user)->post('/teacher/bulletin/ocr/save-structure', [
            'classe_id' => $classe->id,
            'field_coordinates' => [
                ['name' => 'Mathématiques', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 20],
                ['name' => 'Français', 'x' => 10, 'y' => 50, 'width' => 100, 'height' => 20],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'zone_count', 'zones']);
    });

});

describe('Field Coordinate Validation', function () {
    
    test('invalid coordinate values are rejected', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        // Missing required field
        $response = $this->actingAs($user)->post('/teacher/bulletin/ocr/save-structure', [
            'classe_id' => $classe->id,
            'field_coordinates' => [
                ['name' => 'Math', 'x' => 10, 'y' => 10, 'width' => 100],
            ],
        ]);

        $response->assertStatus(422);
    });

    test('non-numeric coordinates are rejected', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $response = $this->actingAs($user)->post('/teacher/bulletin/ocr/save-structure', [
            'classe_id' => $classe->id,
            'field_coordinates' => [
                ['name' => 'Math', 'x' => 'invalid', 'y' => 10, 'width' => 100, 'height' => 20],
            ],
        ]);

        $response->assertStatus(422);
    });

    test('negative coordinates are rejected', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $response = $this->actingAs($user)->post('/teacher/bulletin/ocr/save-structure', [
            'classe_id' => $classe->id,
            'field_coordinates' => [
                ['name' => 'Math', 'x' => -10, 'y' => 10, 'width' => 100, 'height' => 20],
            ],
        ]);

        $response->assertStatus(422);
    });

});
