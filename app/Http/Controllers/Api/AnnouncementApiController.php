<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use App\Contracts\AnnouncementServiceInterface;
use App\Http\Resources\AnnouncementResource;

class AnnouncementApiController extends Controller
{
    public function __construct(private AnnouncementServiceInterface $service)
    {
    }

    /**
     * Récupérer les annonces publiées les plus récentes
     * GET /api/announcements/latest?limit=6&featured=1
     */
    public function latest(Request $request)
    {
        $limit = (int) $request->query('limit', 6);
        $featured = $request->boolean('featured', false);

        $query = Announcement::published();

        if ($featured) {
            $query->featured();
        }

        $announcements = $query->orderByDesc('published_at')
                               ->with('author')
                               ->limit($limit)
                               ->get();

        return AnnouncementResource::collection($announcements);
    }

    /**
     * Récupérer toutes les annonces publiées avec pagination
     * GET /api/announcements?page=1&per_page=12&category=event&sort=-published_at
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 12);
        $category = $request->query('category');
        $search = $request->query('search');
        $sort = $request->query('sort', '-published_at');

        $query = Announcement::published()->with('author');

        // Filtre par catégorie
        if ($category) {
            $query->where('category', $category);
        }

        // Recherche
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%' . $search . '%')
                  ->orWhere('content', 'like', '%' . $search . '%');
            });
        }

        // Tri
        if ($sort === '-published_at') {
            $query->orderByDesc('published_at');
        } elseif ($sort === 'published_at') {
            $query->orderBy('published_at');
        } elseif ($sort === '-featured') {
            $query->orderByDesc('is_featured');
        }

        $announcements = $query->paginate($perPage);

        return AnnouncementResource::collection($announcements);
    }

    /**
     * Récupérer une annonce par ID ou slug
     * GET /api/announcements/{id}
     * GET /api/announcements/slug/{slug}
     */
    public function show($id)
    {
        $announcement = Announcement::published()
                                   ->with('author')
                                   ->where('id', $id)
                                   ->orWhere('slug', $id)
                                   ->firstOrFail();

        // Incrémenter les vues
        $announcement->increment('view_count');

        return new AnnouncementResource($announcement);
    }

    /**
     * Récupérer les annonces en vedette
     * GET /api/announcements/featured/list?limit=3
     */
    public function featured(Request $request)
    {
        $limit = (int) $request->query('limit', 3);

        $announcements = Announcement::published()
                                    ->featured()
                                    ->orderByDesc('published_at')
                                    ->with('author')
                                    ->limit($limit)
                                    ->get();

        return AnnouncementResource::collection($announcements);
    }

    /**
     * Récupérer les catégories disponibles avec le nombre d'annonces
     * GET /api/announcements/categories/list
     */
    public function categories()
    {
        $categories = Announcement::published()
                                 ->select('category')
                                 ->selectRaw('COUNT(*) as count')
                                 ->groupBy('category')
                                 ->orderByDesc('count')
                                 ->get();

        return response()->json([
            'success' => true,
            'data' => $categories,
        ]);
    }

    /**
     * Télécharger un fichier d'annonce
     * GET /api/announcements/{id}/download
     */
    public function downloadAttachment(int $id)
    {
        $announcement = Announcement::published()->findOrFail($id);

        if (!$announcement->attached_file) {
            return response()->json([
                'success' => false,
                'message' => 'No file attached to this announcement.',
            ], 404);
        }

        return response()->download(
            storage_path('app/public/' . $announcement->attached_file),
            $announcement->attachment_name
        );
    }

}
