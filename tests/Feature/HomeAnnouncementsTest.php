<?php

use App\Contracts\AnnouncementServiceInterface;
use App\DTOs\AnnouncementResponseDTO;
use Mockery\MockInterface;

it('shows homepage and announcements from service (happy path)', function () {
    // Create a mock announcement service
    $fakeAnnouncements = [
        new AnnouncementResponseDTO(
            id: 1,
            title: 'Test annonce',
            content: '<p>Contenu test</p>',
            category: null,
            featured_image: null,
            published_date: now()->format('d/m/Y'),
            expires_at: null,
            visibility: 'all',
            is_pinned: false,
            is_active: true,
            author_name: 'Admin',
            slug: 'test-annonce',
        ),
    ];

    // Mock the announcement service
    $this->mock(AnnouncementServiceInterface::class, function (MockInterface $mock) use ($fakeAnnouncements) {
        $mock->shouldReceive('getPublished')
            ->andReturn($fakeAnnouncements);
    });

    $response = $this->get('/');
    $response->assertStatus(200);
    $response->assertSee('Actualit', false); // Use partial match since & may be escaped
    $response->assertSee('Test annonce');
});
