<?php

/**
 * HomeController — Contrôleur de la Page d'Accueil Publique
 *
 * Phase 2 — Interface Publique Principale
 * Charge toutes les données dynamiques pour la page d'accueil Millénaire Connect.
 * Adapté du template Learner avec les couleurs de la plateforme (#0d9488).
 *
 * @package App\Http\Controllers
 */

namespace App\Http\Controllers;

use App\Models\Announcement;
use App\Models\EstablishmentSetting;
use App\Models\Teacher;
use App\Models\Student;
use App\Models\User;
use App\Models\AdminRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Affiche la page d'accueil publique.
     * Charge les annonces publiées, les enseignants actifs et les statistiques globales.
     * Les utilisateurs authentifiés peuvent accéder à cette page sans être redirigés.
     */
    public function index()
    {
        try {
            $settings = EstablishmentSetting::getInstance();
        } catch (\Exception $e) {
            $settings = null;
        }

        // ─── Annonces publiées ──────────────────────────────────────────────
        $announcements = collect();
        try {
            $announcements = Announcement::query()
                ->select('id', 'title', 'content', 'slug', 'published_at', 'cover_image', 'attached_file', 'category')
                ->where('is_published', true)
                ->where(function ($q) {
                    $q->whereNull('published_at')
                      ->orWhere('published_at', '<=', now());
                })
                ->latest('published_at')
                ->take(6)
                ->get();
        } catch (\Exception $e) {
            // Silently fail if announcements fail
        }

        // ─── Enseignants actifs (with user name & subjects) ────────────────────────────
        $teachers = collect();
        try {
            $teachers = Teacher::with(['user:id,name,email', 'subjects:id,name'])
                ->select('id', 'user_id', 'qualification', 'is_prof_principal', 'is_active')
                ->where('is_active', true)
                ->limit(8)
                ->get();
        } catch (\Exception $e) {
            // Silently fail if teachers fail
        }

        // ─── Témoignages (si modèle Testimonial existe) ─────────────────────
        $testimonials = collect();
        try {
            if (class_exists(\App\Models\Testimonial::class)) {
                $testimonials = \App\Models\Testimonial::select('id', 'name', 'role', 'content', 'photo', 'sort_order')
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get();
            }
        } catch (\Exception $e) {
            // Silently fail if testimonials fail
        }

        // ─── Statistiques globales ──────────────────────────────────────────
        $stats = [
            'students'  => 0,
            'teachers'  => 0,
            'classes'   => 0,
            'bulletins' => 0,
        ];
        
        try {
            $stats = [
                'students'  => Student::count(),
                'teachers'  => Teacher::where('is_active', true)->count(),
                'classes'   => \App\Models\Classe::count(),
                'bulletins' => 0,
            ];
        } catch (\Exception $e) {
            // Use default stats if query fails
        }

        // ─── Images du Carousel ──────────────────────────────────────────────
        $carouselImages = [];
        if ($settings && !empty($settings->carousel_images)) {
            $carouselImages = is_array($settings->carousel_images) 
                ? $settings->carousel_images 
                : json_decode($settings->carousel_images, true) ?? [];
        }

        return view('public.home', compact('settings', 'announcements', 'teachers', 'testimonials', 'stats', 'carouselImages'));
    }

    /**
     * Page À Propos.
     */
    public function about()
    {
        try {
            $settings = EstablishmentSetting::getInstance();
        } catch (\Exception $e) {
            $settings = null;
        }

        $stats = [
            'students' => 0,
            'teachers' => 0,
            'classes'  => 0,
        ];

        try {
            $stats = [
                'students' => Student::count(),
                'teachers' => Teacher::where('is_active', true)->count(),
                'classes'  => \App\Models\Classe::count(),
            ];
        } catch (\Exception $e) {
            // Use default stats
        }

        return view('public.about', compact('settings', 'stats'));
    }

    /**
     * Page Enseignants complète.
     */
    public function instructors(): \Illuminate\View\View
    {
        try {
            $settings = EstablishmentSetting::getInstance();
        } catch (\Exception $e) {
            $settings = null;
        }

        $teachers = collect();
        try {
            $teachers = Teacher::select('id', 'user_id', 'qualification', 'is_prof_principal', 'is_active')
                ->where('is_active', true)
                ->orderBy('is_prof_principal', 'desc')
                ->paginate(16);
        } catch (\Exception $e) {
            // Silently fail
        }

        return view('public.instructors', compact('teachers', 'settings'));
    }

    /**
     * Page Corps Administratif.
     */
    public function staff(): \Illuminate\View\View
    {
        try {
            $settings = EstablishmentSetting::getInstance();
        } catch (\Exception $e) {
            $settings = null;
        }

        $adminRoles = collect();
        try {
            $adminRoles = AdminRole::select('id', 'user_id', 'role_name', 'responsibilities')
                ->orderBy('role_name')
                ->get();
        } catch (\Exception $e) {
            // Silently fail
        }

        return view('public.staff', compact('adminRoles', 'settings'));
    }

    /**
     * Profil public d'un enseignant.
     */
    public function teacherProfile(int $id): \Illuminate\View\View
    {
        try {
            $settings = EstablishmentSetting::getInstance();
        } catch (\Exception $e) {
            $settings = null;
        }

        try {
            $teacher = Teacher::select('id', 'user_id', 'qualification', 'is_prof_principal', 'is_active')
                ->where('is_active', true)
                ->findOrFail($id);

            return view('public.teacher-profile', compact('teacher', 'settings'));
        } catch (\Exception $e) {
            abort(404, 'Teacher not found');
        }
    }
}
