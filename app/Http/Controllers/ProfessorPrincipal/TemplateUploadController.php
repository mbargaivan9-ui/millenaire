<?php

namespace App\Http\Controllers\ProfessorPrincipal;

use App\Http\Controllers\Controller;
use App\Models\BulletinTemplate;
use App\Models\Classe;
use App\Services\BulletinScanService;
use App\Services\ClaudeHaikuService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

/**
 * TemplateUploadController
 *
 * Handle bulletin image upload → OCR → Claude Haiku analysis
 * Creates draft template for editor
 *
 * Routes:
 * POST /prof-principal/templates/upload
 * GET /prof-principal/templates/upload (form)
 */
class TemplateUploadController extends Controller
{
    public function __construct(
        private BulletinScanService $scanService,
        private ClaudeHaikuService $claudeService,
    ) {}

    /**
     * Show bulletin upload form
     */
    public function showUploadForm(Request $request)
    {
        $classrooms = Classe::where('school_id', Auth::user()->school_id)
            ->with('teachers')
            ->orderBy('name')
            ->get();
            
        return view('professor-principal.templates.upload', [
            'classrooms' => $classrooms,
        ]);
    }

    /**
     * Handle bulletin image upload and OCR processing
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function uploadAndProcess(Request $request): JsonResponse
    {
        try {
            // Validate input
            $validated = $request->validate([
                'image' => 'required|file|max:10240|mimes:jpeg,png,pdf',
                'classroom_id' => 'required|exists:classes,id',
                'academic_year' => 'required|string|regex:/^\d{4}-\d{4}$/',
                'trimester' => 'required|in:1,2,3',
            ]);

            Log::info('Bulletin upload started', [
                'user_id' => Auth::id(),
                'file_size' => $request->file('image')->getSize(),
                'classroom_id' => $validated['classroom_id'],
            ]);

            // Step 1: Run OCR scan
            $ocrResult = $this->scanService->processImage($request->file('image'));
            
            if ($ocrResult['status'] === 'error') {
                Log::error('OCR processing failed', ['reason' => $ocrResult['message']]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Échec de l\'analyse OCR: ' . $ocrResult['message'],
                    'error_code' => 'OCR_FAILED',
                ], 400);
            }

            Log::info('OCR scan completed', [
                'confidence' => $ocrResult['confidence_score'],
            ]);

            // Step 2: Send to Claude Haiku for analysis
            $imageBase64 = base64_encode(file_get_contents($ocrResult['original_image_path']));
            
            $claudeResult = $this->claudeService->analyzeAndGenerateTemplate(
                $ocrResult,
                $imageBase64
            );

            if ($claudeResult['status'] === 'error') {
                Log::error('Claude analysis failed', ['reason' => $claudeResult['message']]);
                return response()->json([
                    'status' => 'error',
                    'message' => 'Échec de l\'analyse par IA: ' . $claudeResult['message'],
                    'error_code' => 'CLAUDE_FAILED',
                ], 400);
            }

            Log::info('Claude analysis completed');

            // Step 3: Save draft template
            $template = BulletinTemplate::create([
                'school_id' => Auth::user()->school_id,
                'classroom_id' => $validated['classroom_id'],
                'created_by' => Auth::id(),
                'name' => 'Template ' . $validated['academic_year'] . ' T' . $validated['trimester'],
                'academic_year' => $validated['academic_year'],
                'trimester' => $validated['trimester'],
                'structure_json' => json_encode($claudeResult['structure']),
                'html_template' => '', // Will be generated later
                'original_image_path' => $ocrResult['original_image_path'],
                'ocr_confidence_score' => $ocrResult['confidence_score'],
                'is_validated' => false,
            ]);

            Log::info('Draft template created', [
                'template_id' => $template->id,
                'version' => $template->version,
            ]);

            return response()->json([
                'status' => 'success',
                'template_id' => $template->id,
                'ocr_confidence' => $ocrResult['confidence_score'],
                'message' => 'Scan OCR réussi. Veuillez maintenant éditer le template.',
                'next_step' => route('prof-principal.templates.editor', $template),
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation échouée',
                'errors' => $e->errors(),
            ], 422);
            
        } catch (\Exception $e) {
            Log::error('Bulletin upload exception', [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Une erreur interne est survenue',
                'error_code' => 'INTERNAL_ERROR',
            ], 500);
        }
    }

    /**
     * Reprocess image if OCR confidence is low
     */
    public function reprocessImage(Request $request, BulletinTemplate $template): JsonResponse
    {
        try {
            $this->authorize('update', $template);

            if (!Storage::disk('local')->exists($template->original_image_path)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Image originale non trouvée',
                ], 404);
            }

            // Rerun OCR with different settings or manual correction
            $imagePath = Storage::disk('local')->path($template->original_image_path);
            $ocrResult = $this->scanService->processImage(
                new \Illuminate\Http\UploadedFile(
                    $imagePath,
                    basename($imagePath),
                    mime_content_type($imagePath),
                    null,
                    true // test mode
                )
            );

            if ($ocrResult['status'] === 'error') {
                return response()->json(['status' => 'error', 'message' => $ocrResult['message']], 400);
            }

            // Update template with new OCR data
            $template->update([
                'ocr_confidence_score' => $ocrResult['confidence_score'],
            ]);

            return response()->json([
                'status' => 'success',
                'new_confidence' => $ocrResult['confidence_score'],
                'improved' => $ocrResult['confidence_score'] > ($template->ocr_confidence_score ?? 0),
            ]);

        } catch (\Exception $e) {
            Log::error('Image reprocessing failed', ['error' => $e->getMessage()]);
            return response()->json(['status' => 'error', 'message' => 'Retraitement échoué'], 500);
        }
    }
}
