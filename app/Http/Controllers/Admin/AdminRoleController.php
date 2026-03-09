<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use App\Services\AdminAuditService;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class AdminRoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display a listing of admin roles
     */
    public function index(): View
    {
        $roles = AdminRole::with('users')->get();
        
        $statistics = [
            'total_roles' => $roles->count(),
            'total_admins' => $roles->where('name', 'admin')->first()?->users->count() ?? 0,
            'censeur_count' => $roles->where('name', 'censeur')->first()?->users->count() ?? 0,
            'intendant_count' => $roles->where('name', 'intendant')->first()?->users->count() ?? 0,
            'secretaire_count' => $roles->where('name', 'secretaire')->first()?->users->count() ?? 0,
            'surveillant_count' => $roles->where('name', 'surveillant')->first()?->users->count() ?? 0,
        ];

        return view('admin.roles.index', [
            'roles' => $roles,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show the form for creating a new admin role
     */
    public function create(): View
    {
        $roleTypes = [
            'admin' => 'Administrator',
            'censeur' => 'Censeur (Academic Supervisor)',
            'intendant' => 'Intendant (Finance Manager)',
            'secretaire' => 'Secrétaire (Administrative Secretary)',
            'surveillant_general' => 'Surveillant Général (General Supervisor)',
        ];

        $permissions = [
            'manage_users' => 'Manage Users',
            'manage_classes' => 'Manage Classes',
            'manage_teachers' => 'Manage Teachers',
            'manage_assignments' => 'Manage Teacher Assignments',
            'manage_finance' => 'Manage Finance',
            'manage_fees' => 'Manage Fees',
            'manage_payments' => 'Manage Payments',
            'manage_reports' => 'Generate Reports',
            'manage_announcements' => 'Manage Announcements',
            'manage_settings' => 'Manage Settings',
            'view_audit_logs' => 'View Audit Logs',
            'manage_admin_roles' => 'Manage Admin Roles',
        ];

        return view('admin.roles.create', [
            'roleTypes' => $roleTypes,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store a newly created admin role
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_type' => 'required|string|in:admin,censeur,intendant,secretaire,surveillant_general',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $role = AdminRole::create([
            'code' => $validated['role_type'],
            'name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'hierarchy_level' => $this->getHierarchyLevel($validated['role_type']),
            'permissions' => json_encode($validated['permissions'] ?? []),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Log audit
        AdminAuditService::log(
            auth()->user(),
            'create_admin_role',
            'AdminRole',
            $role->id,
            $validated,
            []
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Admin role created successfully');
    }

    /**
     * Display the specified admin role
     */
    public function show(AdminRole $role): View
    {
        $role->load('users');

        return view('admin.roles.show', [
            'role' => $role,
        ]);
    }

    /**
     * Show the form for editing the specified admin role
     */
    public function edit(AdminRole $role): View
    {
        $permissions = [
            'manage_users' => 'Manage Users',
            'manage_classes' => 'Manage Classes',
            'manage_teachers' => 'Manage Teachers',
            'manage_assignments' => 'Manage Teacher Assignments',
            'manage_finance' => 'Manage Finance',
            'manage_fees' => 'Manage Fees',
            'manage_payments' => 'Manage Payments',
            'manage_reports' => 'Generate Reports',
            'manage_announcements' => 'Manage Announcements',
            'manage_settings' => 'Manage Settings',
            'view_audit_logs' => 'View Audit Logs',
            'manage_admin_roles' => 'Manage Admin Roles',
        ];

        $rolePermissions = $role->getPermissionsArray();
        
        // Add virtual properties for view
        $role->display_name = $role->name;
        $role->role_type = $role->code;

        return view('admin.roles.edit', [
            'role' => $role,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update the specified admin role
     */
    public function update(Request $request, AdminRole $role): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'permissions' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $oldData = $role->toArray();

        $role->update([
            'name' => $validated['display_name'],
            'description' => $validated['description'] ?? null,
            'permissions' => json_encode($validated['permissions'] ?? []),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        // Log audit
        AdminAuditService::log(
            auth()->user(),
            'update_admin_role',
            'AdminRole',
            $role->id,
            $validated,
            $oldData
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Admin role updated successfully');
    }

    /**
     * Remove the specified admin role
     */
    public function destroy(AdminRole $role): RedirectResponse
    {
        if ($role->users()->count() > 0) {
            return redirect()->route('admin.roles.index')
                ->with('error', 'Cannot delete a role that has assigned users');
        }

        $roleData = $role->toArray();
        $role->delete();

        // Log audit
        AdminAuditService::log(
            auth()->user(),
            'delete_admin_role',
            'AdminRole',
            $role->id,
            [],
            $roleData
        );

        return redirect()->route('admin.roles.index')
            ->with('success', 'Admin role deleted successfully');
    }

    /**
     * Assign user to admin role (AJAX)
     */
    public function assignUser(Request $request, AdminRole $role): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        // Check if already assigned
        if ($role->users()->where('user_id', $validated['user_id'])->exists()) {
            return response()->json([
                'success' => false,
                'message' => 'User is already assigned to this role',
            ], 400);
        }

        $user = User::find($validated['user_id']);
        $role->users()->attach($user->id);

        // Log audit
        AdminAuditService::log(
            auth()->user(),
            'assign_admin_role',
            'AdminRole',
            $role->id,
            ['user_id' => $user->id, 'user_name' => $user->name],
            []
        );

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} assigned to role {$role->label}",
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ],
        ]);
    }

    /**
     * Remove user from admin role (AJAX)
     */
    public function removeUser(Request $request, AdminRole $role): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($validated['user_id']);
        $role->users()->detach($user->id);

        // Log audit
        AdminAuditService::log(
            auth()->user(),
            'remove_admin_role',
            'AdminRole',
            $role->id,
            ['user_id' => $user->id, 'user_name' => $user->name],
            []
        );

        return response()->json([
            'success' => true,
            'message' => "User {$user->name} removed from role {$role->label}",
        ]);
    }

    /**
     * Get available users for role assignment
     */
    public function getAvailableUsers(AdminRole $role): JsonResponse
    {
        $assignedUserIds = $role->users()->pluck('user_id')->toArray();

        $users = User::where('is_active', true)
            ->whereNotIn('id', $assignedUserIds)
            ->select('id', 'name', 'email', 'role')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $users,
            'count' => $users->count(),
        ]);
    }

    /**
     * Get hierarchy level based on role type
     */
    private function getHierarchyLevel(string $roleType): int
    {
        return match($roleType) {
            'admin' => 9,
            'censeur' => 7,
            'intendant' => 6,
            'secretaire' => 4,
            'surveillant_general' => 5,
            default => 0,
        };
    }
}
