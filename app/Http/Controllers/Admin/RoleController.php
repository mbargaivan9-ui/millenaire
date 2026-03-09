<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminRole;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');
    }

    /**
     * Display list of administrative roles
     */
    public function index(): View
    {
        $roles = AdminRole::with('users')
            ->orderBy('hierarchy_level')
            ->get();

        $statistics = [
            'total_roles' => $roles->count(),
            'total_admins' => User::whereIn('role', ['admin', 'censeur', 'intendant', 'secretaire', 'surveillant'])->count(),
            'admin_count' => User::where('role', 'admin')->count(),
            'censeur_count' => User::where('role', 'censeur')->count(),
            'intendant_count' => User::where('role', 'intendant')->count(),
            'secretaire_count' => User::where('role', 'secretaire')->count(),
            'surveillant_count' => User::where('role', 'surveillant')->count(),
        ];

        return view('admin.roles.index', [
            'roles' => $roles,
            'statistics' => $statistics,
        ]);
    }

    /**
     * Show role creation form
     */
    public function create(): View
    {
        $roleTypes = [
            'admin' => 'Administrator (Full Access)',
            'censeur' => 'Censeur (Supervisor)',
            'intendant' => 'Intendant (Financial Manager)',
            'secretaire' => 'Secrétaire (Secretary)',
            'surveillant' => 'Surveillant Général (General Supervisor)',
        ];

        $permissions = [
            'manage_users' => 'Manage Users',
            'manage_classes' => 'Manage Classes',
            'manage_assignments' => 'Manage Teacher Assignments',
            'manage_finances' => 'Manage Finances',
            'manage_roles' => 'Manage Admin Roles',
            'view_reports' => 'View Reports',
            'manage_announcements' => 'Manage Announcements',
            'audit_trail' => 'View Audit Trail',
            'system_settings' => 'Manage System Settings',
            'approve_bulletins' => 'Approve Report Cards',
        ];

        return view('admin.roles.create', [
            'roleTypes' => $roleTypes,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Store new role
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'role_type' => 'required|in:admin,censeur,intendant,secretaire,surveillant',
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $hierarchyLevels = [
            'admin' => 0,
            'censeur' => 1,
            'intendant' => 2,
            'secretaire' => 3,
            'surveillant' => 4,
        ];

        AdminRole::create([
            'role_type' => $validated['role_type'],
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
            'permissions' => json_encode($validated['permissions']),
            'hierarchy_level' => $hierarchyLevels[$validated['role_type']] ?? 5,
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully!');
    }

    /**
     * Show role edit form
     */
    public function edit(AdminRole $role): View
    {
        $roleTypes = [
            'admin' => 'Administrator (Full Access)',
            'censeur' => 'Censeur (Supervisor)',
            'intendant' => 'Intendant (Financial Manager)',
            'secretaire' => 'Secrétaire (Secretary)',
            'surveillant' => 'Surveillant Général (General Supervisor)',
        ];

        $permissions = [
            'manage_users' => 'Manage Users',
            'manage_classes' => 'Manage Classes',
            'manage_assignments' => 'Manage Teacher Assignments',
            'manage_finances' => 'Manage Finances',
            'manage_roles' => 'Manage Admin Roles',
            'view_reports' => 'View Reports',
            'manage_announcements' => 'Manage Announcements',
            'audit_trail' => 'View Audit Trail',
            'system_settings' => 'Manage System Settings',
            'approve_bulletins' => 'Approve Report Cards',
        ];

        $rolePermissions = json_decode($role->permissions, true) ?? [];

        return view('admin.roles.edit', [
            'role' => $role,
            'roleTypes' => $roleTypes,
            'permissions' => $permissions,
            'rolePermissions' => $rolePermissions,
        ]);
    }

    /**
     * Update role
     */
    public function update(Request $request, AdminRole $role): RedirectResponse
    {
        $validated = $request->validate([
            'display_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'permissions' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $role->update([
            'display_name' => $validated['display_name'],
            'description' => $validated['description'],
            'permissions' => json_encode($validated['permissions']),
            'is_active' => $validated['is_active'] ?? true,
        ]);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully!');
    }

    /**
     * Delete role
     */
    public function destroy(AdminRole $role): RedirectResponse
    {
        if ($role->users()->exists()) {
            return back()->withErrors('Cannot delete role with assigned users.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully!');
    }

    /**
     * Assign role to user
     */
    public function assignToUser(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role_id' => 'required|exists:admin_roles,id',
        ]);

        $user = User::find($validated['user_id']);
        $role = AdminRole::find($validated['role_id']);

        // Update user's role
        $user->update(['role' => $role->role_type]);

        // Attach role
        $user->adminRoles()->sync($validated['role_id']);

        return back()->with('success', "Role assigned to {$user->name} successfully!");
    }

    /**
     * Remove role from user
     */
    public function removeFromUser(Request $request, User $user, AdminRole $role): RedirectResponse
    {
        $user->adminRoles()->detach($role->id);

        return back()->with('success', 'Role removed from user successfully!');
    }
}
