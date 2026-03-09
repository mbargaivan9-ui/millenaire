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
     * Redirige les utilisateurs authentifiés vers leur dashboard selon leur rôle.
     */
    public function index()
    {
        // ✅ Rediriger les utilisateurs authentifiés vers leur dashboard approprié
        if (Auth::check()) {
            $user = Auth::user();
            $role = $user->role ?? 'student';
            
            // Mappage des rôles aux routes de dashboard
            $roleRoutes = [
                'admin' => 'admin.dashboard',
                'censeur' => 'admin.dashboard',
                'intendant' => 'admin.dashboard',
                'secretaire' => 'admin.dashboard',
                'surveillant' => 'admin.dashboard',
                'professeur' => 'teacher.dashboard',
                'prof_principal' => 'teacher.dashboard',
                'parent' => 'parent.dashboard',
                'student' => 'student.dashboard',
            ];
            
            $route = $roleRoutes[$role] ?? 'home';
            
            // Ne pas créer une boucle infinie si la route est 'home'
            if ($route !== 'home') {
                return redirect()->route($route);
            }
        }
    
        try {
            $settings = EstablishmentSetting::getInstance();
        } catch (\Exception $e) {
            $settings = null;
        }

        // ─── Annonces publiées ──────────────────────────────────────────────
        $announcements = collect();
        try {
            $announcements = Announcement::query()
                ->select('id', 'title', 'content', 'slug', 'published_at')
                ->where('status', 'active')
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
        $teachers = collect();
        try {
            $teachers = Teacher::select('id', 'user_id', 'qualification', 'is_prof_principal', 'is_active')
                ->where('is_active', true)
                ->orderBy('is_prof_principal', 'desc')
                ->paginate(16);
        } catch (\Exception $e) {
            // Silently fail
        }

        return view('public.instructors', compact('teachers'));
    }

    /**
     * Page Corps Administratif.
     */
    public function staff(): \Illuminate\View\View
    {
        $adminRoles = collect();
        try {
            $adminRoles = AdminRole::select('id', 'user_id', 'role_name', 'responsibilities')
                ->orderBy('role_name')
                ->get();
        } catch (\Exception $e) {
            // Silently fail
        }

        return view('public.staff', compact('adminRoles'));
    }

    /**
     * Profil public d'un enseignant.
     */
    public function teacherProfile(int $id): \Illuminate\View\View
    {
        try {
            $teacher = Teacher::select('id', 'user_id', 'qualification', 'is_prof_principal', 'is_active')
                ->where('is_active', true)
                ->findOrFail($id);

            return view('public.teacher-profile', compact('teacher'));
        } catch (\Exception $e) {
            abort(404, 'Teacher not found');
        }
    }
}
