<?php

namespace App\Http\Controllers\Admin;

use App\Models\BulletinStructure;
use App\Models\Classe;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;

class BulletinStructureValidationController extends Controller
{
    /**
     * Display list of structures awaiting validation
     */
    public function index(): View
    {
        $this->authorize('manage bulletin structures');

        // Get structures that need validation or recent ones
        $structures = BulletinStructure::query()
            ->where('is_verified', false)
            ->orWhere('is_verified', true)
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('admin.bulletin-structure.validation.index', [
            'structures' => $structures,
        ]);
    }

    /**
     * Show detailed validation view for a specific structure
     */
    public function show(BulletinStructure $structure): View
    {
        $this->authorize('manage bulletin structures');

        return view('admin.bulletin-structure.validation.show', [
            'structure' => $structure->load('classe', 'createdBy'),
            'subjects' => $structure->structure_json['subjects'] ?? [],
            'coefficients' => $structure->structure_json['coefficients'] ?? [],
            'calculationRules' => $structure->calculation_rules ?? [],
            'fieldCoordinates' => $structure->structure_json['field_coordinates'] ?? [],
            'appreciationRules' => $structure->structure_json['appreciation_rules'] ?? [],
        ]);
    }

    /**
     * Display form to edit and validate structure
     */
    public function edit(BulletinStructure $structure): View
    {
        $this->authorize('manage bulletin structures');

        $structure->load('classe', 'createdBy');

        return view('admin.bulletin-structure.validation.edit', [
            'structure' => $structure,
            'subjects' => $structure->structure_json['subjects'] ?? [],
            'coefficients' => $structure->structure_json['coefficients'] ?? [],
            'gradingScale' => $structure->structure_json['grading_scale'] ?? [],
            'calculationRules' => $structure->calculation_rules ?? [],
            'appreciationRules' => $structure->structure_json['appreciation_rules'] ?? [],
            'fieldCoordinates' => $structure->structure_json['field_coordinates'] ?? [],
        ]);
    }

    /**
     * Update structure and mark as verified
     */
    public function update(Request $request, BulletinStructure $structure): RedirectResponse
    {
        $this->authorize('manage bulletin structures');

        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'subjects' => 'required|array|min:1',
            'subjects.*' => 'required|string',
            'coefficients' => 'required|array',
            'coefficients.*' => 'required|numeric|min:0.1|max:10',
            'grading_scale' => 'required|array',
            'grading_scale.min' => 'required|numeric',
            'grading_scale.max' => 'required|numeric|gt:grading_scale.min',
            'calculation_rules' => 'required|array',
            'appreciation_rules' => 'nullable|array',
            'validation_notes' => 'nullable|string|max:1000',
        ]);

        // Ensure all subjects have coefficients
        foreach ($validated['subjects'] as $subject) {
            if (!isset($validated['coefficients'][$subject])) {
                return back()->withErrors([
                    'coefficients' => "Coefficient missing for subject: $subject",
                ]);
            }
        }

        try {
            // Prepare structure JSON
            $structureJson = $structure->structure_json ?? [];
            $structureJson['subjects'] = $validated['subjects'];
            $structureJson['coefficients'] = $validated['coefficients'];
            $structureJson['grading_scale'] = $validated['grading_scale'];
            if (isset($validated['appreciation_rules'])) {
                $structureJson['appreciation_rules'] = $validated['appreciation_rules'];
            }

            // Update structure
            $structure->update([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'structure_json' => $structureJson,
                'calculation_rules' => $validated['calculation_rules'],
                'is_verified' => true,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'validation_notes' => $validated['validation_notes'] ?? null,
                'updated_by' => Auth::id(),
            ]);

            return redirect()->route('admin.bulletin-structure.validation.show', $structure)
                ->with('success', 'Bulletin structure validated and updated successfully');

        } catch (\Exception $e) {
            \Log::error('Bulletin structure validation error', [
                'structure_id' => $structure->id,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors([
                'general' => 'An error occurred while saving the structure: ' . $e->getMessage(),
            ]);
        }
    }

    /**
     * Approve/verify a structure
     */
    public function approve(Request $request, BulletinStructure $structure): RedirectResponse
    {
        $this->authorize('manage bulletin structures');

        $request->validate([
            'approval_notes' => 'nullable|string|max:500',
        ]);

        $structure->update([
            'is_verified' => true,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'validation_notes' => $request->input('approval_notes'),
            'updated_by' => Auth::id(),
        ]);

        // Log activity
        activity('bulletin_structure_approved')
            ->performedOn($structure)
            ->withProperties(['approved_by' => Auth::user()->name])
            ->log('Bulletin structure approved for use');

        return redirect()->route('admin.bulletin-structure.validation.show', $structure)
            ->with('success', 'Bulletin structure approved successfully');
    }

    /**
     * Reject/disapprove a structure
     */
    public function reject(Request $request, BulletinStructure $structure): RedirectResponse
    {
        $this->authorize('manage bulletin structures');

        $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ]);

        $structure->update([
            'is_verified' => false,
            'validation_notes' => $request->input('rejection_reason'),
            'updated_by' => Auth::id(),
        ]);

        // Log activity
        activity('bulletin_structure_rejected')
            ->performedOn($structure)
            ->withProperties([
                'rejected_by' => Auth::user()->name,
                'reason' => $request->input('rejection_reason'),
            ])
            ->log('Bulletin structure rejected');

        return redirect()->route('admin.bulletin-structure.validation.index')
            ->with('warning', 'Bulletin structure rejected. Professor principal will be notified.');
    }

    /**
     * Activate/enable a verified structure for use
     */
    public function activate(BulletinStructure $structure): RedirectResponse
    {
        $this->authorize('manage bulletin structures');

        if (!$structure->is_verified) {
            return back()->withErrors([
                'general' => 'Only verified structures can be activated',
            ]);
        }

        $structure->update([
            'is_active' => true,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.bulletin-structure.validation.show', $structure)
            ->with('success', 'Bulletin structure activated successfully');
    }

    /**
     * Deactivate/disable a structure
     */
    public function deactivate(BulletinStructure $structure): RedirectResponse
    {
        $this->authorize('manage bulletin structures');

        $structure->update([
            'is_active' => false,
            'updated_by' => Auth::id(),
        ]);

        return redirect()->route('admin.bulletin-structure.validation.show', $structure)
            ->with('success', 'Bulletin structure deactivated');
    }

    /**
     * Export structure as JSON/CSV
     */
    public function export(BulletinStructure $structure)
    {
        $this->authorize('manage bulletin structures');

        $data = [
            'name' => $structure->name,
            'description' => $structure->description,
            'classe' => $structure->classe->name ?? 'N/A',
            'created_by' => $structure->createdBy->name ?? 'N/A',
            'verified' => $structure->is_verified,
            'verified_by' => $structure->verifiedBy->name ?? 'N/A',
            'verified_at' => $structure->verified_at,
            'active' => $structure->is_active,
            'ocr_confidence' => $structure->ocr_confidence . '%',
            'structure' => $structure->structure_json,
            'calculation_rules' => $structure->calculation_rules,
        ];

        $filename = 'bulletin-structure-' . $structure->id . '-' . now()->format('Y-m-d-His') . '.json';

        return response()->json($data, 200, [
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ]);
    }

    /**
     * Bulk action: verify multiple structures
     */
    public function bulkVerify(Request $request): RedirectResponse
    {
        $this->authorize('manage bulletin structures');

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:bulletin_structures,id',
        ]);

        $count = BulletinStructure::whereIn('id', $request->input('ids'))
            ->update([
                'is_verified' => true,
                'verified_by' => Auth::id(),
                'verified_at' => now(),
                'updated_by' => Auth::id(),
            ]);

        return redirect()->route('admin.bulletin-structure.validation.index')
            ->with('success', "$count structure(s) verified successfully");
    }

    /**
     * Get validation statistics
     */
    public function stats()
    {
        $this->authorize('manage bulletin structures');

        return response()->json([
            'total' => BulletinStructure::count(),
            'pending_verification' => BulletinStructure::where('is_verified', false)->count(),
            'verified' => BulletinStructure::where('is_verified', true)->count(),
            'active' => BulletinStructure::where('is_active', true)->where('is_verified', true)->count(),
            'average_confidence' => round(BulletinStructure::avg('ocr_confidence')),
        ]);
    }
}
