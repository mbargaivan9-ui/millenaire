<?php

/**
 * Admin\TeacherController — Gestion des Enseignants
 *
 * Phase 2 — Administration
 *
 * @package App\Http\Controllers\Admin
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TeacherController extends Controller
{
    public function index(Request $request)
    {
        $query = Teacher::with('user', 'subjects', 'classes', 'headClass')
            ->join('users', 'users.id', '=', 'teachers.user_id')
            ->select('teachers.*');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('users.name', 'like', "%$s%")
                ->orWhere('users.email', 'like', "%$s%")
            );
        }
        if ($request->filled('subject_id')) {
            $query->whereHas('subjects', fn($q) => $q->where('subjects.id', $request->subject_id));
        }

        $teachers = $query->orderBy('users.name')->paginate(20)->withQueryString();
        $subjects = Subject::orderBy('name')->get();

        return view('admin.teachers.index', compact('teachers', 'subjects'));
    }

    public function create()
    {
        $subjects = Subject::orderBy('name')->get();
        $classes  = Classe::orderBy('name')->get();
        return view('admin.teachers.create', compact('subjects', 'classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'email'       => 'required|email|unique:users',
            'subject_ids' => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
            'qualification'=> 'nullable|string|max:255',
            'bio_fr'      => 'nullable|string|max:1000',
            'bio_en'      => 'nullable|string|max:1000',
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'role'     => 'teacher',
            'password' => Hash::make(Str::random(16)),
            'preferred_language' => app()->getLocale(),
        ]);

        $teacher = Teacher::create([
            'user_id'       => $user->id,
            'qualification' => $data['qualification'] ?? null,
            'bio_fr'        => $data['bio_fr'] ?? null,
            'bio_en'        => $data['bio_en'] ?? null,
            'is_active'     => true,
        ]);

        $teacher->subjects()->sync($data['subject_ids']);

        activity()->causedBy(auth()->user())->performedOn($teacher)->log('Enseignant créé');

        return redirect()->route('admin.teachers.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Enseignant créé.' : 'Teacher created.');
    }

    public function show(int $id)
    {
        $teacher = Teacher::with('user', 'subjects', 'classes', 'headClass')->findOrFail($id);
        return view('admin.teachers.show', compact('teacher'));
    }

    public function edit(int $id)
    {
        $teacher  = Teacher::with('user', 'subjects')->findOrFail($id);
        $subjects = Subject::orderBy('name')->get();
        $classes  = Classe::orderBy('name')->get();
        return view('admin.teachers.edit', compact('teacher', 'subjects', 'classes'));
    }

    public function update(Request $request, int $id)
    {
        $teacher = Teacher::with('user')->findOrFail($id);

        $data = $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => "required|email|unique:users,email,{$teacher->user_id}",
            'subject_ids'   => 'required|array|min:1',
            'subject_ids.*' => 'exists:subjects,id',
            'qualification' => 'nullable|string|max:255',
            'bio_fr'        => 'nullable|string|max:1000',
            'bio_en'        => 'nullable|string|max:1000',
            'is_active'     => 'boolean',
            'is_visible_on_site' => 'boolean',
        ]);

        $teacher->user->update(['name' => $data['name'], 'email' => $data['email']]);
        $teacher->update([
            'qualification'     => $data['qualification'] ?? null,
            'bio_fr'            => $data['bio_fr'] ?? null,
            'bio_en'            => $data['bio_en'] ?? null,
            'is_active'         => $request->boolean('is_active'),
            'is_visible_on_site'=> $request->boolean('is_visible_on_site'),
        ]);
        $teacher->subjects()->sync($data['subject_ids']);

        return redirect()->route('admin.teachers.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Enseignant modifié.' : 'Teacher updated.');
    }

    /**
     * Activer / désactiver un enseignant.
     */
    public function toggleActive(int $id)
    {
        $teacher = Teacher::findOrFail($id);
        $teacher->update(['is_active' => !$teacher->is_active]);

        return back()->with('success', app()->getLocale() === 'fr'
            ? ($teacher->is_active ? 'Enseignant activé.' : 'Enseignant désactivé.')
            : ($teacher->is_active ? 'Teacher activated.' : 'Teacher deactivated.')
        );
    }
}
