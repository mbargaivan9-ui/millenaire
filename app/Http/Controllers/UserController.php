<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();
        
        // Search
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
        }
        
        // Filter by role
        if ($request->role) {
            $query->where('role', $request->role);
        }
        
        // Filter by status
        if ($request->status) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $users = $query->paginate(15);
        $roles = ['admin', 'teacher', 'parent', 'student'];
        
        return view('admin.users.index', [
            'users' => $users,
            'roles' => $roles
        ]);
    }
    
    public function create()
    {
        $roles = ['admin', 'teacher', 'parent', 'student'];
        
        return view('admin.users.form', [
            'roles' => $roles
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'role' => 'required|in:admin,teacher,parent,student',
            'gender' => 'required|in:M,F',
            'phoneNumber' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'password' => 'required|min:8|confirmed',
            'is_active' => 'boolean'
        ]);
        
        $user = User::create([
            ...$validated,
            'password' => bcrypt($validated['password']),
            'is_active' => $request->boolean('is_active', true)
        ]);
        
        return redirect()->route('admin.users.index')
                        ->with('success', 'Utilisateur créé avec succès');
    }
    
    public function edit(User $user)
    {
        $roles = ['admin', 'teacher', 'parent', 'student'];
        
        return view('admin.users.form', [
            'user' => $user,
            'roles' => $roles
        ]);
    }
    
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,teacher,parent,student',
            'gender' => 'required|in:M,F',
            'phoneNumber' => 'nullable|string',
            'date_of_birth' => 'nullable|date',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        $user->update($validated + [
            'is_active' => $request->boolean('is_active', true)
        ]);
        
        return redirect()->route('admin.users.index')
                        ->with('success', 'Utilisateur mis à jour');
    }
    
    public function destroy(User $user)
    {
        $user->delete();
        
        return redirect()->route('admin.users.index')
                        ->with('success', 'Utilisateur supprimé');
    }
}
