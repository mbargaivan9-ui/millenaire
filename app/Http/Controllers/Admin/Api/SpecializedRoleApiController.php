<?php

namespace App\Http\Controllers\Admin\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\UserSpecializedRoleAssignment;
use App\Models\AdminSectionActivityLog;
use Illuminate\Http\Request;

/**
 * SpecializedRoleApiController
 * API endpoints for specialized role operations
 */
class SpecializedRoleApiController extends Controller
{
    /**
     * Get user's accessible sections
     */
    public function getAccessibleSections(Request $request)
    {
        $user = $request->user();
        
        $assignment = UserSpecializedRoleAssignment::where('user_id', $user->id)
            ->where('deactivated_at', null)
            ->with('role.sections')
            ->first();

        if (!$assignment) {
            return response()->json([
                'sections' => [],
                'role' => null,
                'message' => 'No specialized role assigned',
            ]);
        }

        $sections = $assignment->getAccessibleSections()
            ->map(function($section) use ($assignment) {
                return [
                    'id' => $section->id,
                    'code' => $section->code,
                    'name' => $section->name,
                    'description' => $section->description,
                    'icon' => $section->icon,
                    'route' => $section->route,
                    'permissions' => $assignment->role->getSectionPermissions($section->code),
                ];
            });

        return response()->json([
            'sections' => $sections,
            'role' => [
                'id' => $assignment->role->id,
                'name' => $assignment->role->name,
                'code' => $assignment->role->code,
                'icon' => $assignment->role->icon,
                'color' => $assignment->role->color,
                'description' => $assignment->role->description,
            ],
        ]);
    }

    /**
     * Get unread notifications count
     */
    public function getUnreadNotificationsCount(Request $request)
    {
        $count = $request->user()->notifications()
            ->whereNull('read_at')
            ->count();

        return response()->json([
            'count' => $count,
            'timestamp' => now(),
        ]);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationAsRead(Request $request, $notificationId)
    {
        $notification = Notification::where('id', $notificationId)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $notification->markAsRead();

        return response()->json(['success' => true]);
    }

    /**
     * Mark all notifications as read
     */
    public function markAllNotificationsAsRead(Request $request)
    {
        $request->user()->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => now()]);

        return response()->json(['success' => true]);
    }

    /**
     * Get section activity logs
     */
    public function getSectionActivityLogs(Request $request)
    {
        $request->validate([
            'section_id' => 'nullable|exists:admin_role_sections,id',
            'limit' => 'nullable|integer|min:1|max:100',
        ]);

        $query = AdminSectionActivityLog::where('user_id', $request->user()->id)
            ->with('section');

        if ($request->filled('section_id')) {
            $query->where('admin_role_section_id', $request->section_id);
        }

        $logs = $query->latest('logged_at')
            ->limit($request->limit ?? 50)
            ->get()
            ->map(function($log) {
                return [
                    'id' => $log->id,
                    'action' => $log->action,
                    'section' => $log->section->name ?? 'Unknown',
                    'description' => $log->description,
                    'timestamp' => $log->logged_at,
                    'time_ago' => $log->logged_at->diffForHumans(),
                ];
            });

        return response()->json(['logs' => $logs]);
    }

    /**
     * Get dashboard widgets data
     */
    public function getDashboardData(Request $request)
    {
        $user = $request->user();
        
        $assignment = UserSpecializedRoleAssignment::where('user_id', $user->id)
            ->where('deactivated_at', null)
            ->with('role')
            ->first();

        if (!$assignment) {
            return response()->json([
                'role' => null,
                'sections' => [],
                'recent_activities' => [],
                'unread_notifications' => 0,
            ]);
        }

        $activities = AdminSectionActivityLog::where('user_id', $user->id)
            ->with('section')
            ->latest('logged_at')
            ->limit(10)
            ->get()
            ->map(function($log) {
                return [
                    'icon' => $log->section->icon ?? '📋',
                    'section' => $log->section->name,
                    'action' => $log->action,
                    'time_ago' => $log->logged_at->diffForHumans(),
                ];
            });

        return response()->json([
            'role' => [
                'name' => $assignment->role->name,
                'icon' => $assignment->role->icon,
                'color' => $assignment->role->color,
            ],
            'sections' => $assignment->getAccessibleSections()->count(),
            'recent_activities' => $activities,
            'unread_notifications' => $user->notifications()->whereNull('read_at')->count(),
        ]);
    }
}
