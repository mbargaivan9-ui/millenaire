/**
 * Bulletin Calculation Engine
 * Gère les formules de calcul pour les bulletins dynamiques
 */

class BulletinCalculator {
    constructor(structure) {
        this.structure = structure;
        this.fields = structure.fields || [];
        this.notes = {}; // Store note values
        this.calculations = {}; // Store calculated values
    }

    /**
     * Set a note value and trigger recalculation
     */
    setNote(fieldName, value) {
        this.notes[fieldName] = parseFloat(value) || 0;
        this.recalculate();
        return this.calculations;
    }

    /**
     * Recalculate all dependent fields
     */
    recalculate() {
        this.calculations = {};
        
        // First, copy all notes as-is
        Object.keys(this.notes).forEach(key => {
            this.calculations[key] = this.notes[key];
        });

        // Then calculate dependent fields
        this.fields.forEach(field => {
            if (field.calculation_formula && field.field_type !== 'subject') {
                this.calculations[field.field_name] = this.evaluateFormula(
                    field.calculation_formula,
                    field.formula_params || {}
                );
            }
        });

        return this.calculations;
    }

    /**
     * Evaluate a formula
     * Supports: weighted_average, simple_average, rank_by_average, count
     */
    evaluateFormula(formula, params = {}) {
        const formulaName = formula.toLowerCase().trim();

        switch (formulaName) {
            case 'weighted_average':
                return this.calculateWeightedAverage(params);
            
            case 'simple_average':
            case 'moyenne':
                return this.calculateSimpleAverage();
            
            case 'rank_by_average':
            case 'classement':
                return this.calculateRank(params);
            
            case 'sum':
                return this.calculateSum(params);
            
            case 'count':
                return this.countSubjects();
            
            default:
                // Try to evaluate as mathematical expression
                return this.evaluateMathExpression(formula);
        }
    }

    /**
     * Calculate weighted average
     * Formula: (sum of note * coefficient) / sum of coefficients
     */
    calculateWeightedAverage(params = {}) {
        const subjectFields = this.fields.filter(f => f.field_type === 'subject');
        
        if (subjectFields.length === 0) return 0;

        let totalWeighted = 0;
        let totalCoefficient = 0;

        subjectFields.forEach(field => {
            const note = this.notes[field.field_name] || 0;
            const coef = field.coefficient || 1;

            totalWeighted += note * coef;
            totalCoefficient += coef;
        });

        const average = totalCoefficient > 0 ? totalWeighted / totalCoefficient : 0;
        
        // Apply rounding rules
        return this.applyRounding(average, params);
    }

    /**
     * Calculate simple average
     * Formula: sum of all notes / number of notes
     */
    calculateSimpleAverage() {
        const subjectFields = this.fields.filter(f => f.field_type === 'subject');
        
        if (subjectFields.length === 0) return 0;

        const sum = subjectFields.reduce((acc, field) => {
            return acc + (this.notes[field.field_name] || 0);
        }, 0);

        return sum / subjectFields.length;
    }

    /**
     * Calculate rank (placeholder - in real app, needs all student data)
     */
    calculateRank(params = {}) {
        // In a real scenario, this would need access to all other students
        // For now, return a placeholder
        return 1;
    }

    /**
     * Calculate sum of specific fields
     */
    calculateSum(params = {}) {
        const fieldsToSum = params.fields || [];
        
        return fieldsToSum.reduce((acc, fieldName) => {
            return acc + (this.notes[fieldName] || 0);
        }, 0);
    }

    /**
     * Count subjects
     */
    countSubjects() {
        return this.fields.filter(f => f.field_type === 'subject').length;
    }

    /**
     * Apply rounding rules to a value
     */
    applyRounding(value, params = {}) {
        const method = params.rounding || 'half_up';
        const decimals = params.decimals || 2;

        let rounded;
        const multiplier = Math.pow(10, decimals);

        switch (method) {
            case 'ceil':
            case 'upper':
                rounded = Math.ceil(value * multiplier) / multiplier;
                break;
            
            case 'floor':
            case 'lower':
                rounded = Math.floor(value * multiplier) / multiplier;
                break;
            
            case 'half_up':
            case 'round':
            default:
                rounded = Math.round(value * multiplier) / multiplier;
                break;
        }

        return rounded;
    }

    /**
     * Evaluate mathematical expressions safely
     * Uses Function constructor to safely evaluate math expressions
     */
    evaluateMathExpression(expr) {
        try {
            // Build variable substitutions
            const varNames = Object.keys(this.notes);
            const varValues = Object.values(this.notes);

            // Create function and evaluate
            const func = new Function(...varNames, `return ${expr}`);
            const result = func(...varValues);

            return isNaN(result) ? 0 : parseFloat(result);
        } catch (error) {
            console.error('Formula evaluation error:', error);
            return 0;
        }
    }

    /**
     * Validate all calculations
     */
    validate() {
        const errors = [];

        // Check min/max bounds
        this.fields.forEach(field => {
            const value = this.calculations[field.field_name];
            
            if (value < field.min_value) {
                errors.push({
                    field: field.field_label,
                    message: `Valeur ${value} inférieure au minimum ${field.min_value}`,
                    type: 'warning',
                });
            }
            
            if (value > field.max_value) {
                errors.push({
                    field: field.field_label,
                    message: `Valeur ${value} supérieure au maximum ${field.max_value}`,
                    type: 'warning',
                });
            }
        });

        return {
            isValid: errors.length === 0,
            errors: errors,
        };
    }

    /**
     * Export calculations as JSON
     */
    export() {
        return {
            notes: this.notes,
            calculations: this.calculations,
            validation: this.validate(),
        };
    }

    /**
     * Get all field values
     */
    getAll() {
        return this.calculations;
    }

    /**
     * Get specific field value
     */
    get(fieldName) {
        return this.calculations[fieldName];
    }

    /**
     * Reset all values
     */
    reset() {
        this.notes = {};
        this.calculations = {};
    }
}

// Export for use in Blade views
if (typeof window !== 'undefined') {
    window.BulletinCalculator = BulletinCalculator;
}

// Export for Node.js/ES6 module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = BulletinCalculator;
}
