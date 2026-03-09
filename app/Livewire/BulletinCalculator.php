<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\DynamicBulletinStructure;

class BulletinCalculator extends Component
{
    public DynamicBulletinStructure $structure;
    public array $sampleValues = [];
    public array $calculations = [];
    public array $validationErrors = [];
    public bool $showCalculator = false;
    public string $sampleStudent = '';

    public function mount(DynamicBulletinStructure $structure)
    {
        $this->structure = $structure;
        $this->initializeSampleValues();
    }

    /**
     * Initialize sample values for all subject fields
     */
    protected function initializeSampleValues(): void
    {
        $this->structure->loadMissing('fields');
        
        foreach ($this->structure->fields as $field) {
            if ($field->field_type === 'subject') {
                // Initialize with middle of range
                $min = $field->min_value ?? 0;
                $max = $field->max_value ?? 20;
                $this->sampleValues[$field->field_name] = ($min + $max) / 2;
            }
        }

        $this->sampleStudent = 'Élève Test ' . rand(1, 999);
        $this->recalculate();
    }

    /**
     * Update a sample value and recalculate
     */
    public function updateSampleValue(string $fieldName, $value): void
    {
        $this->sampleValues[$fieldName] = (float) $value;
        $this->recalculate();
    }

    /**
     * Perform all calculations based on current sample values
     */
    public function recalculate(): void
    {
        $this->calculations = [];
        $this->validationErrors = [];

        // Load all fields with their relationships
        $this->structure->loadMissing('fields');

        // First pass: collect all note values
        $noteValues = $this->sampleValues;

        // Second pass: calculate derived fields
        foreach ($this->structure->fields as $field) {
            if ($field->field_type === 'subject') {
                // Subject field - already in notes
                $this->calculations[$field->field_name] = $noteValues[$field->field_name] ?? 0;
            } elseif ($field->field_type === 'calculated' && $field->calculation_formula) {
                // Calculated field - evaluate formula
                $this->calculations[$field->field_name] = $this->evaluateFormula(
                    $field->calculation_formula,
                    $field->formula_params ?? [],
                    $noteValues
                );
            }
        }

        // Validate results
        $this->validateCalculations();
    }

    /**
     * Evaluate formula based on type
     */
    protected function evaluateFormula(
        string $formula,
        array $params = [],
        array $noteValues = []
    ): float {
        $formula = strtolower(trim($formula));

        return match ($formula) {
            'weighted_average', 'moyenne_pondérée' => $this->calculateWeightedAverage(
                $noteValues,
                $params
            ),
            'simple_average', 'moyenne' => $this->calculateSimpleAverage($noteValues),
            'sum' => $this->calculateSum($noteValues, $params),
            'count' => $this->countSubjects(),
            default => $this->evaluateMathExpression($formula, $noteValues),
        };
    }

    /**
     * Calculate weighted average
     */
    protected function calculateWeightedAverage(array $noteValues, array $params = []): float
    {
        $this->structure->loadMissing('fields');
        $subjectFields = $this->structure->fields->where('field_type', 'subject');

        if ($subjectFields->isEmpty()) {
            return 0;
        }

        $totalWeighted = 0;
        $totalCoefficient = 0;

        foreach ($subjectFields as $field) {
            $note = $noteValues[$field->field_name] ?? 0;
            $coef = $field->coefficient ?? 1;

            $totalWeighted += $note * $coef;
            $totalCoefficient += $coef;
        }

        $average = $totalCoefficient > 0 ? $totalWeighted / $totalCoefficient : 0;

        return $this->applyRounding($average, $params);
    }

    /**
     * Calculate simple average
     */
    protected function calculateSimpleAverage(array $noteValues): float
    {
        $this->structure->loadMissing('fields');
        $subjectFields = $this->structure->fields->where('field_type', 'subject');

        if ($subjectFields->isEmpty()) {
            return 0;
        }

        $sum = 0;
        foreach ($subjectFields as $field) {
            $sum += $noteValues[$field->field_name] ?? 0;
        }

        return $sum / $subjectFields->count();
    }

    /**
     * Calculate sum
     */
    protected function calculateSum(array $noteValues, array $params = []): float
    {
        $fieldsToSum = $params['fields'] ?? [];
        $sum = 0;

        foreach ($fieldsToSum as $fieldName) {
            $sum += $noteValues[$fieldName] ?? 0;
        }

        return $sum;
    }

    /**
     * Count subjects
     */
    protected function countSubjects(): float
    {
        $this->structure->loadMissing('fields');
        return $this->structure->fields->where('field_type', 'subject')->count();
    }

    /**
     * Apply rounding rules
     */
    protected function applyRounding(float $value, array $params = []): float
    {
        $method = $params['rounding'] ?? 'half_up';
        $decimals = $params['decimals'] ?? 2;

        $multiplier = pow(10, $decimals);

        return match ($method) {
            'ceil', 'upper' => ceil($value * $multiplier) / $multiplier,
            'floor', 'lower' => floor($value * $multiplier) / $multiplier,
            'half_up', 'round' => round($value * $multiplier) / $multiplier,
            default => round($value * $multiplier) / $multiplier,
        };
    }

    /**
     * Evaluate mathematical expression safely
     */
    protected function evaluateMathExpression(string $expr, array $noteValues = []): float
    {
        try {
            // Build evaluation context
            $vars = [];
            foreach ($noteValues as $name => $value) {
                $vars[$name] = (float) $value;
            }

            // Safe evaluation using basic math operations
            return $this->safeMathEval($expr, $vars);
        } catch (\Exception $e) {
            \Log::error('Formula evaluation error: ' . $e->getMessage());
            return 0;
        }
    }

    /**
     * Safe mathematical expression evaluator
     * Only allows basic arithmetic operations
     */
    protected function safeMathEval(string $expr, array $vars = []): float
    {
        // Remove whitespace
        $expr = preg_replace('/\s+/', '', $expr);

        // Replace variable names with their values
        foreach ($vars as $varName => $value) {
            $expr = preg_replace('/\b' . preg_quote($varName) . '\b/', (string) $value, $expr);
        }

        // Check for invalid characters
        if (!preg_match('/^[\d+\-*\/().]+$/', $expr)) {
            throw new \Exception('Invalid characters in expression');
        }

        // Evaluate using PHP's eval with safety measures
        try {
            $result = @eval("return {$expr};");
            return is_numeric($result) ? (float) $result : 0;
        } catch (\Exception $e) {
            throw new \Exception('Invalid mathematical expression');
        }
    }

    /**
     * Validate calculations against field constraints
     */
    protected function validateCalculations(): void
    {
        $this->structure->loadMissing('fields');

        foreach ($this->structure->fields as $field) {
            $value = $this->calculations[$field->field_name] ?? 0;

            if ($field->min_value !== null && $value < $field->min_value) {
                $this->validationErrors[] = [
                    'field' => $field->field_label,
                    'message' => "Valeur {$value} inférieure au minimum {$field->min_value}",
                    'type' => 'warning',
                ];
            }

            if ($field->max_value !== null && $value > $field->max_value) {
                $this->validationErrors[] = [
                    'field' => $field->field_label,
                    'message' => "Valeur {$value} supérieure au maximum {$field->max_value}",
                    'type' => 'error',
                ];
            }
        }
    }

    /**
     * Reset to initial sample values
     */
    public function resetSampleValues(): void
    {
        $this->initializeSampleValues();
        $this->dispatch('calculator:reset');
    }

    /**
     * Toggle calculator visibility
     */
    public function toggleCalculator(): void
    {
        $this->showCalculator = !$this->showCalculator;
    }

    /**
     * Generate random student name
     */
    public function randomizeStudent(): void
    {
        $names = ['Élève Test', 'Étudiant', 'Apprenant'];
        $this->sampleStudent = $names[array_rand($names)] . ' ' . rand(1000, 9999);
    }

    /**
     * Export calculation results
     */
    public function exportResults(): array
    {
        return [
            'student' => $this->sampleStudent,
            'date' => now()->toDateTimeString(),
            'notes' => $this->sampleValues,
            'calculations' => $this->calculations,
            'structure_id' => $this->structure->id,
            'structure_name' => $this->structure->structure_name,
        ];
    }

    public function render()
    {
        return view('livewire.bulletin-calculator', [
            'structure' => $this->structure,
            'sampleValues' => $this->sampleValues,
            'calculations' => $this->calculations,
            'validationErrors' => $this->validationErrors,
            'subjectFields' => $this->structure->fields->where('field_type', 'subject'),
            'calculatedFields' => $this->structure->fields->where('field_type', 'calculated'),
        ]);
    }
}
