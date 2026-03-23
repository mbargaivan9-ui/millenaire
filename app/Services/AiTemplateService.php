<?php

namespace App\Services\Bulletin;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * AiTemplateService
 *
 * Analyse un bulletin scolaire (image/PDF converti en image)
 * via l'API Claude Vision (claude-opus-4-5) et extrait :
 *  - La liste des matières avec coefficients
 *  - Le layout (portrait/paysage, nb colonnes)
 *  - Les positions relatives des champs
 *
 * Génère ensuite un template HTML/CSS reproduisant visuellement le bulletin.
 */
class AiTemplateService
{
    private string $apiKey;
    private string $model = 'claude-opus-4-5';

    public function __construct()
    {
        $this->apiKey = config('services.anthropic.api_key', env('ANTHROPIC_API_KEY', ''));
    }

    /**
     * Analyse une image de bulletin et retourne la structure JSON extraite.
     *
     * @param  string $imagePath  Chemin absolu vers l'image (JPEG/PNG)
     * @return array              Structure JSON : subjects, layout, fields, columns
     * @throws \RuntimeException  Si l'API échoue
     */
    public function analyzeBulletinImage(string $imagePath): array
    {
        $imageData  = base64_encode(file_get_contents($imagePath));
        $mimeType   = mime_content_type($imagePath);

        $prompt = $this->buildAnalysisPrompt();

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(60)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => 4096,
            'messages'   => [[
                'role'    => 'user',
                'content' => [
                    [
                        'type'   => 'image',
                        'source' => [
                            'type'       => 'base64',
                            'media_type' => $mimeType,
                            'data'       => $imageData,
                        ],
                    ],
                    ['type' => 'text', 'text' => $prompt],
                ],
            ]],
        ]);

        if (!$response->successful()) {
            Log::error('AiTemplateService: API error', ['status' => $response->status(), 'body' => $response->body()]);
            throw new \RuntimeException('Échec de l\'analyse IA : ' . $response->status());
        }

        $text = $response->json('content.0.text', '');
        return $this->parseJsonFromResponse($text);
    }

    /**
     * Génère le template HTML/CSS à partir de la structure JSON extraite.
     * Le HTML produit reproduit visuellement le bulletin et supporte
     * les variables dynamiques {{student.name}}, {{grade.math}}, etc.
     *
     * @param  array  $structure  JSON retourné par analyzeBulletinImage()
     * @param  string $layout     portrait | landscape
     * @return string             HTML/CSS complet du template
     */
    public function generateTemplateHtml(array $structure, string $layout = 'portrait'): string
    {
        $prompt = $this->buildHtmlGenerationPrompt($structure, $layout);

        $response = Http::withHeaders([
            'x-api-key'         => $this->apiKey,
            'anthropic-version' => '2023-06-01',
            'content-type'      => 'application/json',
        ])->timeout(90)->post('https://api.anthropic.com/v1/messages', [
            'model'      => $this->model,
            'max_tokens' => 8192,
            'messages'   => [[
                'role'    => 'user',
                'content' => $prompt,
            ]],
        ]);

        if (!$response->successful()) {
            throw new \RuntimeException('Échec génération HTML IA : ' . $response->status());
        }

        $html = $response->json('content.0.text', '');

        // Extraire uniquement le HTML si encapsulé dans des balises markdown
        if (preg_match('/```html\s*([\s\S]*?)\s*```/i', $html, $matches)) {
            $html = $matches[1];
        }

        return $html;
    }

    /**
     * Suggère une appréciation pour une note donnée via l'IA légère (claude-haiku).
     * Résultat mis en cache pour éviter les appels redondants.
     *
     * @param  float  $grade       Note sur 20
     * @param  string $subjectName Nom de la matière
     * @param  string $studentName Prénom de l'élève
     * @return string              Suggestion d'appréciation
     */
    public function suggestAppreciation(float $grade, string $subjectName, string $studentName): string
    {
        $cacheKey = "ai_appreciation_" . md5("{$grade}_{$subjectName}");

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($grade, $subjectName, $studentName) {
            $response = Http::withHeaders([
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => '2023-06-01',
                'content-type'      => 'application/json',
            ])->timeout(15)->post('https://api.anthropic.com/v1/messages', [
                'model'      => 'claude-haiku-4-5-20251001',
                'max_tokens' => 100,
                'system'     => 'Tu es un professeur. Donne UNE SEULE appréciation courte (max 10 mots) en français pour un élève. Réponds UNIQUEMENT par l\'appréciation, rien d\'autre.',
                'messages'   => [[
                    'role'    => 'user',
                    'content' => "Note: {$grade}/20 en {$subjectName} pour {$studentName}. Appréciation:",
                ]],
            ]);

            if (!$response->successful()) {
                return '';
            }

            return trim($response->json('content.0.text', ''));
        });
    }

    // ─── Prompts ──────────────────────────────────────────────────────────────

    private function buildAnalysisPrompt(): string
    {
        return <<<PROMPT
Analyse ce bulletin scolaire et extrait UNIQUEMENT un objet JSON valide avec cette structure exacte :

{
  "layout": "portrait",
  "columns_count": 1,
  "school_name_detected": true,
  "has_logo_zone": true,
  "subjects": [
    {
      "name": "Mathématiques",
      "coefficient": 4,
      "has_appreciation_column": true,
      "has_exam1_column": false,
      "has_exam2_column": false
    }
  ],
  "header_fields": ["school_name", "student_name", "class_name", "period", "academic_year"],
  "footer_fields": ["principal_signature", "admin_comment", "rank", "class_average"],
  "color_scheme": {
    "primary": "#003366",
    "secondary": "#FFFFFF",
    "accent": "#FFD700"
  },
  "font_style": "serif"
}

Règles :
- Extrais TOUTES les matières visibles
- Détecte le coefficient de chaque matière (colonne dédiée ou implicite)
- Identifie les champs d'en-tête et de pied de page
- Réponds UNIQUEMENT avec le JSON, sans texte avant ni après
PROMPT;
    }

    private function buildHtmlGenerationPrompt(array $structure, string $layout): string
    {
        $structureJson = json_encode($structure, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $subjects = collect($structure['subjects'] ?? [])->map(fn($s) => $s['name'])->join(', ');
        $colors = $structure['color_scheme'] ?? ['primary' => '#003366', 'secondary' => '#FFFFFF', 'accent' => '#FFD700'];

        return <<<PROMPT
Génère un template HTML/CSS COMPLET pour un bulletin scolaire basé sur cette structure :

{$structureJson}

EXIGENCES STRICTES :
1. Reproduis FIDÈLEMENT le style visuel (couleurs: primaire={$colors['primary']}, accent={$colors['accent']})
2. Chaque champ doit utiliser des variables : {{student.first_name}}, {{student.last_name}}, {{student.matricule}}, {{class.name}}, {{term}}, {{academic_year}}, {{bulletin.rank}}, {{bulletin.class_average}}, {{bulletin.student_average}}, {{bulletin.appreciation}}
3. Pour chaque matière ({$subjects}), utilise : {{grade.NOM_MATIERE}}, {{appreciation.NOM_MATIERE}}, {{coefficient.NOM_MATIERE}} (remplacer espaces par underscore, mettre en minuscules)
4. CSS @media print optimisé (page-break, no-background)
5. Responsive mobile-friendly
6. Table des notes avec colonnes : Matière | Coeff | Note | /20 | Appréciation
7. Header : logo, nom établissement, titre "BULLETIN DE NOTES", élève, classe, période
8. Footer : observation, signature, moyennes, rang

Produit du HTML/CSS complet, avec le style inline dans une balise <style>, prêt à l'emploi.
PROMPT;
    }

    // ─── Parsing ──────────────────────────────────────────────────────────────

    private function parseJsonFromResponse(string $text): array
    {
        // Tenter d'extraire JSON direct
        if (preg_match('/\{[\s\S]*\}/m', $text, $matches)) {
            $decoded = json_decode($matches[0], true);
            if (json_last_error() === JSON_ERROR_NONE) {
                return $decoded;
            }
        }

        Log::warning('AiTemplateService: Could not parse JSON from response', ['text' => substr($text, 0, 500)]);

        // Fallback : structure minimale
        return [
            'layout'        => 'portrait',
            'columns_count' => 1,
            'subjects'      => [],
            'header_fields' => ['school_name', 'student_name', 'class_name', 'period'],
            'footer_fields' => ['rank', 'class_average', 'admin_comment'],
            'color_scheme'  => ['primary' => '#003366', 'secondary' => '#FFFFFF', 'accent' => '#FFD700'],
            'font_style'    => 'serif',
        ];
    }
}
