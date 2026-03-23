<?php

namespace App\Services;

use Anthropic\Client;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Exception;

/**
 * ClaudeHaikuService
 *
 * Service for communicating with Claude Haiku API (Anthropic)
 * Handles:
 * 1. Bulletin structure analysis (OCR text + image)
 * 2. Template JSON generation
 * 3. HTML/CSS template generation
 * 4. Student bulletin generation
 *
 * @package App\Services
 */
class ClaudeHaikuService
{
    private Client $anthropic;
    private string $model;
    private int $maxTokens;
    private float $temperature;
    private int $timeout;
    private int $retryAttempts;

    public function __construct()
    {
        $this->model = config('anthropic.model', 'claude-haiku-4-5-20241022');
        $this->maxTokens = config('anthropic.max_tokens', 8000);
        $this->temperature = config('anthropic.temperature', 0.1);
        $this->timeout = config('anthropic.timeout', 60);
        $this->retryAttempts = config('anthropic.retry_attempts', 3);
        
        $apiKey = config('anthropic.api_key');
        if (!$apiKey) {
            throw new Exception('Missing ANTHROPIC_API_KEY in environment');
        }
        
        $this->anthropic = new Client(apiKey: $apiKey);
        
        Log::info('ClaudeHaikuService initialized', [
            'model' => $this->model,
            'max_tokens' => $this->maxTokens,
        ]);
    }

    /**
     * Analyze OCR result and generate template structure JSON
     *
     * @param array $ocrResult From BulletinScanService
     * @param string $imageBase64 Optional: base64 encoded image for vision analysis
     * @return array Template structure
     */
    public function analyzeAndGenerateTemplate(array $ocrResult, ?string $imageBase64 = null): array
    {
        try {
            $systemPrompt = $this->getSystemPrompt();
            $userPrompt = $this->getUserPromptForAnalysis($ocrResult, $imageBase64);
            
            $response = $this->callClaudeWithRetry($systemPrompt, $userPrompt);
            
            Log::info('Claude template analysis successful', [
                'response_length' => strlen($response),
                'confidence' => $ocrResult['confidence_score'],
            ]);
            
            // Extract JSON from response
            $templateStructure = $this->extractJsonFromResponse($response);
            
            // Validate schema
            $this->validateTemplateSchema($templateStructure);
            
            return [
                'status' => 'success',
                'structure' => $templateStructure,
                'raw_response' => $response,
            ];
            
        } catch (Exception $e) {
            Log::error('Template analysis failed', [
                'error' => $e->getMessage(),
                'ocr_confidence' => $ocrResult['confidence_score'] ?? 'unknown',
            ]);
            
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
                'error_code' => 'CLAUDE_ANALYSIS_FAILED',
            ];
        }
    }

    /**
     * Generate the editor component HTML/Blade from validated template structure
     *
     * @param array $templateStructure
     * @return string Blade HTML component
     */
    public function generateEditorComponent(array $templateStructure): string
    {
        try {
            $systemPrompt = $this->getEditorComponentSystemPrompt();
            $userPrompt = $this->getUserPromptForEditor($templateStructure);
            
            $response = $this->callClaudeWithRetry($systemPrompt, $userPrompt);
            
            Log::info('Claude editor component generated', [
                'structure_subjects' => count($templateStructure['subjects'] ?? []),
            ]);
            
            return $response;
            
        } catch (Exception $e) {
            Log::error('Editor component generation failed', [
                'error' => $e->getMessage(),
            ]);
            
            throw $e;
        }
    }

    /**
     * Generate individual student bulletin HTML
     *
     * System prompt for template structure analysis
     */
    private function getSystemPrompt(): string
    {
        return <<<'PROMPT'
Tu es un expert en analyse de documents scolaires et en conception de templates HTML/CSS.

Tu recevras :
1. Le texte brut extrait par Tesseract OCR d'une image de bulletin scolaire.
2. Optionnellement, l'image encodée en base64 du bulletin original.

Ta mission :
A) ANALYSER la structure du bulletin :
   - Identifier l'en-tête (nom établissement, logo, année scolaire)
   - Détecter les informations élève (nom, prénom, classe, matricule)
   - Extraire toutes les matières avec leurs coefficients, notes et colonnes
   - Repérer les zones de calcul (moyenne, rang, appréciation, signature)
   - Identifier le pied de page (mentions, cachet, signatures)

B) GÉNÉRER un objet JSON structuré représentant le template clone :
   Format attendu STRICTEMENT : JSON valide, sans texte additionnel.
   Structure requise : { header:{}, student_info:{}, subjects:[], calculations:{}, footer:{}, layout:{}, editability:{} }

C) Respecter STRICTEMENT le schéma JSON fourni ci-après dans la réponse.

IMPORTANT : 
- Répondre UNIQUEMENT avec du JSON valide
- Pas de texte additionnel avant ou après le JSON
- Les tableaux doivent être complètement remplis
- Tous les champs obligatoires doivent être présents
PROMPT;
    }

    /**
     * User prompt for template analysis
     */
    private function getUserPromptForAnalysis(array $ocrResult, ?string $imageBase64 = null): string
    {
        $structuredData = $ocrResult['structured_data'] ?? [];
        
        $prompt = <<<PROMPT
Voici les données OCR extraites d'une image de bulletin scolaire :

=== TEXTE OCR BRUT ===
{$ocrResult['raw_text']}

=== DONNÉES ANALYSÉES ===
Score de confiance OCR : {$ocrResult['confidence_score']}%
Total de lignes traitées : {$structuredData['total_lines_processed']}
Motifs détectés : {$this->formatDetectedPatterns($structuredData['identified_patterns'] ?? [])}

=== INSTRUCTIONS ===
1. Analyse la structure complète du bulletin
2. Génère le JSON de template selon le schéma fourni
3. Si confiance OCR < 70%, indique les zones d'incertitude dans le JSON via des champs "uncertain": true
4. Assume une structure Camerounaise standard si ambiguïtés

Génère UNIQUEMENT le JSON de template.
PROMPT;

        if ($imageBase64) {
            $prompt .= "\n\n=== IMAGE ORIGINAL (BASE64) ===\nImage encodée disponible pour analyse visuelle.";
        }
        
        return $prompt;
    }

    /**
     * System prompt for editor component
     */
    private function getEditorComponentSystemPrompt(): string
    {
        return <<<'PROMPT'
Tu es un expert en développement Laravel Blade et Alpine.js.

À partir d'une structure JSON de bulletin validée, génère un composant Blade interactif permettant au professeur principal d'éditer le template avant validation.

Le composant doit supporter :
1. EDITION VISUELLE COMPLÈTE :
   - Glisser-déposer pour réordonner les matières (utilise Alpine.js, pas Sortable.js)
   - Double-clic sur tout texte pour édition inline (contenteditable)
   - Bouton [+Matière] pour ajouter une ligne
   - Bouton [Supprimer] sur chaque matière
   - Champs coefficient éditables directement
   - Prévisualisation en temps réel

2. PANNEAU LATÉRAL :
   - Gestion des colonnes visibles
   - Personnalisation escaldes d'appréciation
   - Configuration des couleurs et polices
   - Upload de logo

3. VALIDATION :
   - Bouton [VALIDER LA STRUCTURE]
   - Validation JSON Schema côté client
   - Envoi POST vers /api/templates/validate

CONTRAINTES :
- Framework JS: Alpine.js v3 UNIQUEMENT
- Styling: Tailwind CSS 3
- Compatible impression CSS via @media print
- Responsive mobile/tablet/desktop
- Pas de dépendances externes complexes
PROMPT;
    }

    /**
     * User prompt for editor generation
     */
    private function getUserPromptForEditor(array $templateStructure): string
    {
        $subjectsJson = json_encode($templateStructure['subjects'] ?? [], JSON_PRETTY_PRINT);
        
        return <<<PROMPT
Génère un composant Blade Laravel pour éditer ce template de bulletin :

JSON du template :
{$subjectsJson}

Le composant doit :
- Être un x-slot Livewire ou un composant Blade simple
- Utiliser Alpine.js pour la réactivité
- Afficher une prévisualisation WYSIWYG du bulletin
- Permettre toutes les modifications listées dans les instructions système

Génère le code Blade/HTML complet, prêt à intégrer dans une vue Laravel.
PROMPT;
    }

    /**
     * Call Claude API with retry logic
     */
    private function callClaudeWithRetry(string $systemPrompt, string $userPrompt): string
    {
        $lastException = null;
        
        for ($attempt = 1; $attempt <= $this->retryAttempts; $attempt++) {
            try {
                Log::info('Calling Claude API', [
                    'attempt' => $attempt,
                    'model' => $this->model,
                ]);
                
                $response = $this->anthropic->messages->create(
                    model: $this->model,
                    max_tokens: $this->maxTokens,
                    system: $systemPrompt,
                    messages: [
                        [
                            'role' => 'user',
                            'content' => $userPrompt,
                        ],
                    ],
                    temperature: $this->temperature,
                );
                
                return $response->content[0]->text;
                
            } catch (Exception $e) {
                $lastException = $e;
                Log::warning('Claude API call failed', [
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);
                
                if ($attempt < $this->retryAttempts) {
                    $backoffSeconds = pow(2, $attempt - 1);
                    Log::info('Retrying after backoff', ['seconds' => $backoffSeconds]);
                    sleep($backoffSeconds);
                }
            }
        }
        
        throw $lastException ?? new Exception('Claude API call failed after retries');
    }

    /**
     * Extract JSON from Claude response (handles markdown code blocks)
     */
    private function extractJsonFromResponse(string $response): array
    {
        // Try direct JSON first
        try {
            return json_decode($response, true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $e) {
            Log::info('Direct JSON parse failed, trying markdown extraction');
        }
        
        // Extract from markdown code blocks
        if (preg_match('/```(?:json)?\s*\n?(.*?)\n?```/s', $response, $matches)) {
            try {
                return json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
            } catch (Exception $e) {
                Log::error('Markdown JSON extraction failed', [
                    'error' => $e->getMessage(),
                ]);
            }
        }
        
        throw new Exception('Could not extract valid JSON from Claude response');
    }

    /**
     * Validate template against required schema
     */
    private function validateTemplateSchema(array $template): void
    {
        $required = ['header', 'student_info', 'subjects', 'calculations', 'footer', 'layout', 'editability'];
        
        foreach ($required as $field) {
            if (!array_key_exists($field, $template)) {
                throw new Exception("Missing required field in template: {$field}");
            }
        }
        
        if (!is_array($template['subjects']) || empty($template['subjects'])) {
            throw new Exception('Template must contain at least one subject');
        }
    }

    /**
     * Format detected patterns for display
     */
    private function formatDetectedPatterns(array $patterns): string
    {
        $detected = array_filter($patterns, fn($v) => $v === true);
        return implode(', ', array_keys($detected)) ?: 'Aucun';
    }
}
