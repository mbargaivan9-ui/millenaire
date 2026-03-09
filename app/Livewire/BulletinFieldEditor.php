<?php

namespace App\Livewire;

use App\Models\DynamicBulletinStructure;
use App\Models\BulletinStructureField;
use Livewire\Component;
use Illuminate\Database\Eloquent\Collection;

class BulletinFieldEditor extends Component
{
    public DynamicBulletinStructure $structure;
    public Collection $fields;
    public $editingFieldId = null;
    public $showAddForm = false;
    
    // Form data for new/editing field
    public $fieldName = '';
    public $fieldLabel = '';
    public $fieldType = 'subject';
    public $coefficient = 1;
    public $minValue = 0;
    public $maxValue = 20;
    public $calculationFormula = null;
    public $displayOrder = 0;

    protected $rules = [
        'fieldName' => 'required|string|max:255',
        'fieldLabel' => 'required|string|max:255',
        'fieldType' => 'required|in:subject,coefficient,note,average,rank,appreciation,custom',
        'coefficient' => 'required|numeric|min:0|max:100',
        'minValue' => 'required|numeric|min:0',
        'maxValue' => 'required|numeric|min:0',
        'calculationFormula' => 'nullable|string|max:500',
        'displayOrder' => 'required|integer|min:0',
    ];

    public function mount(DynamicBulletinStructure $structure)
    {
        $this->structure = $structure;
        $this->loadFields();
    }

    public function loadFields()
    {
        $this->fields = $this->structure->fields()->orderBy('display_order')->get();
    }

    /**
     * Ajouter un nouveau champ
     */
    public function addField()
    {
        $this->validate();

        BulletinStructureField::create([
            'bulletin_structure_id' => $this->structure->id,
            'field_name' => $this->fieldName,
            'field_label' => $this->fieldLabel,
            'field_type' => $this->fieldType,
            'coefficient' => $this->coefficient,
            'min_value' => $this->minValue,
            'max_value' => $this->maxValue,
            'calculation_formula' => $this->calculationFormula,
            'display_order' => $this->displayOrder,
        ]);

        $this->resetForm();
        $this->loadFields();
        $this->dispatch('field-added', ['message' => 'Champ ajouté avec succès']);
    }

    /**
     * Éditer un champ existant
     */
    public function startEditingField($fieldId)
    {
        $field = BulletinStructureField::find($fieldId);
        
        $this->editingFieldId = $fieldId;
        $this->fieldName = $field->field_name;
        $this->fieldLabel = $field->field_label;
        $this->fieldType = $field->field_type;
        $this->coefficient = $field->coefficient;
        $this->minValue = $field->min_value;
        $this->maxValue = $field->max_value;
        $this->calculationFormula = $field->calculation_formula;
        $this->displayOrder = $field->display_order;
    }

    /**
     * Sauvegarder les modifications du champ
     */
    public function updateField()
    {
        $this->validate();

        $field = BulletinStructureField::find($this->editingFieldId);
        $field->update([
            'field_name' => $this->fieldName,
            'field_label' => $this->fieldLabel,
            'field_type' => $this->fieldType,
            'coefficient' => $this->coefficient,
            'min_value' => $this->minValue,
            'max_value' => $this->maxValue,
            'calculation_formula' => $this->calculationFormula,
            'display_order' => $this->displayOrder,
        ]);

        // Enregistrer la modification dans l'historique
        $this->structure->recordRevision(
            null,
            null,
            "Champ '{$this->fieldLabel}' modifié"
        );

        $this->resetForm();
        $this->loadFields();
        $this->dispatch('field-updated', ['message' => 'Champ mis à jour']);
    }

    /**
     * Supprimer un champ
     */
    public function deleteField($fieldId)
    {
        $field = BulletinStructureField::find($fieldId);
        $label = $field->field_label;
        
        $field->delete();
        
        $this->structure->recordRevision(
            null,
            null,
            "Champ '{$label}' supprimé"
        );

        $this->loadFields();
        $this->dispatch('field-deleted', ['message' => 'Champ supprimé']);
    }

    /**
     * Réordonner les champs via drag-drop
     */
    public function reorderFields($orderedIds)
    {
        foreach ($orderedIds as $index => $fieldId) {
            BulletinStructureField::find($fieldId)->update(['display_order' => $index]);
        }

        $this->loadFields();
        $this->structure->recordRevision(null, null, 'Ordre des champs modifié');
        $this->dispatch('fields-reordered', ['message' => 'Ordre mis à jour']);
    }

    /**
     * Toggle visibilité d'un champ
     */
    public function toggleFieldVisibility($fieldId)
    {
        $field = BulletinStructureField::find($fieldId);
        $field->update(['is_visible' => !$field->is_visible]);
        
        $this->loadFields();
        $this->dispatch('field-visibility-changed', ['message' => 'Visibilité mise à jour']);
    }

    /**
     * Toggle éditabilité d'un champ
     */
    public function toggleFieldEditable($fieldId)
    {
        $field = BulletinStructureField::find($fieldId);
        $field->update(['is_editable' => !$field->is_editable]);
        
        $this->loadFields();
        $this->dispatch('field-editability-changed', ['message' => 'Éditabilité mise à jour']);
    }

    /**
     * Annuler et réinitialiser le formulaire
     */
    public function cancelEdit()
    {
        $this->resetForm();
    }

    /**
     * Réinitialiser les champs du formulaire
     */
    private function resetForm()
    {
        $this->editingFieldId = null;
        $this->showAddForm = false;
        $this->fieldName = '';
        $this->fieldLabel = '';
        $this->fieldType = 'subject';
        $this->coefficient = 1;
        $this->minValue = 0;
        $this->maxValue = 20;
        $this->calculationFormula = null;
        $this->displayOrder = 0;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.bulletin-field-editor', [
            'fields' => $this->fields,
            'fieldTypes' => [
                'subject' => 'Matière',
                'coefficient' => 'Coefficient',
                'note' => 'Note',
                'average' => 'Moyenne',
                'rank' => 'Classement',
                'appreciation' => 'Appréciation',
                'custom' => 'Personnalisé',
            ],
        ]);
    }
}
