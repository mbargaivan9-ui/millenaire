<?php

use App\Services\CarouselService;
use Illuminate\Filesystem\Filesystem;

it('resolves absolute urls and ignores missing files', function () {
    $fs = new Filesystem();
    $service = new CarouselService($fs);

    $url = 'https://example.com/image.jpg';
    expect($service->resolveUrl($url))->toBe($url);

    // non-existing relative path should return null
    expect($service->resolveUrl('non-existing-file-xyz.png'))->toBeNull();
});

it('parses carousel settings from json string', function () {
    $fs = new Filesystem();
    $service = new CarouselService($fs);

    $settings = ['carousel_images' => json_encode(['https://example.com/a.jpg','b.jpg'])];
    $images = $service->getFromSettings($settings);

    expect($images)->toBeArray();
    expect(count($images))->toBe(2);
    expect($images[0]->url)->toBe('https://example.com/a.jpg');
});
