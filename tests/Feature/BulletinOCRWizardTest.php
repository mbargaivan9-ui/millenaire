<?php

use App\Models\User;
use App\Models\Classe;
use App\Models\Teacher;
use App\Models\BulletinStructure;
use Illuminate\Support\Facades\Session;

describe('BulletinStructureOCRController saveStructure()', function () {
    
    test('professor principal can save bulletin structure with all fields', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        // Set session data (as would be done in processUpload)
        session([
            'ocr_extraction_text' => 'Français coeff 2, Mathématiques coeff 3',
            'ocr_confidence' => 85,
            'ocr_method' => 'tesseract',
            'field_coordinates' => [
                ['name' => 'Français', 'x' => 10, 'y' => 10, 'width' => 100, 'height' => 20],
                ['name' => 'Mathématiques', 'x' => 10, 'y' => 40, 'width' => 100, 'height' => 20],
            ],
        ]);

        $response = $this->actingAs($user)->post("/teacher/bulletin-structure-ocr/save/{$classe->id}", [
            'name' => 'Structure Bulletin 2025',
            'description' => 'Structure extracted from bulletin scan',
            'subjects' => ['Français', 'Mathématiques'],
            'coefficients' => ['Français' => 2, 'Mathématiques' => 3],
            'grading_scale' => ['min' => 0, 'max' => 20],
            'calculation_rules' => ['mean_formula' => '(sum * coefficients) / total_coefficients'],
        ]);

        $response->assertStatus(302)
            ->assertRedirect();

        // Verify structure was saved to database
        $structure = BulletinStructure::where('classe_id', $classe->id)
            ->where('name', 'Structure Bulletin 2025')
            ->first();

        expect($structure)->not->toBeNull();
        expect($structure->structure_json['subjects'])->toHaveCount(2);
        expect($structure->structure_json['field_coordinates'])->toHaveCount(2);
        expect($structure->ocr_confidence)->toBe(85);
        expect($structure->is_verified)->toBeFalse(); // Not verified by default
    });

    test('non-professor-principal cannot save structure', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => false,
        ]);

        $response = $this->actingAs($user)->post("/teacher/bulletin-structure-ocr/save/{$classe->id}", [
            'name' => 'Test Structure',
            'subjects' => ['Français'],
        ]);

        $response->assertStatus(403);
    });

    test('insufficient validation data is rejected', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $response = $this->actingAs($user)->post("/teacher/bulletin-structure-ocr/save/{$classe->id}", [
            'name' => 'Test', // Missing required fields
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors();
    });

    test('structure name must be unique per class', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        // Create first structure
        BulletinStructure::create([
            'classe_id' => $classe->id,
            'name' => 'Duplicate Name',
            'structure_json' => ['subjects' => []],
            'calculation_rules' => [],
            'ocr_confidence' => 80,
            'created_by' => $user->id,
        ]);

        session([
            'ocr_extraction_text' => 'Test',
            'ocr_confidence' => 80,
            'field_coordinates' => [],
        ]);

        // Try to create same name
        $response = $this->actingAs($user)->post("/teacher/bulletin-structure-ocr/save/{$classe->id}", [
            'name' => 'Duplicate Name',
            'description' => 'Test',
            'subjects' => ['Français'],
            'coefficients' => ['Français' => 2],
            'grading_scale' => ['min' => 0, 'max' => 20],
            'calculation_rules' => ['mean_formula' => 'sum'],
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors();
    });

    test('field coordinates are persisted to database', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        $fieldCoordinates = [
            ['name' => 'Subject1', 'x' => 10, 'y' => 20, 'width' => 100, 'height' => 30],
            ['name' => 'Subject2', 'x' => 150, 'y' => 20, 'width' => 100, 'height' => 30],
        ];

        session([
            'ocr_extraction_text' => 'Text',
            'ocr_confidence' => 85,
            'field_coordinates' => $fieldCoordinates,
        ]);

        $response = $this->actingAs($user)->post("/teacher/bulletin-structure-ocr/save/{$classe->id}", [
            'name' => 'Structure Test',
            'description' => 'Test',
            'subjects' => ['Subject1', 'Subject2'],
            'coefficients' => ['Subject1' => 1, 'Subject2' => 2],
            'grading_scale' => ['min' => 0, 'max' => 20],
            'calculation_rules' => ['mean_formula' => 'sum'],
        ]);

        $response->assertStatus(302);

        $structure = BulletinStructure::where('classe_id', $classe->id)
            ->where('name', 'Structure Test')
            ->first();

        expect($structure->structure_json['field_coordinates'])->toBe($fieldCoordinates);
    });

    test('session data is cleaned after successful save', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        session([
            'ocr_extraction_text' => 'Text',
            'ocr_confidence' => 85,
            'field_coordinates' => [],
        ]);

        $response = $this->actingAs($user)->post("/teacher/bulletin-structure-ocr/save/{$classe->id}", [
            'name' => 'Test',
            'description' => 'Test',
            'subjects' => ['Français'],
            'coefficients' => ['Français' => 2],
            'grading_scale' => ['min' => 0, 'max' => 20],
            'calculation_rules' => ['mean_formula' => 'sum'],
        ]);

        // After redirect, check that session was cleaned
        $response->assertStatus(302);
        expect(session('ocr_extraction_text'))->toBeNull();
        expect(session('ocr_confidence'))->toBeNull();
        expect(session('field_coordinates'))->toBeNull();
    });

    test('calculation rules are validated before save', function () {
        $user = User::factory()->create();
        $classe = Classe::factory()->create();
        $teacher = Teacher::factory()->create([
            'user_id' => $user->id,
            'is_prof_principal' => true,
            'head_teacher_id' => $user->id,
            'head_class_id' => $classe->id,
        ]);

        session([
            'ocr_extraction_text' => 'Text',
            'ocr_confidence' => 85,
            'field_coordinates' => [],
        ]);

        $response = $this->actingAs($user)->post("/teacher/bulletin-structure-ocr/save/{$classe->id}", [
            'name' => 'Test',
            'subjects' => ['Français'],
            'coefficients' => ['Français' => 2],
            'grading_scale' => ['min' => 0, 'max' => 20],
            'calculation_rules' => [], // Invalid: empty
        ]);

        $response->assertStatus(302)
            ->assertSessionHasErrors();
    });

});
