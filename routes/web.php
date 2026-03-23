<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;

use App\Http\Controllers\DiagnosticController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\AnnouncementController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClassController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\GradeController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\ParentController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\CourseMaterialController;
use App\Http\Controllers\DisciplineController;
use App\Http\Controllers\GuardianController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageAttachmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\HealthCheckController;

// Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\TeacherAbsenceController;

// Teacher
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\MarksController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\StudentAbsenceController;
use App\Http\Controllers\Teacher\PrincipalStudentAbsenceController;
use App\Http\Controllers\Teacher\BulletinNgController;

use App\Http\Controllers\Teacher\ParentManagementController;
// Parent
use App\Http\Controllers\Parent\DashboardController as ParentDashboardController;
use App\Http\Controllers\Parent\PaymentController as ParentPaymentController;
use App\Http\Controllers\Parent\ParentMonitoringController;
// Student
use App\Http\Controllers\Student\DashboardController as StudentDashboardController;
use App\Http\Controllers\Student\StudentProgressController;
use App\Http\Controllers\Student\CourseController as StudentCourseController;
// Phase 4
use App\Http\Controllers\Payment\MobileMoneyController;
use App\Http\Controllers\Notification\PushNotificationController;

// ═══════════════════════════════════════
//  PUBLIC ROUTES
// ═══════════════════════════════════════


Route::get('/', [HomeController::class, 'index'])->name('home');

// Health Check Endpoints (Phase 10 - Production)
Route::get('/health', [HealthCheckController::class, 'check'])->name('health.check');
Route::get('/health/detailed', [HealthCheckController::class, 'detailed'])->name('health.detailed')->middleware('auth:sanctum');

// Public announcements
Route::get('/announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
Route::get('/announcements/{slug}', [AnnouncementController::class, 'show'])->name('announcements.show');
Route::get('/announcements/{id}/download', [\App\Http\Controllers\Api\AnnouncementApiController::class, 'downloadAttachment'])->name('announcements.downloadAttachment');

// API Announcements - Phase 11
Route::prefix('/api/announcements')->name('api.announcements.')->group(function () {
    Route::get('/latest', [\App\Http\Controllers\Api\AnnouncementApiController::class, 'latest'])->name('latest');
    Route::get('/featured', [\App\Http\Controllers\Api\AnnouncementApiController::class, 'featured'])->name('featured');
    Route::get('/categories', [\App\Http\Controllers\Api\AnnouncementApiController::class, 'categories'])->name('categories');
    Route::get('/', [\App\Http\Controllers\Api\AnnouncementApiController::class, 'index'])->name('index');
    Route::get('/{id}', [\App\Http\Controllers\Api\AnnouncementApiController::class, 'show'])->name('show');
    Route::get('/{id}/download', [\App\Http\Controllers\Api\AnnouncementApiController::class, 'downloadAttachment'])->name('download');
});

// Public pages
Route::get('/about', [HomeController::class, 'about'])->name('public.about');
Route::get('/instructors', [HomeController::class, 'instructors'])->name('public.instructors');
Route::get('/staff', [HomeController::class, 'staff'])->name('public.staff');

// Public teacher profile
Route::get('/teachers/{id}', [HomeController::class, 'teacherProfile'])->name('public.teacher.profile');

// Payment Webhooks (public — called by payment gateways)
Route::prefix('webhooks')->name('webhooks.')->group(function () {
    Route::post('payment/campay', [PaymentWebhookController::class, 'handleCampayWebhook'])->name('payment.campay');
    Route::post('payment/orange', [PaymentWebhookController::class, 'handleOrangeMoneyWebhook'])->name('payment.orange');
    Route::post('payment/mtn', [PaymentWebhookController::class, 'handleMTNMoneyWebhook'])->name('payment.mtn');
    Route::get('payment/health', [PaymentWebhookController::class, 'webhookHealth'])->name('payment.health');
});

// QR Code Verification (public)
Route::get('verify/receipt/{token}', [MobileMoneyController::class, 'verifyQr'])->name('payment.verify-qr');
Route::get('/offline', fn() => view('pwa.offline'))->name('pwa.offline');

// Push VAPID key (public — needed by JS before auth)
Route::get('push/vapid-key', [PushNotificationController::class, 'vapidKey'])->name('push.vapid-key');

// Language switch (public & authenticated)
Route::get('/lang/switch/{lang}', [LanguageController::class, 'switch'])->name('lang.switch');

// ═══════════════════════════════════════
//  AUTHENTICATION (guests only)
// ═══════════════════════════════════════

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
    Route::get('/forgot-password', [AuthController::class, 'showForgotPasswordForm'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendPasswordResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'showResetPasswordForm'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'resetPassword'])->name('password.update');
});

// ═══════════════════════════════════════
//  AUTHENTICATED ROUTES
// ═══════════════════════════════════════

Route::middleware('auth')->group(function () {

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // ─── Account (all roles) ───
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('profile', [ProfileController::class, 'edit'])->name('profile');
    });

    // ─── Profile (all roles) ───
    Route::prefix('profile')->name('profile.')->group(function () {
        Route::get('edit', [ProfileController::class, 'edit'])->name('edit');
        Route::get('show', [ProfileController::class, 'show'])->name('show');
        Route::put('update', [ProfileController::class, 'update'])->name('update');
        Route::get('security', [ProfileController::class, 'security'])->name('security');
        Route::post('change-password', [ProfileController::class, 'changePassword'])->name('change-password');
        Route::post('upload-avatar', [ProfileController::class, 'uploadAvatar'])->name('upload-avatar');
        Route::post('logout-all', [ProfileController::class, 'logoutAllDevices'])->name('logout-all');
        Route::prefix('2fa')->name('2fa.')->group(function () {
            Route::post('enable', [ProfileController::class, 'enable2FA'])->name('enable');
            Route::post('disable', [ProfileController::class, 'disable2FA'])->name('disable');
        });
    });

    // ─── Chat (all roles) ───
    Route::middleware(['auth', 'throttle:60,1'])->prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [App\Http\Controllers\Chat\ChatController::class, 'index'])
            ->name('index');

        // API: Conversations
        Route::get('/conversations', [App\Http\Controllers\Chat\ChatController::class, 'listConversations'])
            ->name('conversations.list');

        Route::get('/conversations/{conversation}', [App\Http\Controllers\Chat\ChatController::class, 'loadConversation'])
            ->name('conversations.load');

        Route::post('/conversations', [App\Http\Controllers\Chat\ChatController::class, 'createConversation'])
            ->name('conversations.create');

        Route::post('/conversations/{conversation}/read', [App\Http\Controllers\Chat\ChatController::class, 'markAsRead'])
            ->name('conversations.read');

        Route::get('/search', [App\Http\Controllers\Chat\ChatController::class, 'searchConversations'])
            ->name('search');

        // API: Messages
        Route::post('/messages', [App\Http\Controllers\Chat\ChatController::class, 'sendMessage'])
            ->name('messages.send');

        Route::delete('/messages/{message}', [App\Http\Controllers\Chat\ChatController::class, 'deleteMessage'])
            ->name('messages.delete');

        Route::post('/messages/{message}/react', [App\Http\Controllers\Chat\ChatController::class, 'react'])
            ->name('messages.react');

        // API: Polling (fallback sans WebSocket)
        Route::get('/conversations/{conversation}/poll', [App\Http\Controllers\Chat\ChatController::class, 'pollMessages'])
            ->name('conversations.poll');

        // API: Typing indicator
        Route::post('/typing', [App\Http\Controllers\Chat\ChatController::class, 'typing'])
            ->name('typing');

        // API: Utilisateurs disponibles
        Route::get('/users', [App\Http\Controllers\Chat\ChatController::class, 'availableUsers'])
            ->name('users');

        Route::get('/search-users', [App\Http\Controllers\Chat\ChatController::class, 'searchUsers'])
            ->name('search-users');

        // API: Badge non-lu (topbar)
        Route::get('/unread', [App\Http\Controllers\Chat\ChatController::class, 'unreadCount'])
            ->name('unread');

        // Téléchargement de fichier
        Route::get('/attachments/{attachment}/download', [App\Http\Controllers\Chat\ChatController::class, 'downloadAttachment'])
            ->name('attachment.download');
    });

    // ─── Messages & Notifications (all roles) ───
    Route::prefix('messages')->name('messages.')->group(function () {
        Route::get('/', [MessageController::class, 'index'])->name('index');
        Route::get('create', [MessageController::class, 'create'])->name('create');
        Route::post('/', [MessageController::class, 'store'])->name('store');
        Route::get('{message}', [MessageController::class, 'show'])->name('show');
        Route::delete('{message}', [MessageController::class, 'destroy'])->name('destroy');
    });

    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('index');
        Route::get('settings', [NotificationController::class, 'settings'])->name('settings');
        Route::post('settings', [NotificationController::class, 'saveSettings'])->name('save-settings');
        Route::post('{notification}/mark-read', [NotificationController::class, 'markRead'])->name('mark-read');
        Route::post('mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
        Route::delete('{notification}', [NotificationController::class, 'destroy'])->name('destroy');
        Route::get('api/latest', [NotificationController::class, 'getLatest'])->name('api.latest');
        Route::get('api/messages', [NotificationController::class, 'getLatestMessages'])->name('api.messages');
    });

    // ─── Push Notifications ───
    Route::prefix('push')->name('push.')->group(function () {
        Route::post('subscribe', [PushNotificationController::class, 'subscribe'])->name('subscribe');
        Route::post('unsubscribe', [PushNotificationController::class, 'unsubscribe'])->name('unsubscribe');
        Route::get('status', [PushNotificationController::class, 'status'])->name('status');
        Route::post('test', [PushNotificationController::class, 'test'])->middleware('role:admin')->name('test');
    });

    // ─── Mobile Money Payments ───
    Route::prefix('payment')->name('payment.')->group(function () {
        Route::get('mobile-money', [MobileMoneyController::class, 'index'])->name('mobile-money');
        Route::post('mobile-money/initiate', [MobileMoneyController::class, 'initiate'])->name('initiate');
        Route::get('status/{payment}', [MobileMoneyController::class, 'checkStatus'])->name('status');
        Route::get('receipt/{payment}', [MobileMoneyController::class, 'receipt'])->name('receipt');
        Route::get('admin/history', [MobileMoneyController::class, 'adminHistory'])
            ->middleware('role:admin,intendant,censeur')->name('admin-history');
    });

    // CamPay webhook (no session middleware)
    Route::post('payment/webhook/campay', [MobileMoneyController::class, 'webhook'])
        ->name('payment.webhook.campay')
        ->withoutMiddleware(['web']);

    // ════════════════════════════════════════════════
    //  ADMIN & STAFF
    // ════════════════════════════════════════════════
    Route::middleware('role:admin,censeur,intendant,secretaire,surveillant')
        ->prefix('admin')->name('admin.')->group(function () {

        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

        // Users
        Route::resource('users', UserController::class)->except('show');
        Route::post('users/import', [UserController::class, 'import'])->name('users.import');

        // Classes & Subjects
        Route::resource('classes', ClassController::class);
        Route::resource('subjects', SubjectController::class);

        // Students
        Route::resource('students', \App\Http\Controllers\Admin\StudentController::class);
        Route::post('students/bulk-update', [\App\Http\Controllers\Admin\StudentController::class, 'bulkUpdate'])->name('students.bulkUpdate');
        Route::get('students/export/csv', [\App\Http\Controllers\Admin\StudentController::class, 'export'])->name('students.export');

        // Attendance
        Route::resource('attendance', \App\Http\Controllers\Admin\AttendanceController::class);
        Route::post('attendance/bulk-create', [\App\Http\Controllers\Admin\AttendanceController::class, 'bulkCreate'])->name('attendance.bulkCreate');
        Route::get('attendance/report', [\App\Http\Controllers\Admin\AttendanceController::class, 'report'])->name('attendance.report');

        // Teacher Absences
        Route::resource('teacher-absences', \App\Http\Controllers\Admin\TeacherAbsenceController::class);
        Route::post('teacher-absences/{teacherAbsence}/approve', [\App\Http\Controllers\Admin\TeacherAbsenceController::class, 'approve'])->name('teacher_absences.approve');
        Route::post('teacher-absences/{teacherAbsence}/reject', [\App\Http\Controllers\Admin\TeacherAbsenceController::class, 'reject'])->name('teacher_absences.reject');
        Route::get('teacher-absences/report', [\App\Http\Controllers\Admin\TeacherAbsenceController::class, 'report'])->name('teacher_absences.report');
        Route::post('teacher-absences/bulk-create', [\App\Http\Controllers\Admin\TeacherAbsenceController::class, 'bulkCreate'])->name('teacher_absences.bulkCreate');

        // Schedule
        Route::resource('schedule', \App\Http\Controllers\Admin\ScheduleController::class);
        Route::get('schedule/view-class/{classe}', [\App\Http\Controllers\Admin\ScheduleController::class, 'viewClass'])->name('schedule.viewClass');
        Route::get('schedule/view-teacher/{teacher}', [\App\Http\Controllers\Admin\ScheduleController::class, 'viewTeacher'])->name('schedule.viewTeacher');
        Route::get('schedule/export/{classe}', [\App\Http\Controllers\Admin\ScheduleController::class, 'export'])->name('schedule.export');

        // Fees
        Route::get('fees/settings', [\App\Http\Controllers\Admin\FeeController::class, 'settings'])->name('fees.settings');
        Route::post('fees/settings', [\App\Http\Controllers\Admin\FeeController::class, 'updateSettings'])->name('fees.updateSettings');
        Route::post('fees/assign-to-class', [\App\Http\Controllers\Admin\FeeController::class, 'assignToClass'])->name('fees.assignToClass');
        Route::get('fees/report', [\App\Http\Controllers\Admin\FeeController::class, 'report'])->name('fees.report');
        Route::resource('fees', \App\Http\Controllers\Admin\FeeController::class, ['except' => ['show']]);

        // KPI & Reports Export
        Route::prefix('kpi')->name('kpi.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\KpiController::class, 'index'])->name('index');
            Route::get('export/{bulletinConfig}', [\App\Http\Controllers\Admin\KpiController::class, 'export'])->name('export');
            Route::get('export-csv', [\App\Http\Controllers\Admin\KpiController::class, 'exportCsv'])->name('export-csv');
        });

        // Finance
        Route::prefix('finance')->name('finance.')->group(function () {
            Route::get('/', [FinanceController::class, 'index'])->name('index');
            Route::get('treasury-report', [FinanceController::class, 'treasuryReport'])->name('treasury-report');
            Route::get('class/{class}', [FinanceController::class, 'showClass'])->name('class');
            Route::get('student/{student}', [FinanceController::class, 'showStudent'])->name('student');
            Route::get('manage-fees', [FinanceController::class, 'manageFeeSettings'])->name('fee-settings');
            Route::post('update-fee', [FinanceController::class, 'updateClassFee'])->name('update-fee');
            Route::get('student/{student}/invoice', [FinanceController::class, 'generateInvoice'])->name('invoice');
            Route::get('export-class-excel/{class}', [FinanceController::class, 'exportClassExcel'])->name('export-class');
            Route::get('export-school-excel', [FinanceController::class, 'exportSchoolExcel'])->name('export-school');
            Route::prefix('api')->group(function () {
                Route::get('unpaid', [FinanceController::class, 'apiUnpaidStudents'])->name('api-unpaid');
                Route::get('overdue', [FinanceController::class, 'apiOverdueStudents'])->name('api-overdue');
                Route::get('school-report', [FinanceController::class, 'apiSchoolReport'])->name('api-school-report');
                Route::get('treasury-report', [FinanceController::class, 'apiTreasuryReport'])->name('api-treasury-report');
                Route::get('transactions', [FinanceController::class, 'apiTransactions'])->name('api-transactions');
            });
        });

        // Assignments (teacher-class)
        Route::resource('assignments', AssignmentController::class);
        Route::get('assignments/history', [AssignmentController::class, 'history'])->name('assignments.history');
        Route::get('assignments/teacher/{teacher}/history', [AssignmentController::class, 'teacherHistory'])->name('assignments.teacher.history');
        Route::get('assignments/class/{class}/history', [AssignmentController::class, 'classHistory'])->name('assignments.class.history');
        Route::get('assignments/grid/{class?}', [AssignmentController::class, 'grid'])->name('assignments.grid');
        Route::prefix('api/assignments')->name('assignments.api.')->group(function () {
            Route::get('statistics', [AssignmentController::class, 'statistics'])->name('statistics');
            Route::get('available-teachers', [AssignmentController::class, 'availableTeachers'])->name('available-teachers');
            Route::post('add-multiple', [AssignmentController::class, 'addMultiple'])->name('add-multiple');
            Route::post('remove', [AssignmentController::class, 'removeAssignment'])->name('remove');
            Route::get('teacher/{teacher}/classes', [AssignmentController::class, 'getTeacherClasses'])->name('teacher-classes');
            Route::get('class/{class}/teachers', [AssignmentController::class, 'getClassTeachers'])->name('class-teachers');
        });

        // Roles & Permissions
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\AdminRoleController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Admin\AdminRoleController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Admin\AdminRoleController::class, 'store'])->name('store');
            Route::get('{role}', [\App\Http\Controllers\Admin\AdminRoleController::class, 'show'])->name('show');
            Route::get('{role}/edit', [\App\Http\Controllers\Admin\AdminRoleController::class, 'edit'])->name('edit');
            Route::put('{role}', [\App\Http\Controllers\Admin\AdminRoleController::class, 'update'])->name('update');
            Route::delete('{role}', [\App\Http\Controllers\Admin\AdminRoleController::class, 'destroy'])->name('destroy');
        });

        // Announcements
        Route::resource('announcements', \App\Http\Controllers\Admin\AnnouncementController::class);
        Route::patch('announcements/{id}/publish', [\App\Http\Controllers\Admin\AnnouncementController::class, 'publish'])->name('announcements.publish');

        // Settings
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
        Route::get('settings/diagnostic', [\App\Http\Controllers\Admin\DiagnosticSettingsController::class, 'diagnostic'])->name('settings.diagnostic');
        Route::post('settings/carousel', [SettingsController::class, 'updateCarousel'])->name('settings.carousel.store');
        Route::post('settings/exam-results', [SettingsController::class, 'storeExamResult'])->name('settings.exam-results.store');
        Route::put('settings/exam-results/{id}', [SettingsController::class, 'updateExamResult'])->name('settings.exam-results.update');
        Route::delete('settings/exam-results/{id}', [SettingsController::class, 'deleteExamResult'])->name('settings.exam-results.delete');

        // Testimonials
        Route::get('testimonials/create', [SettingsController::class, 'createTestimonial'])->name('testimonials.create');
        Route::post('testimonials', [SettingsController::class, 'storeTestimonial'])->name('testimonials.store');
        Route::get('testimonials/{testimonial}/edit', [SettingsController::class, 'editTestimonial'])->name('testimonials.edit');
        Route::put('testimonials/{testimonial}', [SettingsController::class, 'updateTestimonial'])->name('testimonials.update');
        Route::delete('testimonials/{testimonial}', [SettingsController::class, 'destroyTestimonial'])->name('testimonials.destroy');

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('dashboard', [ReportController::class, 'dashboard'])->name('dashboard');
            Route::get('activity-logs', [ReportController::class, 'activityLogs'])->name('activity-logs');
            Route::get('financial', [ReportController::class, 'financialReport'])->name('financial');
            Route::get('student-performance', [ReportController::class, 'studentPerformance'])->name('student-performance');
            Route::get('attendance', [ReportController::class, 'attendanceReport'])->name('attendance');
            Route::get('export/{reportType}/{format}', [ReportController::class, 'export'])->name('export');
        });

        // Payment history (admin view)
        Route::get('payments/history', [MobileMoneyController::class, 'adminHistory'])->name('payments.history');

       
       

        // Teachers CRUD (admin)
        Route::resource('teachers', \App\Http\Controllers\Admin\TeacherController::class);
        Route::post('teachers/{teacher}/toggle', [\App\Http\Controllers\Admin\TeacherController::class, 'toggleActive'])->name('teachers.toggle');
    });

    // ════════════════════════════════════════════════
    //  TEACHER SPACE
    // ════════════════════════════════════════════════
    Route::middleware(['role:teacher,professeur,prof_principal,admin,censeur', 'ensure.teacher.record'])
        ->prefix('teacher')->name('teacher.')->group(function () {

        Route::get('/', [TeacherDashboardController::class, 'index'])->name('dashboard');

        // Marks & Attendance
        Route::resource('marks', MarksController::class);
        Route::resource('attendance', AttendanceController::class);

        // Grade Entry
        Route::prefix('grades')->name('grades.')->group(function () {
            Route::get('{classSubjectTeacher}', [GradeEntryController::class, 'index'])->name('entry.index');
            Route::post('{classSubjectTeacher}/save', [GradeEntryController::class, 'saveGrade'])->name('entry.save');
            Route::get('{classSubjectTeacher}/student/{student}', [GradeEntryController::class, 'getStudentGrades'])->name('entry.student');
            Route::get('{classSubjectTeacher}/class-stats', [GradeEntryController::class, 'getClassStatistics'])->name('entry.stats');
            Route::get('{classSubjectTeacher}/export', [GradeEntryController::class, 'exportGrades'])->name('entry.export');
        });

        
        Route::get('courses', [TeacherDashboardController::class, 'courses'])->name('courses');
        Route::get('assignments', [TeacherDashboardController::class, 'assignments'])->name('assignments');

        // Quizzes
        Route::prefix('quizzes')->name('quizzes.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Teacher\QuizController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Teacher\QuizController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Teacher\QuizController::class, 'store'])->name('store');
            Route::get('{quiz}/edit', [\App\Http\Controllers\Teacher\QuizController::class, 'edit'])->name('edit');
            Route::put('{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'update'])->name('update');
            Route::delete('{quiz}', [\App\Http\Controllers\Teacher\QuizController::class, 'destroy'])->name('destroy');
            Route::get('{quiz}/questions', [\App\Http\Controllers\Teacher\QuizController::class, 'questions'])->name('questions');
            Route::post('{quiz}/questions', [\App\Http\Controllers\Teacher\QuizController::class, 'storeQuestion'])->name('questions.store');
            Route::get('{quiz}/results', [\App\Http\Controllers\Teacher\QuizController::class, 'results'])->name('results');
            Route::post('{quiz}/publish', [\App\Http\Controllers\Teacher\QuizController::class, 'publish'])->name('publish');
        });

        // Course Materials
        Route::prefix('materials')->name('materials.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Teacher\MaterialController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Teacher\MaterialController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Teacher\MaterialController::class, 'store'])->name('store');
            Route::get('{material}/edit', [\App\Http\Controllers\Teacher\MaterialController::class, 'edit'])->name('edit');
            Route::put('{material}', [\App\Http\Controllers\Teacher\MaterialController::class, 'update'])->name('update');
            Route::delete('{material}', [\App\Http\Controllers\Teacher\MaterialController::class, 'destroy'])->name('destroy');
        });

        // Parent Management
        Route::prefix('parent-management')->name('parent-management.')->group(function () {
            Route::get('/{class?}', [\App\Http\Controllers\Teacher\ParentManagementController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Teacher\ParentManagementController::class, 'store'])->name('store');
            Route::get('/{parent}/edit', [\App\Http\Controllers\Teacher\ParentManagementController::class, 'edit'])->name('edit');
            Route::put('/{parent}', [\App\Http\Controllers\Teacher\ParentManagementController::class, 'update'])->name('update');
            Route::delete('/{parent}', [\App\Http\Controllers\Teacher\ParentManagementController::class, 'destroy'])->name('destroy');
        });

        // Schedule
        Route::get('schedule', [\App\Http\Controllers\Teacher\ScheduleController::class, 'index'])->name('schedule');

        // Student Absences
        Route::prefix('student-absences')->name('student-absences.')->group(function () {
            Route::get('/', [StudentAbsenceController::class, 'index'])->name('index');
            Route::get('create', [StudentAbsenceController::class, 'create'])->name('create');
            Route::post('/', [StudentAbsenceController::class, 'store'])->name('store');
            Route::get('{absence}', [StudentAbsenceController::class, 'show'])->name('show');
            Route::get('{absence}/edit', [StudentAbsenceController::class, 'edit'])->name('edit');
            Route::put('{absence}', [StudentAbsenceController::class, 'update'])->name('update');
            Route::delete('{absence}', [StudentAbsenceController::class, 'destroy'])->name('destroy');
            Route::get('/report', [StudentAbsenceController::class, 'report'])->name('report');
        });

        // Appointments & Availability
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Teacher\AppointmentController::class, 'index'])->name('index');
            Route::patch('{appointment}', [\App\Http\Controllers\Teacher\AppointmentController::class, 'update'])->name('update');
        });
        Route::get('appointments', [\App\Http\Controllers\Teacher\AppointmentController::class, 'index'])->name('appointments');
        Route::prefix('availabilities')->name('availabilities.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Teacher\AvailabilityController::class, 'index'])->name('index');
            Route::post('/', [\App\Http\Controllers\Teacher\AvailabilityController::class, 'store'])->name('store');
            Route::delete('{availability}', [\App\Http\Controllers\Teacher\AvailabilityController::class, 'destroy'])->name('destroy');
        });
        Route::get('availabilities', [\App\Http\Controllers\Teacher\AvailabilityController::class, 'index'])->name('availabilities');

        // ── Bulletin / Grades Dashboard ──
        // Quick access to bulletin/grades for a specific class
        Route::get('bulletin/{class}', [TeacherDashboardController::class, 'bulletinDashboard'])->name('bulletin.dashboard');

        // ── Bulletin NG (Système de génération de bulletins) ──
        // Accessible uniquement aux professeurs principaux et admins
        Route::middleware('role:prof_principal,admin')
            ->prefix('bulletin-ng')->name('bulletin_ng.')->group(function () {
            // Dashboard sessions
            Route::get('/', [BulletinNgController::class, 'index'])->name('index');

            // ── Wizard Étapes (GET)
            Route::get('step1',                  [BulletinNgController::class, 'step1Section'])->name('step1');
            Route::get('step2',                  [BulletinNgController::class, 'step2Config'])->name('step2');
            Route::get('{config}/step3',         [BulletinNgController::class, 'step3Subjects'])->name('step3');
            Route::get('{config}/step4',         [BulletinNgController::class, 'step4Students'])->name('step4');
            Route::get('{config}/step5',         [BulletinNgController::class, 'step5Notes'])->name('step5');
            Route::get('{config}/step6',         [BulletinNgController::class, 'step6Conduite'])->name('step6');
            Route::get('{config}/step7',         [BulletinNgController::class, 'step7Generate'])->name('step7');

            // ── Actions POST (formulaires)
            Route::post('store-config',                  [BulletinNgController::class, 'storeConfig'])->name('store-config');
            Route::post('{config}/store-subjects',       [BulletinNgController::class, 'storeSubjects'])->name('store-subjects');
            Route::post('{config}/finaliser-conduite',   [BulletinNgController::class, 'finaliserConduite'])->name('finaliser-conduite');

            // ── API JSON (AJAX - temps réel)
            Route::post('{config}/students',             [BulletinNgController::class, 'storeStudent'])->name('students.store');
            Route::delete('{config}/students/{student}', [BulletinNgController::class, 'deleteStudent'])->name('students.delete');
            Route::post('{config}/ouvrir-saisie',        [BulletinNgController::class, 'ouvrirSaisie'])->name('ouvrir-saisie');
            Route::post('{config}/save-note',            [BulletinNgController::class, 'saveNote'])->name('save-note');
            Route::post('{config}/verrouiller',          [BulletinNgController::class, 'verrouillerNotes'])->name('verrouiller');
            Route::post('{config}/students/{student}/conduite', [BulletinNgController::class, 'saveConduite'])->name('save-conduite');
            Route::get('{config}/api/stats',             [BulletinNgController::class, 'apiStats'])->name('api.stats');
            Route::get('{config}/students/{student}/notes', [BulletinNgController::class, 'apiStudentNotes'])->name('api.student-notes');

            // ── PDF & Export
            Route::get('{config}/students/{student}/pdf',  [BulletinNgController::class, 'pdfStudent'])->name('pdf.student');
            Route::get('{config}/pdf-all',                 [BulletinNgController::class, 'pdfAll'])->name('pdf.all');
            Route::get('{config}/students/{student}/preview', [BulletinNgController::class, 'previewStudent'])->name('preview.student');
        });
    });

    // ════════════════════════════════════════════════
    //  PARENT SPACE
    // ════════════════════════════════════════════════
    Route::middleware('role:parent')->prefix('parent')->name('parent.')->group(function () {

        Route::get('/', [ParentDashboardController::class, 'index'])->name('dashboard');
        Route::get('children', [ParentDashboardController::class, 'children'])->name('children');
        Route::get('children/{child}/marks', [ParentDashboardController::class, 'childMarks'])->name('child.marks');
        Route::get('children/{child}/attendance', [ParentDashboardController::class, 'childAttendance'])->name('child.attendance');
        Route::get('children/{child}/report-cards', [ParentDashboardController::class, 'childReportCards'])->name('child.report-cards');

        // Monitoring (Phase 3)
        Route::get('monitoring', [ParentMonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('child/{student}/data', [ParentMonitoringController::class, 'getChildData'])->name('child.data');
        Route::get('child/{student}/evolution', [ParentMonitoringController::class, 'getEvolutionChart'])->name('child.evolution');
        Route::post('notifications/read', [ParentMonitoringController::class, 'markNotificationsRead'])->name('notifications.read');

        // Payments
        Route::prefix('payments')->name('payments.')->group(function () {
            Route::get('/', [ParentPaymentController::class, 'index'])->name('index');
            Route::get('mobile-money', [ParentPaymentController::class, 'mobileMoneyIndex'])->name('mobile-money');
            Route::get('receipts', [ParentPaymentController::class, 'receiptsIndex'])->name('receipts');
            Route::post('initiate', [ParentPaymentController::class, 'initiate'])->name('initiate');
            Route::get('{payment}/status', [ParentPaymentController::class, 'checkStatus'])->name('status');
            Route::get('{payment}/receipt', [ParentPaymentController::class, 'receipt'])->name('receipt');
            Route::get('{receipt}/download', [ParentPaymentController::class, 'downloadReceipt'])->name('download');
            Route::get('statistics', [ParentPaymentController::class, 'statistics'])->name('statistics');
        });
        
        // Mobile Money alias routes (same as payments show)
        Route::prefix('mobile-money')->name('mobile-money.')->group(function () {
            Route::get('student/{student}', [ParentPaymentController::class, 'mobileMoneyShow'])->name('show');
        });
        
        // Legacy child payment route
        Route::get('children/{student}/payments', [ParentPaymentController::class, 'show'])->name('child.payments');

        // Appointments (parent side)
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Parent\AppointmentController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Parent\AppointmentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Parent\AppointmentController::class, 'store'])->name('store');
            Route::delete('{appointment}', [\App\Http\Controllers\Parent\AppointmentController::class, 'destroy'])->name('destroy');
        });

       
    });

    // ════════════════════════════════════════════════
    //  STUDENT SPACE
    // ════════════════════════════════════════════════
    Route::middleware('role:student')->prefix('student')->name('student.')->group(function () {

        Route::get('/', [StudentDashboardController::class, 'index'])->name('dashboard');
        Route::get('marks', [StudentDashboardController::class, 'marks'])->name('marks');
        Route::get('attendance', [StudentDashboardController::class, 'attendance'])->name('attendance');
        Route::get('schedule', [StudentDashboardController::class, 'schedule'])->name('schedule');
        Route::get('assignments', [StudentDashboardController::class, 'assignments'])->name('assignments');
        
        Route::get('courses', [StudentCourseController::class, 'index'])->name('courses.index');

        // Progress (Phase 3)
        Route::get('progress', [StudentProgressController::class, 'index'])->name('progress.index');
        Route::get('progress/data', [StudentProgressController::class, 'getProgressData'])->name('progress.data');
        Route::get('progress/chart', [StudentProgressController::class, 'getChartData'])->name('progress.chart');

        // E-Learning
        Route::prefix('e-learning')->name('e-learning.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\QuizController::class, 'index'])->name('index');
        });

        // Quiz - Take & Results
        Route::prefix('quiz-take')->name('quiz-take.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\QuizController::class, 'index'])->name('index');
            Route::get('{quiz}/start', [\App\Http\Controllers\Student\QuizController::class, 'start'])->name('start');
            Route::post('{quiz}/submit', [\App\Http\Controllers\Student\QuizController::class, 'submit'])->name('submit');
        });

        Route::prefix('quiz-result')->name('quiz-result.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Student\DashboardController::class, 'quizResults'])->name('index');
            Route::get('{submission}', [\App\Http\Controllers\Student\QuizController::class, 'result'])->name('show');
        });

      
    });

    // ─── Shared Resources (all authenticated) ───
    Route::resource('attendance', AttendanceController::class);
    Route::resource('course-materials', CourseMaterialController::class);
    Route::get('course-materials/{courseMaterial}/download', [CourseMaterialController::class, 'download'])->name('course-materials.download');
    Route::resource('discipline', DisciplineController::class);
    Route::resource('guardians', GuardianController::class);
    Route::post('guardians/{guardian}/assign-student', [GuardianController::class, 'assignStudent'])->name('guardians.assign-student');
    Route::delete('guardians/{guardian}/remove-student/{student}', [GuardianController::class, 'removeStudent'])->name('guardians.remove-student');
    Route::resource('conversations', ConversationController::class);
    Route::post('conversations/{conversation}/add-participant', [ConversationController::class, 'addParticipant'])->name('conversations.add-participant');
    Route::delete('conversations/{conversation}/remove-participant/{user}', [ConversationController::class, 'removeParticipant'])->name('conversations.remove-participant');
    Route::post('conversations/{conversation}/archive', [ConversationController::class, 'archive'])->name('conversations.archive');
    Route::resource('schedules', ScheduleController::class);
    Route::get('schedules/class/{classe}', [ScheduleController::class, 'viewClass'])->name('schedules.viewClass');

    Route::post('message-attachments/{message}', [MessageAttachmentController::class, 'store'])->name('message-attachments.store');
    Route::get('message-attachments/{attachment}/download', [MessageAttachmentController::class, 'download'])->name('message-attachments.download');
    Route::delete('message-attachments/{attachment}', [MessageAttachmentController::class, 'destroy'])->name('message-attachments.destroy');
});
