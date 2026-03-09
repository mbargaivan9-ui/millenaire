<?php

namespace App\Http\Controllers;

use App\Models\BulletinTemplate;
use App\Models\Classe;
use App\Models\Subject;
use Illuminate\Http\Request;

class BulletinTemplateController extends Controller
{
    /**
     * Display a listing of bulletin templates
     */
    public function index(Request $request)
    {
        $query = BulletinTemplate::with('classe');
        
        if ($request->has('class_id')) {
            $query->where('class_id', $request->class_id);
        }
        
        $templates = $query->paginate(20);
        
        return view('bulletin-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new bulletin template
     */
    public function create()
    {
        $classes = Classe::get();
        $subjects = Subject::get();
        
        return view('bulletin-templates.create', compact('classes', 'subjects'));
    }

    /**
     * Store a newly created bulletin template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:255',
            'template_data' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        BulletinTemplate::create($validated);

        return redirect()->route('bulletin-templates.index')
            ->with('success', 'Modèle de bulletin créé avec succès');
    }

    /**
     * Display the specified bulletin template
     */
    public function show(BulletinTemplate $bulletinTemplate)
    {
        return view('bulletin-templates.show', compact('bulletinTemplate'));
    }

    /**
     * Show the form for editing the specified bulletin template
     */
    public function edit(BulletinTemplate $bulletinTemplate)
    {
        $classes = Classe::get();
        $subjects = Subject::get();
        
        return view('bulletin-templates.edit', compact('bulletinTemplate', 'classes', 'subjects'));
    }

    /**
     * Update the specified bulletin template
     */
    public function update(Request $request, BulletinTemplate $bulletinTemplate)
    {
        $validated = $request->validate([
            'class_id' => 'required|exists:classes,id',
            'name' => 'required|string|max:255',
            'template_data' => 'required|array',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $bulletinTemplate->update($validated);

        return redirect()->route('bulletin-templates.index')
            ->with('success', 'Modèle de bulletin mis à jour avec succès');
    }

    /**
     * Remove the specified bulletin template
     */
    public function destroy(BulletinTemplate $bulletinTemplate)
    {
        $bulletinTemplate->delete();

        return redirect()->route('bulletin-templates.index')
            ->with('success', 'Modèle de bulletin supprimé avec succès');
    }

    /**
     * Preview bulletin template
     */
    public function preview(BulletinTemplate $bulletinTemplate)
    {
        return view('bulletin-templates.preview', compact('bulletinTemplate'));
    }
}
