<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\FeeSetting;
use App\Models\Payment;
use App\Models\Student;
use Illuminate\Http\Request;

class FeeController extends Controller
{
    /**
     * Affiche la liste des frais
     */
    public function index(Request $request)
    {
        $query = Fee::query();

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $fees = $query->paginate(20);
        $feeSettings = FeeSetting::first();

        return view('admin.fees.index', compact('fees', 'feeSettings'));
    }

    /**
     * Crée un nouveau frais
     */
    public function create()
    {
        return view('admin.fees.create');
    }

    /**
     * Stocke un nouveau frais
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'is_mandatory' => 'boolean',
            'status' => 'required|in:active,inactive',
        ]);

        Fee::create($validated);

        return redirect()->route('admin.fees.index')
                        ->with('success', 'Frais créés');
    }

    /**
     * Édite un frais
     */
    public function edit(Fee $fee)
    {
        return view('admin.fees.edit', compact('fee'));
    }

    /**
     * Met à jour un frais
     */
    public function update(Request $request, Fee $fee)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'due_date' => 'nullable|date',
            'is_mandatory' => 'boolean',
            'status' => 'required|in:active,inactive',
        ]);

        $fee->update($validated);

        return redirect()->route('admin.fees.index')
                        ->with('success', 'Frais mis à jour');
    }

    /**
     * Supprime un frais
     */
    public function destroy(Fee $fee)
    {
        $fee->delete();
        return redirect()->route('admin.fees.index')
                        ->with('success', 'Frais supprimé');
    }

    /**
     * Assigne les frais aux étudiants
     */
    public function assignToClass(Request $request)
    {
        $validated = $request->validate([
            'fee_id' => 'required|exists:fees,id',
            'classe_id' => 'required|exists:classes,id',
        ]);

        $students = Student::where('classe_id', $validated['classe_id'])->get();
        $fee = Fee::find($validated['fee_id']);

        $assigned = 0;
        foreach ($students as $student) {
            $payment = Payment::firstOrCreate(
                ['student_id' => $student->id, 'fee_id' => $fee->id],
                [
                    'amount' => $fee->amount,
                    'status' => 'pending',
                    'due_date' => $fee->due_date,
                ]
            );
            if ($payment->wasRecentlyCreated) {
                $assigned++;
            }
        }

        return redirect()->back()
                        ->with('success', "{$assigned} paiements assignés");
    }

    /**
     * Rapport des frais
     */
    public function report(Request $request)
    {
        $startDate = $request->start_date ?? now()->startOfMonth();
        $endDate = $request->end_date ?? now()->endOfMonth();

        $fees = Fee::with(['payments' => function ($q) use ($startDate, $endDate) {
            $q->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        $stats = [
            'total_expected' => Payment::whereBetween('due_date', [$startDate, $endDate])
                ->where('status', '!=', 'paid')
                ->sum('amount'),
            'total_paid' => Payment::whereBetween('paid_at', [$startDate, $endDate])
                ->where('status', 'paid')
                ->sum('amount'),
            'pending' => Payment::where('status', 'pending')
                ->where('due_date', '<', now())
                ->count(),
        ];

        return view('admin.fees.report', compact('fees', 'stats', 'startDate', 'endDate'));
    }

    /**
     * Gestion des paramètres de frais
     */
    public function settings()
    {
        $settings = FeeSetting::first() ?? new FeeSetting();
        return view('admin.fees.settings', compact('settings'));
    }

    /**
     * Met à jour les paramètres
     */
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'payment_deadline' => 'required|integer|min:1',
            'late_payment_percentage' => 'required|numeric|min:0|max:100',
            'allow_installments' => 'nullable|boolean',
            'max_installments' => 'required_if:allow_installments,1|integer|min:2',
        ]);

        // Gérer le booléen allow_installments
        $validated['allow_installments'] = $request->has('allow_installments');

        $settings = FeeSetting::first() ?? new FeeSetting();
        $settings->fill($validated)->save();

        return redirect()->back()
                        ->with('success', 'Paramètres mis à jour');
    }
}
