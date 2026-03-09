<?php
declare(strict_types=1);

namespace Tests\Feature;

use App\DTOs\CarouselImageDTO;
use App\Models\EstablishmentSetting;
use App\Services\CarouselService;
use Illuminate\Filesystem\Filesystem;
use Tests\TestCase;

class CarouselDisplayTest extends TestCase
{
    private CarouselService $carouselService;
    private Filesystem $files;

    protected function setUp(): void
    {
        parent::setUp();
        $this->files = new Filesystem();
        $this->carouselService = new CarouselService($this->files);
    }

    /**
     * Test that home page displays carousel
     */
    public function test_home_page_loads_carousel_successfully(): void
    {
        // Arrange: Create some carousel images in settings
        $settings = EstablishmentSetting::getInstance();
        $settings->update([
            'carousel_images' => json_encode([
                'https://via.placeholder.com/1200x600?text=Carousel+1',
                'https://via.placeholder.com/1200x600?text=Carousel+2',
            ]),
        ]);

        // Act: Visit home page
        $response = $this->get(route('home'));

        // Assert: Should render successfully
        $response->assertStatus(200);
        $response->assertViewIs('public.home');
        $response->assertViewHas('carouselImages');

        // Check that carousel images are passed to the view
        $carouselImages = $response->viewData('carouselImages');
        expect($carouselImages)->toBeArray();
        expect(count($carouselImages))->toBeGreaterThanOrEqual(2);
    }

    /**
     * Test carousel resolves different image formats
     */
    public function test_carousel_service_handles_multiple_url_formats(): void
    {
        $settings = [
            'carousel_images' => json_encode([
                'https://example.com/image1.jpg',
                'https://example.com/image2.png',
                'https://example.com/image3.webp',
            ]),
        ];

        $images = $this->carouselService->getFromSettings($settings);

        expect($images)->toBeArray();
        expect(count($images))->toBe(3);
        
        foreach ($images as $image) {
            expect($image)->toBeInstanceOf(CarouselImageDTO::class);
            expect($image->url)->not->toBeNull();
        }
    }

    /**
     * Test carousel returns empty array for invalid JSON
     */
    public function test_carousel_handles_invalid_json_gracefully(): void
    {
        $settings = [
            'carousel_images' => 'invalid json string {]',
        ];

        $images = $this->carouselService->getFromSettings($settings);

        expect($images)->toBeArray();
        expect(count($images))->toBe(0);
    }

    /**
     * Test carousel works with empty settings
     */
    public function test_carousel_works_with_empty_settings(): void
    {
        $settings = [];

        $images = $this->carouselService->getFromSettings($settings);

        expect($images)->toBeArray();
        expect(count($images))->toBe(0);
    }
}
