<?php

namespace App\Http\Controllers;

use App\Models\Subject;
use Illuminate\Http\Request;

class SubjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Subject::query();
        
        if ($request->search) {
            $query->where('name', 'like', "%{$request->search}%")
                  ->orWhere('code', 'like', "%{$request->search}%");
        }
        
        $subjects = $query->paginate(50);
        
        return view('admin.subjects.index', [
            'subjects' => $subjects
        ]);
    }
    
    public function create()
    {
        return view('admin.subjects.form');
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:subjects',
            'code' => 'required|string|max:10|unique:subjects',
            'description' => 'nullable|string',
            'coefficient' => 'nullable|numeric|min:0.5|max:10',
            'department' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        Subject::create($validated + [
            'is_active' => $request->boolean('is_active', true)
        ]);
        
        return redirect()->route('admin.subjects.index')
                        ->with('success', 'Matière créée');
    }
    
    public function edit(Subject $subject)
    {
        return view('admin.subjects.form', [
            'subject' => $subject
        ]);
    }
    
    public function update(Request $request, Subject $subject)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100|unique:subjects,name,' . $subject->id,
            'code' => 'required|string|max:10|unique:subjects,code,' . $subject->id,
            'description' => 'nullable|string',
            'coefficient' => 'nullable|numeric|min:0.5|max:10',
            'department' => 'nullable|string',
            'is_active' => 'boolean'
        ]);
        
        $subject->update($validated + [
            'is_active' => $request->boolean('is_active', true)
        ]);
        
        return redirect()->route('admin.subjects.index')
                        ->with('success', 'Matière mise à jour');
    }
    
    public function destroy(Subject $subject)
    {
        $subject->delete();
        
        return redirect()->route('admin.subjects.index')
                        ->with('success', 'Matière supprimée');
    }
}
