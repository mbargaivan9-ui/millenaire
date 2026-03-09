<?php

use App\Models\User;
use App\Models\Classe;
use App\Models\Teacher;
use App\Models\BulletinStructure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
    Storage::fake('local');
});

describe('OCR Wizard Complete Flow', function () {
    
    test('professor principal can complete full ocr wizard flow', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        // Step 1: Access wizard form
        $response = $this->actingAs($user)
            ->get("/teacher/bulletin-structure-ocr/create/{$classe->id}");

        $response->assertStatus(200)
            ->assertViewIs('teacher.bulletin-structure-ocr.create');

        // Step 2: Upload file and extract OCR
        $file = UploadedFile::fake()->image('bulletin.jpg', 800, 600);

        $response = $this->actingAs($user)->post(
            "/teacher/bulletin/ocr/upload",
            ['file' => $file, 'classe_id' => $classe->id]
        );

        $response->assertStatus(200)
            ->assertJsonStructure(['text', 'confidence', 'method']);

        // Step 3: Save field coordinates from canvas drawing
        $coordinates = [
            ['name' => 'Français', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 30],
            ['name' => 'Mathématiques', 'x' => 150, 'y' => 10, 'width' => 100, 'height' => 30],
        ];

        $response = $this->actingAs($user)->post(
            '/teacher/bulletin/ocr/save-structure',
            ['classe_id' => $classe->id, 'field_coordinates' => $coordinates]
        );

        $response->assertStatus(200)
            ->assertJson(['zone_count' => 2]);

        // Step 4: Verify and complete the structure
        $response = $this->actingAs($user)
            ->get("/teacher/bulletin-structure-ocr/verify/{$classe->id}");

        $response->assertStatus(200);

        // Step 5: Save final structure to database
        $response = $this->actingAs($user)->post(
            "/teacher/bulletin-structure-ocr/save/{$classe->id}",
            [
                'name' => 'Structure 2025-2026',
                'description' => 'Bulletin structure extracted from scanned image',
                'subjects' => ['Français', 'Mathématiques'],
                'coefficients' => ['Français' => 2, 'Mathématiques' => 3],
                'grading_scale' => ['min' => 0, 'max' => 20],
                'calculation_rules' => [
                    'mean_formula' => '(sum * coefficients) / total_coefficients',
                    'rounding_method' => 'round_half_up',
                ],
                'appreciation_rules' => [
                    'excellent' => [18, 20],
                    'very_good' => [16, 17],
                    'good' => [14, 15],
                ],
            ]
        );

        $response->assertStatus(302);

        // Verify database contains the structure
        $structure = BulletinStructure::where('classe_id', $classe->id)
            ->where('name', 'Structure 2025-2026')
            ->first();

        expect($structure)->not->toBeNull();
        expect($structure->structure_json['subjects'])->toHaveCount(2);
        expect($structure->structure_json['field_coordinates'])->toHaveCount(2);
        expect($structure->created_by)->toBe($user->id);
    });

    test('ocr wizard preserves data through session', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        // Upload file (set session data for verification)
        $response = $this->actingAs($user)->post(
            '/teacher/bulletin/ocr/upload',
            [
                'file' => UploadedFile::fake()->image('bulletin.jpg'),
                'classe_id' => $classe->id,
            ]
        );

        $response->assertStatus(200);

        // Save field coordinates
        $response = $this->actingAs($user)->post(
            '/teacher/bulletin/ocr/save-structure',
            [
                'classe_id' => $classe->id,
                'field_coordinates' => [
                    ['name' => 'Test', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 30],
                ],
            ]
        );

        $response->assertStatus(200);

        // Verify view can access session data
        $response = $this->actingAs($user)
            ->get("/teacher/bulletin-structure-ocr/verify/{$classe->id}");

        $response->assertStatus(200)
            ->assertViewHas('field_coordinates');
    });

});

describe('OCR Error Recovery', function () {
    
    test('wizard handles missing file gracefully', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $response = $this->actingAs($user)->post(
            '/teacher/bulletin/ocr/upload',
            ['classe_id' => $classe->id] // No file
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    });

    test('wizard handles invalid file size', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        // Create file larger than 50MB limit
        $response = $this->actingAs($user)->post(
            '/teacher/bulletin/ocr/upload',
            [
                'file' => UploadedFile::fake()->create('large.pdf', 60000), // 60MB
                'classe_id' => $classe->id,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    });

    test('wizard handles invalid file type', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $response = $this->actingAs($user)->post(
            '/teacher/bulletin/ocr/upload',
            [
                'file' => UploadedFile::fake()->create('test.docx'),
                'classe_id' => $classe->id,
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('file');
    });

    test('wizard handles invalid coordinates gracefully', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $response = $this->actingAs($user)->post(
            '/teacher/bulletin/ocr/save-structure',
            [
                'classe_id' => $classe->id,
                'field_coordinates' => [
                    ['name' => 'Test', 'x' => 'abc', 'y' => 10, 'width' => 100, 'height' => 30],
                ],
            ]
        );

        $response->assertStatus(422)
            ->assertJsonValidationErrors('field_coordinates.0.x');
    });

});

describe('Concurrent OCR Requests', function () {
    
    test('multiple users can upload ocr files simultaneously', function () {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $classe1 = Classe::factory()->create();
        $classe2 = Classe::factory()->create();

        $teacher1 = Teacher::factory()->create([
            'user_id' => $user1->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user1->id,
            'head_class_id' => $classe1->id,
        ]);

        $teacher2 = Teacher::factory()->create([
            'user_id' => $user2->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user2->id,
            'head_class_id' => $classe2->id,
        ]);

        $response1 = $this->actingAs($user1)->post(
            '/teacher/bulletin/ocr/upload',
            [
                'file' => UploadedFile::fake()->image('bulletin1.jpg'),
                'classe_id' => $classe1->id,
            ]
        );

        $response2 = $this->actingAs($user2)->post(
            '/teacher/bulletin/ocr/upload',
            [
                'file' => UploadedFile::fake()->image('bulletin2.jpg'),
                'classe_id' => $classe2->id,
            ]
        );

        $response1->assertStatus(200);
        $response2->assertStatus(200);
    });

});
