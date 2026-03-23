<?php

/**
 * Admin\UserController — Gestion des Utilisateurs avec Rôles Spécialisés
 * 
 * Supports: admin, teacher, parent, student, censeur, intendant, secretaire, surveillant
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Classe;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\AdminSpecializedRole;
use App\Models\AdminRoleSection;
use App\Models\UserSpecializedRoleAssignment;
use App\Notifications\UserCreatedNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()
            ->with(['specializedRoles' => function($q) {
                $q->where('deactivated_at', null);
            }])
            ->orderByDesc('created_at');

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('specialized_role')) {
            $query->whereHas('specializedRoles', function($q) use ($request) {
                $q->where('admin_specialized_role_id', $request->specialized_role)
                  ->where('deactivated_at', null);
            });
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => 
                $q->where('name', 'like', "%$s%")
                  ->orWhere('email', 'like', "%$s%")
                  ->orWhere('first_name', 'like', "%$s%")
                  ->orWhere('last_name', 'like', "%$s%")
            );
        }

        $users = $query->paginate(25)->withQueryString();
        $roles = AdminSpecializedRole::getActive();
        
        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $classes = Classe::orderBy('name')->get();
        $specializedRoles = AdminSpecializedRole::getActive();
        $sections = AdminRoleSection::getActive();
        
        return view('admin.users.create', compact('classes', 'specializedRoles', 'sections'));
    }

    public function store(Request $request)
    {
        $isFr = app()->getLocale() === 'fr';
        
        // Validation
        $rules = [
            'name'                      => 'required|string|max:255',
            'email'                     => 'required|email|unique:users',
            'role'                      => 'required|in:admin,teacher,parent,student,censeur,intendant,secretaire,surveillant',
            'password'                  => 'required|min:8|confirmed',
            'phone'                     => 'nullable|string',
            'preferred_language'        => 'nullable|in:fr,en',
            'class_id'                  => 'nullable|exists:classes,id',
            'matricule'                 => 'nullable|string|unique:students,matricule',
            'specialized_role_id'       => 'nullable|exists:admin_specialized_roles,id',
            'assigned_sections'         => 'nullable|array',
            'assigned_sections.*'       => 'exists:admin_role_sections,id',
            'role_notes'                => 'nullable|string|max:1000',
        ];

        $data = $request->validate($rules);
        $plainPassword = $data['password'];

        // Start transaction
        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name'                   => $data['name'],
                'email'                  => $data['email'],
                'role'                   => $data['role'],
                'password'               => Hash::make($data['password']),
                'phone'                  => $data['phone'] ?? null,
                'preferred_language'     => $data['preferred_language'] ?? app()->getLocale(),
                'must_change_password'   => true,
                'password_changed_at'    => null,
                'is_active'              => true,
            ]);

            // Create associated model based on role
            if (in_array($data['role'], ['student', 'censeur', 'intendant', 'secretaire', 'surveillant'])) {
                if ($data['role'] === 'student' && $data['class_id'] ?? false) {
                    Student::create([
                        'user_id'   => $user->id,
                        'classe_id' => $data['class_id'],
                        'matricule' => $data['matricule'] ?? 'MC' . now()->year . str_pad($user->id, 4, '0', STR_PAD_LEFT),
                    ]);
                } elseif (in_array($data['role'], ['censeur', 'intendant', 'secretaire', 'surveillant'])) {
                    Teacher::create([
                        'user_id'   => $user->id,
                        'is_active' => true,
                    ]);
                }
            } elseif ($data['role'] === 'teacher') {
                Teacher::create([
                    'user_id'   => $user->id,
                    'is_active' => true,
                ]);
            }

            // Assign specialized role if provided
            if ($request->filled('specialized_role_id')) {
                UserSpecializedRoleAssignment::create([
                    'user_id'                     => $user->id,
                    'admin_specialized_role_id'   => $data['specialized_role_id'],
                    'assigned_sections'           => $data['assigned_sections'] ?? null,
                    'notes'                       => $data['role_notes'] ?? null,
                    'assigned_by_id'              => auth()->id(),
                    'assigned_at'                 => now(),
                ]);
            }

            // Send notification
            try {
                $user->notify(new UserCreatedNotification($user, $plainPassword));
                $emailSent = true;
                $emailMessage = $isFr 
                    ? "✓ Utilisateur créé avec succès!\n\nUn email contenant les identifiants de connexion a été envoyé à {$user->email}"
                    : "✓ User created successfully!\n\nLogin credentials have been sent to {$user->email}";
            } catch (\Exception $e) {
                $emailSent = false;
                $emailMessage = $isFr
                    ? "⚠ Utilisateur créé, mais l'email n'a pas pu être envoyé"
                    : "⚠ User created, but email could not be sent";
            }

            // Log activity
            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Utilisateur {$data['role']} créé: {$user->name}");

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', $emailMessage)
                ->with('email_status', $emailSent ? 'success' : 'warning');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', $isFr ? 'Erreur lors de la création: ' . $e->getMessage() : 'Error creating user');
        }
    }

    public function edit(User $user)
    {
        $classes = Classe::orderBy('name')->get();
        $specializedRoles = AdminSpecializedRole::getActive();
        $sections = AdminRoleSection::getActive();
        $userSpecializedRole = $user->specializedRoles()->where('deactivated_at', null)->first();
        
        return view('admin.users.edit', compact('user', 'classes', 'specializedRoles', 'sections', 'userSpecializedRole'));
    }

    public function update(Request $request, User $user)
    {
        $isFr = app()->getLocale() === 'fr';

        $rules = [
            'name'                  => 'required|string|max:255',
            'email'                 => 'required|email|unique:users,email,' . $user->id,
            'phone'                 => 'nullable|string',
            'preferred_language'    => 'nullable|in:fr,en',
            'is_active'             => 'nullable|boolean',
            'specialized_role_id'   => 'nullable|exists:admin_specialized_roles,id',
            'assigned_sections'     => 'nullable|array',
            'assigned_sections.*'   => 'exists:admin_role_sections,id',
            'role_notes'            => 'nullable|string|max:1000',
        ];

        $data = $request->validate($rules);

        DB::beginTransaction();
        try {
            $user->update([
                'name'               => $data['name'],
                'email'              => $data['email'],
                'phone'              => $data['phone'] ?? $user->phone,
                'preferred_language' => $data['preferred_language'] ?? $user->preferred_language,
                'is_active'          => $data['is_active'] ?? $user->is_active,
            ]);

            // Update specialized role assignment
            if ($request->filled('specialized_role_id')) {
                // Deactivate old assignment if exists
                $user->specializedRoles()
                    ->where('deactivated_at', null)
                    ->update(['deactivated_at' => now()]);

                // Create new assignment
                UserSpecializedRoleAssignment::create([
                    'user_id'                   => $user->id,
                    'admin_specialized_role_id' => $data['specialized_role_id'],
                    'assigned_sections'         => $data['assigned_sections'] ?? null,
                    'notes'                     => $data['role_notes'] ?? null,
                    'assigned_by_id'            => auth()->id(),
                    'assigned_at'               => now(),
                ]);
            }

            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Utilisateur {$user->role} modifié: {$user->name}");

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', $isFr ? 'Utilisateur modifié avec succès' : 'User updated successfully');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', $isFr ? 'Erreur lors de la modification: ' . $e->getMessage() : 'Error updating user');
        }
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', app()->getLocale() === 'fr' 
                ? 'Vous ne pouvez pas supprimer votre propre compte'
                : 'You cannot delete your own account');
        }

        DB::beginTransaction();
        try {
            // Deactivate specialized role assignments
            $user->specializedRoles()
                ->where('deactivated_at', null)
                ->update(['deactivated_at' => now()]);

            // Soft delete or deactivate
            $user->update(['is_active' => false]);

            activity()
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log("Utilisateur supprimé: {$user->name}");

            DB::commit();

            return redirect()->route('admin.users.index')
                ->with('success', app()->getLocale() === 'fr' ? 'Utilisateur supprimé' : 'User deleted');
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting user');
        }
    }
}

