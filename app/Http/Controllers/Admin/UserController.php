<?php

/**
 * Admin\UserController — Gestion des Utilisateurs
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Classe;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query()->orderByDesc('created_at');

        if ($request->filled('role'))   $query->where('role', $request->role);
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q->where('name', 'like', "%$s%")->orWhere('email', 'like', "%$s%"));
        }

        $users = $query->paginate(25)->withQueryString();
        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $classes = Classe::orderBy('name')->get();
        return view('admin.users.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users',
            'role'       => 'required|in:admin,teacher,parent,student',
            'password'   => 'required|min:8|confirmed',
            'class_id'   => 'nullable|exists:classes,id',
            'matricule'  => 'nullable|string|unique:students,matricule',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => $data['role'],
            'password' => Hash::make($data['password']),
            'preferred_language' => app()->getLocale(),
        ]);

        // Create associated model
        if ($data['role'] === 'student' && $data['class_id']) {
            Student::create([
                'user_id'    => $user->id,
                'class_id'   => $data['class_id'],
                'matricule'  => $data['matricule'] ?? 'MC' . now()->year . str_pad($user->id, 4, '0', STR_PAD_LEFT),
            ]);
        } elseif ($data['role'] === 'teacher') {
            Teacher::create([
                'user_id'   => $user->id,
                'is_active' => true,
            ]);
        }

        activity()->causedBy(auth()->user())->performedOn($user)->log("Utilisateur {$data['role']} créé");

        return redirect()->route('admin.users.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Utilisateur créé.' : 'User created.');
    }
}
