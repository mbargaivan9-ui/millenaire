<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Models\{BulletinTemplate, Classe, Subject};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * BulletinTemplateController
 *
 * Handles the Template Digitizer functionality
 * Allows Prof Principal to upload template images and define field zones
 */
class BulletinTemplateController extends Controller
{
    /**
     * Show the bulletin template list
     */
    public function index()
    {
        $classes = auth()->user()->teacher->classes ?? collect();
        $templates = BulletinTemplate::whereIn('classe_id', $classes->pluck('id'))
            ->with(['classe', 'creator'])
            ->latest()
            ->paginate(15);

        return view('teacher.bulletin-templates.index', compact('templates', 'classes'));
    }

    /**
     * Show form to create or edit bulletin template
     */
    public function create(Classe $classe = null)
    {
        // Verify teacher is head of the class or admin
        if ($classe && !$this->isAuthorizedForClass($classe)) {
            abort(403, 'Unauthorized');
        }

        $subjects = Subject::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('teacher.bulletin-templates.create', compact('classe', 'subjects'));
    }

    /**
     * Edit existing template
     */
    public function edit(BulletinTemplate $bulletinTemplate)
    {
        // Verify authorization
        if (!$this->isAuthorizedForTemplate($bulletinTemplate)) {
            abort(403, 'Unauthorized');
        }

        $subjects = Subject::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('teacher.bulletin-templates.edit', compact('bulletinTemplate', 'subjects'));
    }

    /**
     * Store the bulletin template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'classe_id' => ['required', 'exists:classes,id'],
            'name' => ['required', 'string', 'max:255'],
            'template_image' => ['required', 'image', 'mimes:jpeg,png,jpg,gif', 'max:5120'],
        ]);

        // Verify authorization
        $classe = Classe::find($validated['classe_id']);
        if (!$this->isAuthorizedForClass($classe)) {
            abort(403, 'Unauthorized');
        }

        // Store image
        $imagePath = $request->file('template_image')->store('bulletin-templates', 'public');

        // Get image dimensions
        $imageSizeInfo = getimagesize(storage_path('app/public/' . $imagePath));
        $imageWidth = $imageSizeInfo[0];
        $imageHeight = $imageSizeInfo[1];

        // Create template
        $template = BulletinTemplate::create([
            'classe_id' => $validated['classe_id'],
            'name' => $validated['name'],
            'template_image_path' => $imagePath,
            'image_width' => $imageWidth,
            'image_height' => $imageHeight,
            'field_zones' => [],
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('teacher.bulletin-templates.edit', $template)
            ->with('success', 'Template created successfully. Now define the field zones!');
    }

    /**
     * Update field zones via AJAX
     */
    public function updateFieldZones(Request $request, BulletinTemplate $bulletinTemplate)
    {
        // Verify authorization
        if (!$this->isAuthorizedForTemplate($bulletinTemplate)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'field_zones' => ['required', 'array'],
            'field_zones.*.subject_id' => ['required', 'exists:subjects,id'],
            'field_zones.*.x' => ['required', 'numeric', 'min:0'],
            'field_zones.*.y' => ['required', 'numeric', 'min:0'],
            'field_zones.*.width' => ['required', 'numeric', 'min:1'],
            'field_zones.*.height' => ['required', 'numeric', 'min:1'],
            'field_zones.*.label' => ['nullable', 'string', 'max:255'],
        ]);

        // Update template
        $bulletinTemplate->update([
            'field_zones' => $validated['field_zones'],
            'updated_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Field zones updated successfully',
            'data' => $bulletinTemplate,
        ]);
    }

    /**
     * Add a single field zone
     */
    public function addFieldZone(Request $request, BulletinTemplate $bulletinTemplate)
    {
        if (!$this->isAuthorizedForTemplate($bulletinTemplate)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'x' => ['required', 'numeric', 'min:0'],
            'y' => ['required', 'numeric', 'min:0'],
            'width' => ['required', 'numeric', 'min:1'],
            'height' => ['required', 'numeric', 'min:1'],
            'label' => ['nullable', 'string', 'max:255'],
        ]);

        $bulletinTemplate->updateFieldZone($validated['subject_id'], $validated);

        return response()->json([
            'success' => true,
            'message' => 'Field zone added successfully',
            'data' => $bulletinTemplate->field_zones,
        ]);
    }

    /**
     * Remove a field zone
     */
    public function removeFieldZone(Request $request, BulletinTemplate $bulletinTemplate)
    {
        if (!$this->isAuthorizedForTemplate($bulletinTemplate)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
        ]);

        $bulletinTemplate->removeFieldZone($validated['subject_id']);

        return response()->json([
            'success' => true,
            'message' => 'Field zone removed successfully',
            'data' => $bulletinTemplate->field_zones,
        ]);
    }

    /**
     * Get template data (for AJAX requests)
     */
    public function show(BulletinTemplate $bulletinTemplate)
    {
        if (!$this->isAuthorizedForTemplate($bulletinTemplate)) {
            abort(403, 'Unauthorized');
        }

        return response()->json([
            'id' => $bulletinTemplate->id,
            'name' => $bulletinTemplate->name,
            'template_image_path' => asset('storage/' . $bulletinTemplate->template_image_path),
            'image_width' => $bulletinTemplate->image_width,
            'image_height' => $bulletinTemplate->image_height,
            'field_zones' => $bulletinTemplate->field_zones ?? [],
        ]);
    }

    /**
     * Delete a template
     */
    public function destroy(BulletinTemplate $bulletinTemplate)
    {
        if (!$this->isAuthorizedForTemplate($bulletinTemplate)) {
            abort(403, 'Unauthorized');
        }

        // Delete image
        if ($bulletinTemplate->template_image_path) {
            Storage::disk('public')->delete($bulletinTemplate->template_image_path);
        }

        $bulletinTemplate->delete();

        return redirect()->route('teacher.bulletin-templates.index')
            ->with('success', 'Template deleted successfully');
    }

    /**
     * Check if user is authorized to manage template
     */
    private function isAuthorizedForTemplate(BulletinTemplate $bulletinTemplate): bool
    {
        if (auth()->user()->isAdmin()) {
            return true;
        }

        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return false;
        }

        // Prof principal can only manage templates for their class
        if ($teacher->is_head_of_class && $teacher->head_class_id === $bulletinTemplate->classe_id) {
            return true;
        }

        return false;
    }

    /**
     * Check if user is authorized for a class
     */
    private function isAuthorizedForClass(Classe $classe): bool
    {
        if (auth()->user()->isAdmin()) {
            return true;
        }

        $teacher = auth()->user()->teacher;
        if (!$teacher) {
            return false;
        }

        return $teacher->is_head_of_class && $teacher->head_class_id === $classe->id;
    }
}
