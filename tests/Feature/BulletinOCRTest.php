test('bulletin ocr parser extracts subjects correctly', function () {
    $parser = app(\App\Services\BulletinStructureParserService::class);
    
    $text = "Matière: Français\nMatière: Mathématiques\nMatière: Anglais";
    $structure = $parser->parseStructure($text);
    
    expect($structure['subjects'])->toHaveCount(3)
        ->andContain('Français', 'Mathématiques', 'Anglais');
});

test('bulletin ocr parser detects coefficients', function () {
    $parser = app(\App\Services\BulletinStructureParserService::class);
    
    $text = "Français coefficient 2\nMathématiques coefficient 3";
    $structure = $parser->parseStructure($text);
    
    expect($structure['coefficients'])->toBeArray()
        ->toHaveKeys(['Français', 'Mathématiques']);
});

test('bulletin ocr parser detects grading scale', function () {
    $parser = app(\App\Services\BulletinStructureParserService::class);
    
    // Test 0-20 scale
    $structure = $parser->parseStructure("Notes de 0 à 20");
    expect($structure['grading_scale']['min'])->toBe(0);
    expect($structure['grading_scale']['max'])->toBe(20);
});

test('ocr service validates file correctly', function () {
    $service = app(\App\Services\BulletinOCRService::class);
    
    // Confidence check
    expect($service->isConfidenceAcceptable(75))->toBeTrue();
    expect($service->isConfidenceAcceptable(50))->toBeFalse();
});
