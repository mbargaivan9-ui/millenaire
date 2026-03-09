<?php
declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CarouselImageResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'url' => $this->url ?? null,
            'title' => $this->title ?? null,
            'caption' => $this->caption ?? null,
            'alt' => $this->alt ?? null,
        ];
    }
}
