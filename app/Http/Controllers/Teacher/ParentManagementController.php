<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\Classe;
use App\Models\ParentAccessToken;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * ParentManagementController
 *
 * PHASE 6 - Section 6.1 — Gestion Parents
 * Permissions: Prof Principal only
 * 
 * Features:
 * - Créer des comptes parents
 * - Lier parents aux élèves
 * - Générer des tokens d'accès uniques
 * - Révoquer l'accès des parents
 * - Tracker l'utilisation des tokens
 */
class ParentManagementController extends Controller
{
    /**
     * List all parent accounts for the prof principal's class.
     *
     * @route GET /teacher/parent-management/{class}
     */
    public function index(Classe $class)
    {
        $teacher = Auth::user()->teacher;
        
        // Only prof principal can access
        abort_unless($teacher && $teacher->is_prof_principal, 403, 
            'Accès non autorisé. Seul le professeur principal peut accéder à cette section.'
        );
        
        // Verify the class is the prof principal's class
        abort_unless($class->id === $teacher->head_class_id, 403, 
            'Accès non autorisé à cette classe.'
        );

        // Get all parents linked to students in this class
        $parents = User::where('role', 'parent')
            ->whereHas('students', function($q) use ($class) {
                $q->where('classe_id', $class->id);
            })
            ->with(['students' => function($q) use ($class) {
                $q->where('classe_id', $class->id);
            }, 'accessTokens'])
            ->paginate(12);

        return view('teacher.parent-management.index', [
            'class' => $class,
            'parents' => $parents,
            'teacher' => $teacher,
        ]);
    }

    /**
     * Show form to generate a new parent access token.
     *
     * @route GET /teacher/parent-management/generate-token
     */
    public function generateTokenForm()
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);

        $classe = Classe::findOrFail($teacher->head_class_id);
        $students = $classe->students()->with('user')->orderBy('last_name')->get();

        return view('teacher.parent-management.generate-token', [
            'classe' => $classe,
            'students' => $students,
        ]);
    }

    /**
     * Generate a new parent access token.
     *
     * @route POST /teacher/parent-management/generate-token
     */
    public function generateToken(Request $request)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'email' => 'nullable|email',
            'phone' => 'nullable|string|max:20',
            'relationship' => 'required|in:parent,guardian,tutor,other',
            'expires_in_days' => 'nullable|integer|between:1,365',
        ]);

        // Verify student belongs to prof principal's class
        $student = Student::findOrFail($validated['student_id']);
        abort_unless($student->classe_id === $teacher->head_class_id, 403,
            'Cet étudiant n\'appartient pas à votre classe.'
        );

        // Generate token
        $token = ParentAccessToken::generateToken();
        
        $data = [
            'teacher_id' => $teacher->id,
            'student_id' => $student->id,
            'token' => $token,
            'email' => $validated['email'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'relationship' => $validated['relationship'],
        ];

        // Set expiration if specified
        if ($validated['expires_in_days'] ?? null) {
            $data['expires_at'] = now()->addDays($validated['expires_in_days']);
        }

        $tokenRecord = ParentAccessToken::create($data);

        // Log the action
        activity()
            ->causedBy(auth()->user())
            ->performedOn($tokenRecord)
            ->withProperties([
                'student' => $student->user->name,
                'relationship' => $validated['relationship'],
            ])
            ->log('Token d\'accès parent généré');

        return back()->with('success', 'Token généré avec succès. Partecgez-le avec le parent.');
    }

    /**
     * Show token details and revoke option.
     *
     * @route GET /teacher/parent-management/token/{token}/show
     */
    public function showToken($token)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);

        $tokenRecord = ParentAccessToken::where('token', $token)
            ->where('teacher_id', $teacher->id)
            ->with(['student.user', 'user'])
            ->firstOrFail();

        return view('teacher.parent-management.show-token', [
            'tokenRecord' => $tokenRecord,
        ]);
    }

    /**
     * Revoke a parent access token.
     *
     * @route POST /teacher/parent-management/token/{token}/revoke
     */
    public function revokeToken(Request $request, $token)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);

        $tokenRecord = ParentAccessToken::where('token', $token)
            ->where('teacher_id', $teacher->id)
            ->firstOrFail();

        $tokenRecord->revoke();

        // Log the action
        activity()
            ->causedBy(auth()->user())
            ->performedOn($tokenRecord)
            ->log('Token d\'accès parent révoqué');

        return back()->with('success', 'Accès du parent a été révoqué.');
    }

    /**
     * Create a parent account and link to student (after token validation).
     *
     * @route POST /teacher/parent-management/create-parent
     */
    public function createParentAccount(Request $request)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8|confirmed',
            'relationship' => 'required|in:parent,guardian,tutor,other',
        ]);

        // Verify student belongs to prof principal's class
        $student = Student::findOrFail($validated['student_id']);
        abort_unless($student->classe_id === $teacher->head_class_id, 403);

        // Create parent user account
        $parentUser = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'password' => Hash::make($validated['password']),
            'role' => 'parent',
            'is_active' => true,
        ]);

        // Create guardian relationship
        $student->guardians()->attach($parentUser->id, [
            'relationship' => $validated['relationship'],
        ]);

        // Log the action
        activity()
            ->causedBy(auth()->user())
            ->performedOn($parentUser)
            ->withProperties([
                'student' => $student->user->name,
                'relationship' => $validated['relationship'],
            ])
            ->log('Compte parent créé');

        return back()->with('success', "Compte parent créé pour {$validated['name']}. L'accès aux bulletins de {$student->user->name} a été accordé.");
    }

    /**
     * Remove parent access to a student.
     *
     * @route POST /teacher/parent-management/remove-parent/{student}/{parent}
     */
    public function removeParentAccess(Request $request, Student $student, User $parent)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);
        abort_unless($student->classe_id === $teacher->head_class_id, 403);
        abort_unless($parent->role === 'parent', 403);

        // Detach parent from student
        $student->guardians()->detach($parent->id);

        // Log the action
        activity()
            ->causedBy(auth()->user())
            ->performedOn($student)
            ->withProperties(['parent' => $parent->name])
            ->log('Accès parent supprimé');

        return back()->with('success', "L'accès de {$parent->name} a été supprimé.");
    }

    /**
     * Bulk generate tokens for a class.
     *
     * @route POST /teacher/parent-management/bulk-generate
     */
    public function bulkGenerateTokens(Request $request)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);

        $validated = $request->validate([
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'relationship' => 'required|in:parent,guardian,tutor,other',
            'expires_in_days' => 'nullable|integer|between:1,365',
        ]);

        $classe = Classe::findOrFail($teacher->head_class_id);
        $created = 0;

        foreach ($validated['student_ids'] as $studentId) {
            $student = Student::findOrFail($studentId);
            
            // Only create for students in the prof principal's class
            if ($student->classe_id !== $teacher->head_class_id) {
                continue;
            }

            $token = ParentAccessToken::generateToken();
            
            $data = [
                'teacher_id' => $teacher->id,
                'student_id' => $student->id,
                'token' => $token,
                'relationship' => $validated['relationship'],
            ];

            if ($validated['expires_in_days'] ?? null) {
                $data['expires_at'] = now()->addDays($validated['expires_in_days']);
            }

            ParentAccessToken::create($data);
            $created++;
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($classe)
            ->log('Tokens d\'accès parents générés en masse');

        return back()->with('success', "$created token(s) générés avec succès.");
    }

    /**
     * Show form to create a new parent account.
     *
     * @route GET /teacher/parent-management/{class}/create
     */
    public function create(Classe $class)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal && $class->id === $teacher->head_class_id, 403);

        $students = $class->students()->with('user')->orderBy('last_name')->get();

        return view('teacher.parent-management.create', [
            'class' => $class,
            'students' => $students,
        ]);
    }

    /**
     * Store a newly created parent account.
     *
     * @route POST /teacher/parent-management/{class}
     */
    public function store(Request $request, Classe $class)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal && $class->id === $teacher->head_class_id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'nullable|string|max:20|unique:users,phone',
            'relationship' => 'required|in:father,mother,guardian,relatives',
            'password' => 'required|string|min:8|confirmed',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'exists:students,id',
            'is_active' => 'nullable',
            'generate_token' => 'nullable',
        ]);

        // Create parent user
        $parent = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'role' => 'parent',
            'is_active' => isset($validated['is_active']),
        ]);

        // Link to students
        foreach ($validated['student_ids'] as $studentId) {
            $student = Student::findOrFail($studentId);
            if ($student->classe_id === $class->id) {
                $student->parents()->attach($parent->id, ['relationship' => $validated['relationship']]);
            }
        }

        // Generate token if requested
        if (isset($validated['generate_token'])) {
            $token = ParentAccessToken::generateToken();
            ParentAccessToken::create([
                'user_id' => $parent->id,
                'token' => $token,
                'expires_at' => now()->addDays(30),
            ]);
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($parent)
            ->withProperties(['class' => $class->name])
            ->log('Compte parent créé');

        return redirect()->route('teacher.parent-management.index', $class)
            ->with('success', "Parent '{$parent->name}' créé avec succès.");
    }

    /**
     * Show form to edit a parent account.
     *
     * @route GET /teacher/parent-management/{parent}/edit
     */
    public function edit(User $parent)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);
        abort_unless($parent->role === 'parent', 403);

        $class = $teacher->headClass;
        $students = $class->students()->with('user')->orderBy('last_name')->get();
        $parentStudent = $parent->students()->where('classe_id', $class->id)->first();

        return view('teacher.parent-management.edit', [
            'parent' => $parent,
            'class' => $class,
            'students' => $students,
            'parentStudent' => $parentStudent,
        ]);
    }

    /**
     * Update a parent account.
     *
     * @route PUT /teacher/parent-management/{parent}
     */
    public function update(Request $request, User $parent)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);
        abort_unless($parent->role === 'parent', 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $parent->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,' . $parent->id,
            'relationship' => 'required|in:father,mother,guardian,relatives',
            'student_ids' => 'nullable|array',
            'student_ids.*' => 'exists:students,id',
            'is_active' => 'nullable',
            'change_password' => 'nullable',
            'password' => 'nullable|required_if:change_password,1|string|min:8|confirmed',
        ]);

        $parent->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'is_active' => isset($validated['is_active']),
        ]);

        if (isset($validated['password'])) {
            $parent->update(['password' => Hash::make($validated['password'])]);
        }

        // Update student links
        if (isset($validated['student_ids'])) {
            $class = $teacher->headClass;
            $parent->students()->detach();
            
            foreach ($validated['student_ids'] as $studentId) {
                $student = Student::findOrFail($studentId);
                if ($student->classe_id === $class->id) {
                    $student->parents()->attach($parent->id, ['relationship' => $validated['relationship']]);
                }
            }
        }

        activity()
            ->causedBy(auth()->user())
            ->performedOn($parent)
            ->log('Compte parent mis à jour');

        return redirect()->route('teacher.parent-management.index', $teacher->head_class_id)
            ->with('success', "Parent '{$parent->name}' mis à jour avec succès.");
    }

    /**
     * Delete a parent account.
     *
     * @route DELETE /teacher/parent-management/{parent}
     */
    public function destroy(User $parent)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);
        abort_unless($parent->role === 'parent', 403);

        $name = $parent->name;
        $classId = $teacher->head_class_id;
        
        // Revoke all tokens
        $parent->accessTokens()->update(['revoked_at' => now()]);
        
        // Detach from students
        $parent->students()->detach();
        
        // Delete user
        $parent->delete();

        activity()
            ->causedBy(auth()->user())
            ->log('Compte parent supprimé: ' . $name);

        return redirect()->route('teacher.parent-management.index', $classId)
            ->with('success', "Parent '{$name}' supprimé avec succès.");
    }

    /**
     * Show form to generate tokens.
     *
     * @route GET /teacher/parent-management/{class}/generate-tokens
     */
    public function generateTokensForm(Classe $class)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal && $class->id === $teacher->head_class_id, 403);

        $parents = User::where('role', 'parent')
            ->whereHas('students', function($q) use ($class) {
                $q->where('classe_id', $class->id);
            })
            ->with('accessTokens')
            ->get();

        return view('teacher.parent-management.generate-tokens', [
            'class' => $class,
            'parents' => $parents,
        ]);
    }

    /**
     * Store generated tokens.
     *
     * @route POST /teacher/parent-management/{class}/store-tokens
     */
    public function storeTokens(Request $request, Classe $class)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal && $class->id === $teacher->head_class_id, 403);

        $validated = $request->validate([
            'parent_ids' => 'required|array|min:1',
            'parent_ids.*' => 'exists:users,id',
            'validity_days' => 'required|integer|between:1,365',
            'max_uses' => 'nullable|integer|min:1',
        ]);

        $count = 0;
        foreach ($validated['parent_ids'] as $parentId) {
            $parent = User::findOrFail($parentId);
            if ($parent->role !== 'parent') continue;

            $token = ParentAccessToken::generateToken();
            
            ParentAccessToken::create([
                'user_id' => $parent->id,
                'token' => $token,
                'expires_at' => now()->addDays($validated['validity_days']),
                'max_uses' => $validated['max_uses'] ?? null,
            ]);

            $count++;
        }

        return redirect()->route('teacher.parent-management.tokens', $class)
            ->with('success', "$count token(s) générés avec succès.");
    }

    /**
     * List al active tokens.
     *
     * @route GET /teacher/parent-management/{class}/tokens
     */
    public function listTokens(Classe $class)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal && $class->id === $teacher->head_class_id, 403);

        $tokens = ParentAccessToken::active()
            ->with(['user', 'user.students'])
            ->orderByDesc('expires_at')
            ->paginate(20);

        return view('teacher.parent-management.tokens', [
            'class' => $class,
            'tokens' => $tokens,
        ]);
    }

    /**
     * Revoke a token.
     *
     * @route DELETE /teacher/parent-management/{token}/revoke
     */
    public function revokeToken(ParentAccessToken $token)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);

        $classId = $teacher->head_class_id;
        $token->revoke();

        activity()
            ->causedBy(auth()->user())
            ->performedOn($token)
            ->log('Token révoqué');

        return back()->with('success', 'Token révoqué avec succès.');
    }

    /**
     * Export tokens as CSV.
     *
     * @route GET /teacher/parent-management/{class}/export-tokens
     */
    public function exportTokensCSV(Classe $class)
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal && $class->id === $teacher->head_class_id, 403);

        $tokens = ParentAccessToken::active()
            ->with(['user', 'user.students'])
            ->orderByDesc('created_at')
            ->get();

        $filename = 'parent-tokens-' . $class->name . '-' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function() use ($tokens) {
            $file = fopen('php://output', 'w');
            
            // Header row
            fputcsv($file, ['Parent', 'Email', 'Token', 'Enfants', 'Expire le', 'Généré le']);
            
            // Data rows
            foreach ($tokens as $token) {
                $students = $token->user->students->pluck('user.display_name')->join(', ');
                fputcsv($file, [
                    $token->user->name,
                    $token->user->email,
                    $token->token,
                    $students,
                    $token->expires_at->format('d/m/Y'),
                    $token->created_at->format('d/m/Y H:i'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
            ->withProperties(['count' => $created])
            ->log('Tokens d\'accès parents générés en batch');

        return back()->with('success', "$created token(s) généré(s) avec succès.");
    }

    /**
     * Export parent access tokens as CSV.
     *
     * @route GET /teacher/parent-management/export-tokens
     */
    public function exportTokensCSV()
    {
        $teacher = Auth::user()->teacher;
        abort_unless($teacher && $teacher->is_prof_principal, 403);

        $tokens = ParentAccessToken::where('teacher_id', $teacher->id)
            ->with(['student.user'])
            ->orderByDesc('created_at')
            ->get();

        $rows = ["Étudiant,Email,Téléphone,Token,Lien d'accès,Relation,Généré le,Expire le,Utilisé le"];
        
        foreach ($tokens as $token) {
            $student = $token->student->user->display_name;
            $email = $token->email ?? '';
            $phone = $token->phone ?? '';
            $tokenStr = $token->token;
            $link = route('parent.register-with-token', $tokenStr);
            $relation = $token->relationship_label;
            $created = $token->created_at->format('Y-m-d');
            $expires = $token->expires_at?->format('Y-m-d') ?? 'Aucune';
            $used = $token->used_at?->format('Y-m-d H:i') ?? '—';
            
            $rows[] = "\"$student\",\"$email\",\"$phone\",\"$tokenStr\",\"$link\",\"$relation\",\"$created\",\"$expires\",\"$used\"";
        }

        $content = implode("\n", $rows);
        return response($content)
            ->header('Content-Type', 'text/csv; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename=parent_tokens_' . now()->format('Y-m-d') . '.csv');
    }
}
