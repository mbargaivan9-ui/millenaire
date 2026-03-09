<?php

namespace App\Services;

/**
 * Service pour analyser les structures de bulletins depuis OCR
 * Parse le texte OCR et extrait:
 * - Les matières
 * - Les coefficients
 * - Les formules de calcul
 * - Les règles d'appréciation
 */
class BulletinStructureParserService
{
    /**
     * Parser la structure du bulletin à partir du texte OCR
     *
     * @param string $text Texte extracté par OCR
     * @param array $tables Tables détectées par OCR
     * @return array Structure détectée
     */
    public function parseStructure(string $text, array $tables = []): array
    {
        $structure = [
            'subjects' => $this->extractSubjects($text, $tables),
            'coefficients' => $this->extractCoefficients($text, $tables),
            'grading_scale' => $this->detectGradingScale($text),
            'appreciation_rules' => $this->extractAppreciationRules($text),
        ];

        return $structure;
    }

    /**
     * Parser les matières du bulletin
     *
     * @param string $text
     * @param array $tables
     * @return array
     */
    private function extractSubjects(string $text, array $tables = []): array
    {
        $subjects = [];

        // Si des tables ont été détectées par OCR
        if (!empty($tables)) {
            foreach ($tables as $table) {
                if (isset($table['rows']) && count($table['rows']) > 0) {
                    // La première colonne contient généralement les matières
                    $firstColumn = array_column($table['rows'], 0);
                    
                    foreach ($firstColumn as $cell) {
                        $cell = trim($cell);
                        // Ignorer les headers communs
                        if ($cell && 
                            !in_array(strtolower($cell), ['matière', 'subject', 'note', 'coefficient', 'moyenne', 'rang', 'appreciation']) &&
                            strlen($cell) > 2) {
                            $subjects[] = $cell;
                        }
                    }
                }
            }
        }

        // Si aucune table, extraire du texte brut
        if (empty($subjects)) {
            $subjects = $this->extractSubjectsFromText($text);
        }

        // Dédupliquer et nettoyer
        $subjects = array_unique(array_filter($subjects, fn($s) => strlen(trim($s)) > 2));
        
        return array_values($subjects);
    }

    /**
     * Extraire les matières du texte brut
     *
     * @param string $text
     * @return array
     */
    private function extractSubjectsFromText(string $text): array
    {
        $subjects = [];
        
        // Mots-clés communs de matières scolaires (français/anglais)
        $keywords = [
            'français', 'french',
            'mathématiques', 'maths', 'mathematics',
            'anglais', 'english',
            'sciences', 'science',
            'histoire', 'history',
            'géographie', 'geography',
            'sciences naturelles', 'biology',
            'physique', 'physics',
            'chimie', 'chemistry',
            'éducation physique', 'pe', 'physical education',
            'informatique', 'computer science', 'ict',
            'philosophie', 'philosophy',
            'littérature', 'literature',
            'grammaire', 'grammar',
            'vocabulaire', 'vocabulary',
            'orthographe', 'spelling',
            'calcul', 'arithmetic',
            'géométrie', 'geometry',
            'algèbre', 'algebra',
            'biologie', 'biology',
            'éducation civique', 'civics',
            'musique', 'music',
            'arts plastiques', 'visual arts',
            'technologie', 'technology',
            'économie', 'economics',
            'droit', 'law',
        ];

        $lines = explode("\n", $text);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) < 3) continue;
            
            foreach ($keywords as $keyword) {
                if (stripos($line, $keyword) !== false) {
                    // Essayer d'extraire juste le nom de la matière
                    $subject = preg_replace('/\s*\d+[\.,\d]*\s*$/', '', $line);
                    $subject = preg_replace('/[\[\(].*?[\]\)]/', '', $subject);
                    $subject = trim($subject);
                    
                    if (strlen($subject) > 2 && !in_array($subject, $subjects)) {
                        $subjects[] = $subject;
                    }
                    break;
                }
            }
        }

        return $subjects;
    }

    /**
     * Extraire les coefficients
     *
     * @param string $text
     * @param array $tables
     * @return array
     */
    private function extractCoefficients(string $text, array $tables = []): array
    {
        $subjects = $this->extractSubjects($text, $tables);
        $coefficients = [];

        // Si des tables ont été détectées
        if (!empty($tables)) {
            foreach ($tables as $table) {
                if (isset($table['rows']) && count($table['rows']) > 0) {
                    // Chercher les colonnes "coefficient" ou "coef"
                    $headers = array_map('strtolower', $table['rows'][0] ?? []);
                    $coefIndex = array_search('coefficient', $headers) ?? array_search('coef', $headers) ?? false;
                    
                    if ($coefIndex !== false && isset($table['rows'][1])) {
                        foreach ($table['rows'] as $index => $row) {
                            if ($index === 0) continue; // Skip header
                            
                            $subject = trim($row[0] ?? '');
                            $coef = $row[$coefIndex] ?? 1;
                            $coef = is_numeric($coef) ? $coef : 1;
                            
                            if ($subject && in_array($subject, $subjects)) {
                                $coefficients[$subject] = (float)$coef;
                            }
                        }
                    }
                }
            }
        }

        // Default: tous les coefficients = 1
        foreach ($subjects as $subject) {
            if (!isset($coefficients[$subject])) {
                $coefficients[$subject] = 1;
            }
        }

        return $coefficients;
    }

    /**
     * Détecter l'échelle de notation (0-20, 0-100, A-F, etc.)
     *
     * @param string $text
     * @return array
     */
    private function detectGradingScale(string $text): array
    {
        $lowerText = strtolower($text);
        
        // Détecter le format d'échelle
        if (preg_match('/\b(20|nineteenth|vingt)\b/', $text)) {
            return ['min' => 0, 'max' => 20];
        } elseif (preg_match('/\b(100|cent)\b/', $text)) {
            return ['min' => 0, 'max' => 100];
        } elseif (preg_match('/[A-F]/', $text)) {
            return ['min' => 'A', 'max' => 'F', 'type' => 'letter'];
        }

        // Default: 0-20 (système français)
        return ['min' => 0, 'max' => 20];
    }

    /**
     * Extraire les règles d'appréciation
     *
     * @param string $text
     * @return array
     */
    private function extractAppreciationRules(string $text): array
    {
        $rules = [];
        
        // Patterns communs d'appréciation
        $appreciations = [
            'excellent' => ['excellent', 'excellent', 'très bien', 'outstanding'],
            'très_bien' => ['très bien', 'very good', 'très bon'],
            'bien' => ['bien', 'good', 'bon'],
            'assez_bien' => ['assez bien', 'fairly good', 'assez bon'],
            'moyen' => ['moyen', 'average', 'passable'],
            'faible' => ['faible', 'weak', 'poor', 'insuffisant'],
            'très_faible' => ['très faible', 'very weak', 'criticism'],
        ];

        foreach ($appreciations as $level => $patterns) {
            foreach ($patterns as $pattern) {
                if (stripos($text, $pattern) !== false) {
                    $rules[$level] = $pattern;
                    break;
                }
            }
        }

        // Default rules si rien détecté
        if (empty($rules)) {
            $rules = [
                'excellent' => 'Excellent',
                'très_bien' => 'Très bien',
                'bien' => 'Bien',
                'assez_bien' => 'Assez bien',
                'moyen' => 'Moyen',
                'faible' => 'Faible',
            ];
        }

        return $rules;
    }

    /**
     * Parser les règles de calcul à partir du texte OCR
     *
     * @param string $text
     * @param array $structure
     * @return array
     */
    public function parseCalculationRules(string $text, array $structure = []): array
    {
        return [
            'formulas' => [
                'moyenne' => $this->detectMeanFormula($text, $structure),
                'rang' => $this->detectRankFormula($text),
                'appréciation' => $this->detectAppreciationFormula($text),
            ],
            'rounding' => $this->detectRoundingMethod($text),
            'validation_rules' => [
                'min_grade' => 0,
                'max_grade' => 20,
                'allow_decimals' => true,
            ],
            'special_cases' => $this->detectSpecialCases($text),
        ];
    }

    /**
     * Détecter la formule de calcul de moyenne
     *
     * @param string $text
     * @param array $structure
     * @return string
     */
    private function detectMeanFormula(string $text, array $structure = []): string
    {
        // Chercher les patterns de formule
        if (preg_match('/somme.*coeff|weighted.*average/i', $text)) {
            return 'weighted_mean';
        }
        
        // Default: moyenne pondérée
        if (!empty($structure['coefficients'])) {
            $subjects = array_keys($structure['coefficients']);
            if (count($subjects) > 0) {
                $formula = '(';
                foreach ($subjects as $index => $subject) {
                    if ($index > 0) $formula .= ' + ';
                    $formula .= "note_{$subject} * coef_{$subject}";
                }
                $formula .= ') / sum_of_coefficients';
                return $formula;
            }
        }

        return '(sum_of_grades * coefficients) / sum_of_coefficients';
    }

    /**
     * Détecter la formule de calcul du rang
     *
     * @param string $text
     * @return string
     */
    private function detectRankFormula(string $text): string
    {
        if (preg_match('/rang|rank|position/i', $text)) {
            return 'rank_by_average';
        }

        return 'rank_by_average';
    }

    /**
     * Détecter la formule d'appréciation
     *
     * @param string $text
     * @return string
     */
    private function detectAppreciationFormula(string $text): string
    {
        return 'by_average_threshold';
    }

    /**
     * Détecter la méthode d'arrondi
     *
     * @param string $text
     * @return string
     */
    private function detectRoundingMethod(string $text): string
    {
        if (preg_match('/arrondi.*inf|round.*down|floor/i', $text)) {
            return 'floor';
        } elseif (preg_match('/arrondi.*sup|round.*up|ceil/i', $text)) {
            return 'ceil';
        }

        return 'round';
    }

    /**
     * Détecter les cas spéciaux
     *
     * @param string $text
     * @return array
     */
    private function detectSpecialCases(string $text): array
    {
        $cases = [];

        if (preg_match('/absent|zéro|0\b/i', $text)) {
            $cases['handle_absent'] = true;
        }

        if (preg_match('/bonus|malus/i', $text)) {
            $cases['allow_bonus_malus'] = true;
        }

        return $cases;
    }

    /**
     * Nettoyer et normaliser le texte OCR
     *
     * @param string $text
     * @return string
     */
    public function cleanText(string $text): string
    {
        // Supprimer les lignes vides excessives
        $text = preg_replace('/\n{3,}/', "\n\n", $text);
        
        // Normaliser les espaces
        $text = preg_replace('/[ \t]{2,}/', ' ', $text);
        
        return trim($text);
    }
}
