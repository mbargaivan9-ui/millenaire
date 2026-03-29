<?php

/*
 * Test Route for Step 4 Modal
 * Visit: /test-modal
 */

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/test-modal', function () {
    return view('test-modal');
});

Route::post('/api/test-student', function (Request $request) {
    // Simulate student creation
    return response()->json([
        'success' => true,
        'message' => 'Student added successfully',
        'student' => [
            'id' => rand(1, 1000),
            'matricule' => $request->input('matricule'),
            'nom' => $request->input('nom'),
            'date_naissance' => $request->input('date_naissance'),
            'lieu_naissance' => $request->input('lieu_naissance'),
            'sexe' => $request->input('sexe'),
        ]
    ]);
});
