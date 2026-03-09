<?php

/**
 * Admin\StudentController — Gestion détaillée des Élèves
 *
 * Phase 2 — Administration
 *
 * @package App\Http\Controllers\Admin
 */

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        $query = Student::with('user', 'classe', 'guardians.user')
            ->withCount('absences')
            ->join('users', 'users.id', '=', 'students.user_id')
            ->select('students.*');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(fn($q) => $q
                ->where('users.name', 'like', "%$s%")
                ->orWhere('users.email', 'like', "%$s%")
                ->orWhere('students.matricule', 'like', "%$s%")
            );
        }
        if ($request->filled('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        if ($request->filled('section')) {
            $query->whereHas('classe', fn($q) => $q->where('section', $request->section));
        }

        $students = $query->orderBy('users.name')->paginate(25)->withQueryString();
        $classes  = Classe::orderBy('name')->get();

        return view('admin.students.index', compact('students', 'classes'));
    }

    public function create()
    {
        $classes = Classe::orderBy('name')->get();
        return view('admin.students.create', compact('classes'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'   => 'required|string|max:100',
            'last_name'    => 'required|string|max:100',
            'email'        => 'required|email|unique:users',
            'class_id'     => 'required|exists:classes,id',
            'matricule'    => 'nullable|string|unique:students,matricule',
            'date_of_birth'=> 'nullable|date',
            'gender'       => 'nullable|in:M,F',
        ]);

        $user = User::create([
            'name'     => "{$data['first_name']} {$data['last_name']}",
            'email'    => $data['email'],
            'role'     => 'student',
            'password' => Hash::make(Str::random(12)), // Temp password, will be reset
            'preferred_language' => app()->getLocale(),
        ]);

        $student = Student::create([
            'user_id'       => $user->id,
            'class_id'      => $data['class_id'],
            'matricule'     => $data['matricule'] ?? 'MC' . now()->year . str_pad($user->id, 5, '0', STR_PAD_LEFT),
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'gender'        => $data['gender'] ?? null,
        ]);

        activity()->causedBy(auth()->user())->performedOn($student)->log('Élève créé');

        // TODO: Send welcome email with password reset link
        // $user->sendPasswordResetNotification(Password::createToken($user));

        return redirect()->route('admin.students.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Élève créé avec succès.' : 'Student created successfully.');
    }

    public function show(int $id)
    {
        $student = Student::with('user', 'classe', 'guardians.user', 'marks.subject', 'absences.subject', 'payments')
            ->findOrFail($id);
        return view('admin.students.show', compact('student'));
    }

    public function edit(int $id)
    {
        $student = Student::with('user')->findOrFail($id);
        $classes = Classe::orderBy('name')->get();
        return view('admin.students.edit', compact('student', 'classes'));
    }

    public function update(Request $request, int $id)
    {
        $student = Student::with('user')->findOrFail($id);

        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => "required|email|unique:users,email,{$student->user_id}",
            'class_id' => 'required|exists:classes,id',
        ]);

        $student->user->update(['name' => $request->name, 'email' => $request->email]);
        $student->update(['class_id' => $request->class_id]);

        return redirect()->route('admin.students.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Élève modifié.' : 'Student updated.');
    }

    public function export()
    {
        return Excel::download(new \App\Exports\StudentsExport(), 'eleves-' . now()->format('Y-m-d') . '.xlsx');
    }

    /**
     * Import students from CSV/Excel.
     */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,xlsx,xls|max:5120']);
        Excel::import(new \App\Imports\StudentsImport(), $request->file('file'));

        return back()->with('success', app()->getLocale() === 'fr' ? 'Import terminé.' : 'Import complete.');
    }
}
