<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Contracts\AnnouncementServiceInterface;
use App\Http\Resources\AnnouncementResource;

class AnnouncementApiController extends Controller
{
    public function __construct(private AnnouncementServiceInterface $service)
    {
    }

    public function latest(Request $request)
    {
        $limit = (int) $request->query('limit', 6);
        $announcements = $this->service->getPublished($limit);

        return AnnouncementResource::collection(collect($announcements));
    }
}
