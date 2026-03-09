<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\HomeController;

// ═══════════════════════════════════════════════════════════════
// Include Bulletin Management Routes (OCR + Template + Grades)
// ═══════════════════════════════════════════════════════════════
require __DIR__ . '/bulletins.php';
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
use App\Http\Controllers\BulletinTemplateController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\MessageAttachmentController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\HealthCheckController;
use App\Http\Controllers\BulletinVerifyController;
use App\Http\Controllers\Admin\BulletinAdminController;
use App\Http\Controllers\Admin\BulletinStructureValidationController;
use App\Http\Controllers\Parent\BulletinController as ParentBulletinController;
use App\Http\Controllers\Student\BulletinController as StudentBulletinController;
// Admin
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\KpiDashboardController;
use App\Http\Controllers\Admin\TeacherAbsenceController;
use App\Http\Controllers\Admin\DynamicBulletinController;
// Teacher
use App\Http\Controllers\Teacher\DashboardController as TeacherDashboardController;
use App\Http\Controllers\Teacher\MarksController;
use App\Http\Controllers\Teacher\AttendanceController as TeacherAttendanceController;
use App\Http\Controllers\Teacher\BulletinController;
use App\Http\Controllers\Teacher\BulletinTemplateController as TeacherBulletinTemplateController;
use App\Http\Controllers\Teacher\GradeEntryController;
use App\Http\Controllers\Teacher\TeacherAdvancedController;
use App\Http\Controllers\Teacher\PrincipalStudentAbsenceController;
use App\Http\Controllers\Teacher\BulletinStructureOCRController;
use App\Http\Controllers\Teacher\BulletinOCRAPIController;
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
Route::get('/api/announcements/latest', [\App\Http\Controllers\Api\AnnouncementApiController::class, 'latest'])->name('api.announcements.latest');

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
Route::get('bulletin/verify/{token}', [BulletinVerifyController::class, 'verify'])->name('bulletin.verify');
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

        // KPI Dashboard (Phase 3)
        Route::get('kpi', [KpiDashboardController::class, 'index'])->name('kpi.index');
        Route::get('kpi/refresh', [KpiDashboardController::class, 'refreshKpis'])->name('kpi.refresh');
        Route::get('kpi/class/{classe}', [KpiDashboardController::class, 'getClassDetails'])->name('kpi.class-details');
        Route::post('kpi/assign-teacher', [KpiDashboardController::class, 'assignTeacher'])->name('kpi.assign-teacher');
        Route::post('kpi/unassign-teacher', [KpiDashboardController::class, 'unassignTeacher'])->name('kpi.unassign-teacher');
        Route::get('kpi/export-csv', [KpiDashboardController::class, 'exportCsv'])->name('kpi.export-csv');

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

        // Settings
        Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
        Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
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

        // Bulletins Validation & Publishing (Phase 6)
        Route::prefix('bulletins')->name('bulletins.')->group(function () {
            Route::get('/', [BulletinAdminController::class, 'index'])->name('index');
            Route::post('{bulletin}/validate', [BulletinAdminController::class, 'validate'])->name('validate');
            Route::post('{bulletin}/publish', [BulletinAdminController::class, 'publish'])->name('publish');
            Route::post('{bulletin}/reject', [BulletinAdminController::class, 'reject'])->name('reject');
            Route::get('{bulletin}', [BulletinAdminController::class, 'show'])->name('show');
        });

        // Dynamic Bulletin OCR (Phase 4)
        Route::prefix('bulletin')->name('bulletin.')->group(function () {
            Route::get('/{classe}', [DynamicBulletinController::class, 'index'])->name('index');
            Route::get('/{classe}/upload', [DynamicBulletinController::class, 'uploadForm'])->name('uploadForm');
            Route::post('/{classe}/upload', [DynamicBulletinController::class, 'processUpload'])->name('processUpload');
            Route::get('/structure/{structure}/review', [DynamicBulletinController::class, 'review'])->name('review');
            Route::post('/structure/{structure}/update', [DynamicBulletinController::class, 'update'])->name('update');
            Route::post('/structure/{structure}/validate', [DynamicBulletinController::class, 'validateStructure'])->name('validate');
            Route::post('/structure/{structure}/activate', [DynamicBulletinController::class, 'activate'])->name('activate');
            Route::get('/structure/{structure}/preview', [DynamicBulletinController::class, 'preview'])->name('preview');
            Route::get('/structure/{structure}/history', [DynamicBulletinController::class, 'history'])->name('history');
            Route::post('/structure/{structure}/revision/{revision}/revert', [DynamicBulletinController::class, 'revertToRevision'])->name('revertToRevision');
            Route::get('/structure/{structure}/export', [DynamicBulletinController::class, 'export'])->name('export');
            Route::post('/structure/{structure}/archive', [DynamicBulletinController::class, 'archive'])->name('archive');
            Route::delete('/structure/{structure}', [DynamicBulletinController::class, 'delete'])->name('delete');
            
            // PDF Export Routes (Phase 9)
            Route::get('/structure/{structure}/student/{student}/bulletin-pdf', [DynamicBulletinController::class, 'downloadBulletinPDF'])->name('downloadBulletinPDF');
            Route::get('/structure/{structure}/bulk-pdf', [DynamicBulletinController::class, 'downloadBulkBulletinsPDF'])->name('downloadBulkBulletinsPDF');
            Route::get('/structure/{structure}/student/{student}/preview-html', [DynamicBulletinController::class, 'previewBulletinHTML'])->name('previewBulletinHTML');
            Route::post('/structure/{structure}/save-bulletins', [DynamicBulletinController::class, 'saveBulletinsToStorage'])->name('saveBulletinsToStorage');
        });

        // Bulletin Structure Validation (OCR Admin Interface)
        Route::prefix('bulletin-structure')->name('bulletin-structure.')->group(function () {
            Route::prefix('validation')->name('validation.')->group(function () {
                Route::get('/', [BulletinStructureValidationController::class, 'index'])->name('index');
                Route::get('stats', [BulletinStructureValidationController::class, 'stats'])->name('stats');
                Route::get('{structure}', [BulletinStructureValidationController::class, 'show'])->name('show');
                Route::get('{structure}/edit', [BulletinStructureValidationController::class, 'edit'])->name('edit');
                Route::put('{structure}', [BulletinStructureValidationController::class, 'update'])->name('update');
                Route::post('{structure}/approve', [BulletinStructureValidationController::class, 'approve'])->name('approve');
                Route::post('{structure}/reject', [BulletinStructureValidationController::class, 'reject'])->name('reject');
                Route::post('{structure}/activate', [BulletinStructureValidationController::class, 'activate'])->name('activate');
                Route::post('{structure}/deactivate', [BulletinStructureValidationController::class, 'deactivate'])->name('deactivate');
                Route::get('{structure}/export', [BulletinStructureValidationController::class, 'export'])->name('export');
                Route::post('bulk-verify', [BulletinStructureValidationController::class, 'bulkVerify'])->name('bulk-verify');
            });
        });

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

        // Advanced dashboard (Phase 3)
        Route::get('advanced', [TeacherAdvancedController::class, 'dashboard'])->name('advanced.dashboard');
        Route::get('my-summary', [TeacherAdvancedController::class, 'mySummary'])->name('my-summary');

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

        // ─── Bulletin Vivant Module ───
        Route::prefix('bulletin')->name('bulletin.')->group(function () {
            // OCR API endpoints (authenticated teachers only)
            Route::middleware('auth:sanctum,web')->prefix('ocr')->name('ocr.')->group(function () {
                Route::post('upload', [BulletinOCRAPIController::class, 'processUpload'])->name('upload');
                Route::post('save-structure', [BulletinOCRAPIController::class, 'saveStructure'])->name('save-structure');
            });

            // Template Grid — Prof Principal (NEW) — MUST BE BEFORE parametrized routes
            Route::middleware('role:prof_principal,admin')->group(function () {
                Route::get('template-grid', [BulletinController::class, 'templateGrid'])->name('template-grid');
                Route::get('ocr-wizard', [BulletinController::class, 'ocrWizard'])->name('ocr-wizard');
                Route::get('api/student/{student}/stats', [BulletinController::class, 'getStudentStats'])->name('api.student-stats');
                Route::get('api/class/{classe}/stats', [BulletinController::class, 'getClassStats'])->name('api.class-stats');
            });

            Route::get('/', [BulletinController::class, 'index'])->name('index');
            Route::get('{classSubjectTeacher}/grid', [BulletinController::class, 'grid'])->name('grid');
            Route::get('{classSubjectTeacher}/student/{student}', [BulletinController::class, 'show'])->name('show');
            Route::post('save', [BulletinController::class, 'save'])->name('save'); // AJAX
            Route::get('completion', [BulletinController::class, 'completion'])->name('completion');
            Route::post('{classe}/lock', [BulletinController::class, 'lock'])->name('lock');
            Route::post('{classe}/unlock', [BulletinController::class, 'unlock'])->name('unlock');

            // Advanced (Phase 3)
            Route::get('stats/{classSubjectTeacher}', [TeacherAdvancedController::class, 'getSubjectStats'])->name('stats');
            Route::post('bulk-save', [TeacherAdvancedController::class, 'bulkSave'])->name('bulk-save');
            Route::get('{classe}/export-pdf', [TeacherAdvancedController::class, 'exportClasseBulletins'])->name('export-pdf');

            // PDF export (Phase 3 view)
            Route::get('{student}/pdf', [BulletinController::class, 'exportPdf'])->name('pdf');
        });

        // Report Cards (Prof Principal)
        Route::middleware('role:prof_principal,admin')->group(function () {
            Route::get('report-cards', [TeacherDashboardController::class, 'reportCards'])->name('report-cards');
            Route::get('report-cards/{reportCard}', [TeacherDashboardController::class, 'showReportCard'])->name('report-cards.show');
            Route::get('report-cards/{reportCard}/edit', [TeacherDashboardController::class, 'editReportCard'])->name('report-cards.edit');
            Route::put('report-cards/{reportCard}', [TeacherDashboardController::class, 'updateReportCard'])->name('report-cards.update');
            Route::get('report-cards/{reportCard}/pdf', [TeacherDashboardController::class, 'downloadPDF'])->name('report-cards.pdf');
            Route::resource('bulletin-templates', TeacherBulletinTemplateController::class);

            // Bulletin Structure OCR (NEW)
            Route::prefix('bulletin-structure-ocr')->name('bulletin-structure-ocr.')->group(function () {
                Route::get('create/{classe}', [BulletinStructureOCRController::class, 'createForm'])->name('create');
                Route::post('upload/{classe}', [BulletinStructureOCRController::class, 'processUpload'])->name('upload');
                Route::get('verify/{classe}', [BulletinStructureOCRController::class, 'showVerification'])->name('verify');
                Route::post('save/{classe}', [BulletinStructureOCRController::class, 'saveStructure'])->name('save');
                Route::get('/', [BulletinStructureOCRController::class, 'index'])->name('index');
                Route::get('{bulletinStructure}', [BulletinStructureOCRController::class, 'show'])->name('show');
                Route::get('{bulletinStructure}/edit', [BulletinStructureOCRController::class, 'edit'])->name('edit');
                Route::put('{bulletinStructure}', [BulletinStructureOCRController::class, 'update'])->name('update');
                Route::delete('{bulletinStructure}', [BulletinStructureOCRController::class, 'destroy'])->name('destroy');
            });

            // Student Absences (Prof Principal)
            Route::resource('student-absences', PrincipalStudentAbsenceController::class);
            Route::get('student-absences/bulk-create', [PrincipalStudentAbsenceController::class, 'bulkCreateForm'])->name('student-absences.bulk-create-form');
            Route::post('student-absences/bulk-create', [PrincipalStudentAbsenceController::class, 'bulkCreate'])->name('student-absences.bulk-create');
            Route::post('student-absences/{studentAbsence}/justify', [PrincipalStudentAbsenceController::class, 'justify'])->name('student-absences.justify');
            Route::get('student-absences/report', [PrincipalStudentAbsenceController::class, 'report'])->name('student-absences.report');

            // Parent Management (Prof Principal) — Full CRUD + Token Management
            Route::prefix('parent-management')->name('parent-management.')->group(function () {
                // CRUD Routes
                Route::get('{class}', [ParentManagementController::class, 'index'])->name('index');
                Route::get('{class}/create', [ParentManagementController::class, 'create'])->name('create');
                Route::post('{class}', [ParentManagementController::class, 'store'])->name('store');
                Route::get('{parent}/edit', [ParentManagementController::class, 'edit'])->name('edit');
                Route::put('{parent}', [ParentManagementController::class, 'update'])->name('update');
                Route::delete('{parent}', [ParentManagementController::class, 'destroy'])->name('destroy');
                
                // Token Management Routes
                Route::get('{class}/generate-tokens', [ParentManagementController::class, 'generateTokensForm'])->name('generate-tokens');
                Route::post('{class}/store-tokens', [ParentManagementController::class, 'storeTokens'])->name('store-tokens');
                Route::get('{class}/tokens', [ParentManagementController::class, 'listTokens'])->name('tokens');
                Route::delete('{token}/revoke', [ParentManagementController::class, 'revokeToken'])->name('revoke-token');
                Route::get('{class}/export-tokens', [ParentManagementController::class, 'exportTokensCSV'])->name('export-tokens');
            });
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

        // Schedule
        Route::get('schedule', [\App\Http\Controllers\Teacher\ScheduleController::class, 'index'])->name('schedule');

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
        Route::get('payments', [ParentPaymentController::class, 'index'])->name('payments');
        Route::get('children/{student}/payments', [ParentPaymentController::class, 'show'])->name('child.payments');
        Route::post('payments/initiate', [ParentPaymentController::class, 'initiate'])->name('payments.initiate');
        Route::get('payments/{payment}/status', [ParentPaymentController::class, 'checkStatus'])->name('payments.status');
        Route::get('receipts/{receipt}/download', [ParentPaymentController::class, 'downloadReceipt'])->name('receipts.download');

        // Appointments (parent side)
        Route::prefix('appointments')->name('appointments.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Parent\AppointmentController::class, 'index'])->name('index');
            Route::get('create', [\App\Http\Controllers\Parent\AppointmentController::class, 'create'])->name('create');
            Route::post('/', [\App\Http\Controllers\Parent\AppointmentController::class, 'store'])->name('store');
            Route::delete('{appointment}', [\App\Http\Controllers\Parent\AppointmentController::class, 'destroy'])->name('destroy');
        });

        // Bulletins (Report Cards)
        Route::prefix('bulletins')->name('bulletins.')->group(function () {
            Route::get('/', [ParentBulletinController::class, 'index'])->name('index');
            Route::get('{bulletin}', [ParentBulletinController::class, 'show'])->name('show');
            Route::get('{bulletin}/pdf', [ParentBulletinController::class, 'pdf'])->name('pdf');
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
        Route::get('report-cards', [StudentDashboardController::class, 'reportCards'])->name('report-cards');
        Route::get('courses', [StudentCourseController::class, 'index'])->name('courses.index');

        // Progress (Phase 3)
        Route::get('progress', [StudentProgressController::class, 'index'])->name('progress.index');
        Route::get('progress/data', [StudentProgressController::class, 'getProgressData'])->name('progress.data');
        Route::get('progress/chart', [StudentProgressController::class, 'getChartData'])->name('progress.chart');

        // Bulletins (Report Cards)
        Route::prefix('bulletins')->name('bulletins.')->group(function () {
            Route::get('/', [StudentBulletinController::class, 'index'])->name('index');
            Route::get('{bulletin}', [StudentBulletinController::class, 'show'])->name('show');
            Route::get('{bulletin}/pdf', [StudentBulletinController::class, 'pdf'])->name('pdf');
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
    Route::resource('bulletin-templates', BulletinTemplateController::class);
    Route::post('message-attachments/{message}', [MessageAttachmentController::class, 'store'])->name('message-attachments.store');
    Route::get('message-attachments/{attachment}/download', [MessageAttachmentController::class, 'download'])->name('message-attachments.download');
    Route::delete('message-attachments/{attachment}', [MessageAttachmentController::class, 'destroy'])->name('message-attachments.destroy');
});
