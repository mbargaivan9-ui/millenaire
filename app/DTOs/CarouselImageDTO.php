<?php
declare(strict_types=1);

namespace App\DTOs;

final class CarouselImageDTO
{
    public function __construct(
        public ?string $url = null,
        public ?string $title = null,
        public ?string $caption = null,
        public ?string $alt = null
    ) {}
}
