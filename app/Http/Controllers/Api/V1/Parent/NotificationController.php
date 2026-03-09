<?php

namespace App\Http\Controllers\Api\V1\Parent;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Notification;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the authenticated parent
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $notifications = Notification::where('user_id', $request->user()->id)
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $notifications,
                'message' => 'Notifications retrieved successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving notifications: ' . $e->getMessage(),
            ], 500);
        }
    }
}
