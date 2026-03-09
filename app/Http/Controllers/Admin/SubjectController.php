<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subject;
use App\Models\Classe;
use Illuminate\Http\Request;

/**
 * Admin\SubjectController — Gestion des Matières
 */
class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::with('teachers')->orderBy('name');

        // Filter by search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('name_en', 'like', "%{$search}%");
        }

        $subjects = $query->paginate(20)->appends($request->query());

        return view('admin.subjects.index', compact('subjects'));
    }

    public function create()
    {
        return view('admin.subjects.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:subjects',
            'code'        => 'required|string|max:20|unique:subjects',
            'coefficient' => 'required|numeric|min:0.5|max:10',
            'section'     => 'required|in:francophone,anglophone,both',
        ]);

        $subject = Subject::create($data);
        activity()->causedBy(auth()->user())->performedOn($subject)->log('Matière créée');

        return redirect()->route('admin.subjects.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Matière créée.' : 'Subject created.');
    }

    public function edit(int $id)
    {
        $subject = Subject::findOrFail($id);
        return view('admin.subjects.edit', compact('subject'));
    }

    public function update(Request $request, int $id)
    {
        $subject = Subject::findOrFail($id);
        $data    = $request->validate([
            'name'        => "required|string|max:100|unique:subjects,name,{$id}",
            'coefficient' => 'required|numeric|min:0.5|max:10',
        ]);

        $subject->update($data);
        return redirect()->route('admin.subjects.index')->with('success', 'Matière modifiée.');
    }

    public function destroy(Subject $subject)
    {
        activity()->causedBy(auth()->user())->performedOn($subject)->log('Matière supprimée');
        $subject->delete();

        return redirect()->route('admin.subjects.index')
            ->with('success', app()->getLocale() === 'fr' ? 'Matière supprimée.' : 'Subject deleted.');
    }
}
