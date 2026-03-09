<?php

namespace App\Services;

use App\Models\{DynamicBulletinStructure, BulletinStructureField, Classe, User, BulletinEntry};
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class DynamicBulletinStructureGenerator
{
    /**
     * Créer une nouvelle structure à partir de données OCR parseE
     */
    public function createFromOCRData(
        Classe $classe,
        array $ocrData,
        User $creator,
        string $sourceFilePath,
        string $sourceType = 'image'
    ): DynamicBulletinStructure {
        
        // Extraire la structure du texte OCR analysé
        $structure = $this->extractStructureFromOCR($ocrData);
        
        // Créer les champs
        $fields = $this->generateFieldsFromStructure($structure);
        
        // Créer l'enregistrement principal
        $bulletinStructure = DynamicBulletinStructure::create([
            'classe_id' => $classe->id,
            'source_file_path' => $sourceFilePath,
            'source_type' => $sourceType,
            'structure' => $structure,
            'metadata' => $this->extractMetadataFromOCR($ocrData),
            'formula_config' => $this->generateDefaultFormulaConfig($structure),
            'column_mapping' => $this->generateColumnMapping($structure),
            'status' => 'draft',
            'created_by' => $creator->id,
        ]);

        // Créer les champs associés
        foreach ($fields as $field) {
            $field['bulletin_dynamic_structure_id'] = $bulletinStructure->id;
            BulletinStructureField::create($field);
        }

        return $bulletinStructure;
    }

    /**
     * Extraire la structure (matières, coefficients) du texte OCR
     */
    private function extractStructureFromOCR(array $ocrData): array
    {
        $structure = [
            'subjects' => [],
            'coefficients' => [],
            'columns' => [],
            'total_columns' => 0,
        ];

        // L'OCR Data contient les lignes du tableau
        // Chercher les lignes avec matières
        if (isset($ocrData['tables'])) {
            foreach ($ocrData['tables'] as $table) {
                $structure = $this->parseTableForSubjects($table, $structure);
            }
        }

        // Fallback: si pas de tables, parser le texte brut
        if (empty($structure['subjects']) && isset($ocrData['text'])) {
            $structure = $this->parseTextForSubjects($ocrData['text'], $structure);
        }

        return $structure;
    }

    /**
     * Parser un tableau OCR pour extraire les matières
     */
    private function parseTableForSubjects(array $table, array $structure): array
    {
        $rows = $table['rows'] ?? [];
        
        foreach ($rows as $rowIndex => $row) {
            // Première colonne est généralement le label (matière)
            $firstCell = $row[0] ?? null;
            
            if ($firstCell && $this->isSubjectName($firstCell)) {
                $subjectName = $this->normalizeSubjectName($firstCell);
                
                // Chercher le coefficient (généralement vers la 3ème colonne)
                $coefficient = $this->extractCoefficientFromRow($row);
                
                $structure['subjects'][$subjectName] = [
                    'label' => $subjectName,
                    'coefficient' => $coefficient ?? 1,
                    'column_count' => count($row) - 2, // Soustraire label et coef
                ];
                
                $structure['columns'][] = ['name' => $subjectName, 'type' => 'subject'];
            }
        }

        $structure['total_columns'] = count($structure['columns']);
        return $structure;
    }

    /**
     * Parser le texte brut pour extraire les matières
     */
    private function parseTextForSubjects(string $text, array $structure): array
    {
        // Listes communes de matières
        $commonSubjects = [
            'français|francais' => 'Français',
            'mathématique|mathematique|maths' => 'Mathématiques',
            'anglais|english' => 'Anglais',
            'science|sciences|svt' => 'Sciences',
            'physique' => 'Physique',
            'chimie' => 'Chimie',
            'histoire|géographie|histoire-géographie' => 'Histoire-Géographie',
            'éducation physique|eps' => 'Education Physique',
            'arts|dessin|musique' => 'Arts',
            'technologie|informatique' => 'Technologie',
        ];

        $lines = explode("\n", $text);
        
        foreach ($lines as $line) {
            $trimmedLine = trim(strtolower($line));
            
            foreach ($commonSubjects as $pattern => $label) {
                if (preg_match("/($pattern)/i", $trimmedLine)) {
                    // Chercher le coefficient sur la même ligne
                    preg_match('/(\d+\.?\d*)\s*(?:coef|coefficient|×|x)?/', $line, $matches);
                    $coefficient = $matches[1] ?? 1;
                    
                    if (!isset($structure['subjects'][$label])) {
                        $structure['subjects'][$label] = [
                            'label' => $label,
                            'coefficient' => (float) $coefficient,
                            'column_count' => 3,
                        ];
                        $structure['columns'][] = ['name' => $label, 'type' => 'subject'];
                    }
                }
            }
        }

        $structure['total_columns'] = count($structure['columns']);
        return $structure;
    }

    /**
     * Vérifier si une chaîne est un nom de matière
     */
    private function isSubjectName(string $text): bool
    {
        $text = strtolower(trim($text));
        
        // Exclure les nombres purs et les labels courts
        if (is_numeric($text) || strlen($text) < 3) {
            return false;
        }

        // Chercher des mots clés de matières
        $subjectKeywords = ['français', 'mathematique', 'anglais', 'science', 'physique', 'histoire', 'eps', 'art', 'technologie', 'informatique', 'francais', 'maths'];
        
        foreach ($subjectKeywords as $keyword) {
            if (strpos($text, $keyword) !== false) {
                return true;
            }
        }

        // Ou si la longueur suggère un nom complet
        return strlen($text) > 5 && !preg_match('/^\d+(\.\d+)?$/', $text);
    }

    /**
     * Normaliser le nom d'une matière
     */
    private function normalizeSubjectName(string $name): string
    {
        return ucfirst(trim(strtolower($name)));
    }

    /**
     * Extraire le coefficient d'une ligne de tableau
     */
    private function extractCoefficientFromRow(array $row): ?float
    {
        // Le coefficient est généralement le 2ème ou 3ème élément
        for ($i = 1; $i < min(3, count($row)); $i++) {
            $cell = $row[$i] ?? null;
            
            if (is_numeric($cell)) {
                return (float) $cell;
            }
        }

        return null;
    }

    /**
     * Extraire les métadonnées (année, trimestre, etc)
     */
    private function extractMetadataFromOCR(array $ocrData): array
    {
        $metadata = [
            'academic_year' => date('Y') . '-' . (date('Y') + 1),
            'extraction_date' => now()->toDateTimeString(),
        ];

        // Chercher l'année scolaire dans le texte
        $text = $ocrData['text'] ?? '';
        if (preg_match('/(\d{4})[\/\-](\d{4})/', $text, $matches)) {
            $metadata['academic_year'] = "{$matches[1]}-{$matches[2]}";
        }

        // Chercher le trimestre
        if (preg_match('/(1er|2ème|3ème|trimestre|trimester|1st|2nd|3rd|term)/i', $text, $matches)) {
            $metadata['term'] = $matches[1];
        }

        // Chercher la période
        if (preg_match('/(semestre|period|périodique)/i', $text)) {
            $metadata['period_type'] = 'semester';
        }

        return $metadata;
    }

    /**
     * Générer les champs à partir de la structure
     */
    private function generateFieldsFromStructure(array $structure): array
    {
        $fields = [];
        $order = 1;

        // Ajouter une colonne pour les appréciations/commentaires
        $fields[] = [
            'field_name' => 'appreciation',
            'field_label' => 'Appréciation',
            'field_type' => 'appreciation',
            'column_index' => 0,
            'display_order' => 0,
            'is_required' => false,
            'is_visible' => true,
        ];

        // Ajouter les matières
        foreach ($structure['subjects'] as $subjectKey => $subjectData) {
            $fields[] = [
                'field_name' => Str::slug($subjectKey),
                'field_label' => $subjectData['label'],
                'field_type' => 'subject',
                'column_index' => $order,
                'display_order' => $order,
                'coefficient' => (float) Arr::get($subjectData, 'coefficient', 1),
                'min_value' => 0,
                'max_value' => 20,
                'calculation_formula' => null,
                'is_required' => true,
                'is_visible' => true,
            ];
            $order++;
        }

        // Ajouter les colonnes calculées (moyenne, rang, etc)
        $fields[] = [
            'field_name' => 'average',
            'field_label' => 'Moyenne',
            'field_type' => 'average',
            'column_index' => $order,
            'display_order' => $order,
            'calculation_formula' => 'weighted_average',
            'min_value' => 0,
            'max_value' => 20,
            'is_required' => true,
            'is_visible' => true,
        ];
        $order++;

        $fields[] = [
            'field_name' => 'rank',
            'field_label' => 'Rang',
            'field_type' => 'rank',
            'column_index' => $order,
            'display_order' => $order,
            'calculation_formula' => 'rank_in_class',
            'is_required' => false,
            'is_visible' => true,
        ];

        return $fields;
    }

    /**
     * Générer la configuration par défaut des formules
     */
    private function generateDefaultFormulaConfig(array $structure): array
    {
        return [
            'average_formula' => 'weighted_average',
            'weights' => Arr::pluck($structure['subjects'], 'coefficient'),
            'min_score' => 0,
            'max_score' => 20,
            'rounding' => 'half_up',
            'decimal_places' => 2,
        ];
    }

    /**
     * Générer le mappage des colonnes
     */
    private function generateColumnMapping(array $structure): array
    {
        $mapping = [];
        $colIndex = 0;

        foreach (Arr::get($structure, 'subjects', []) as $subjectKey => $subject) {
            $mapping[Str::slug($subjectKey)] = [
                'ocr_column' => $colIndex,
                'field_name' => Str::slug($subjectKey),
                'coefficient' => $subject['coefficient'] ?? 1,
            ];
            $colIndex++;
        }

        return $mapping;
    }

    /**
     * Valider une structure (vérifier que les données sont cohérentes)
     */
    public function validate(DynamicBulletinStructure $structure, User $validator, ?string $notes = null): bool
    {
        // Vérifier qu'il y a au moins une matière
        $subjects = $structure->fields()->where('field_type', 'subject')->count();
        if ($subjects < 2) {
            throw new \Exception('Une structure doit contenir au moins 2 matières');
        }

        // Marquer comme validée
        return $structure->validate($validator, $notes);
    }

    /**
     * Appliquer une structure à tous les bulletins d'une classe
     */
    public function applyToClassBulletins(DynamicBulletinStructure $structure): int
    {
        $count = 0;

        // Récupérer tous les bulletins de la classe
        $bulletins = BulletinEntry::whereHas('student', function ($q) {
            $q->where('classe_id', $structure->classe_id);
        })->get();

        foreach ($bulletins as $bulletin) {
            try {
                $structure->applyToBulletinEntry($bulletin);
                $count++;
            } catch (\Exception $e) {
                \Log::error("Error applying structure to bulletin {$bulletin->id}: {$e->getMessage()}");
            }
        }

        return $count;
    }
}
