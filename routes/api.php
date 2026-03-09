<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API v1 Routes
|--------------------------------------------------------------------------
|
| Here are all the API v1 endpoints for Millénaire Connect
| These routes serve the Vue.js frontend and mobile clients
|
*/

Route::prefix('api/v1')->name('api.v1.')->group(function () {

    /**
     * ===== PUBLIC ROUTES (No Authentication Required) =====
     */

    // Authentication endpoints
    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('login', 'App\Http\Controllers\Api\V1\Auth\LoginController@login')->name('login');
        Route::post('register-student', 'App\Http\Controllers\Api\V1\Auth\RegisterStudentController@register')->name('register.student');
        Route::post('forgot-password', 'App\Http\Controllers\Api\V1\Auth\ForgotPasswordController@forgot')->name('password.forgot');
        Route::post('reset-password', 'App\Http\Controllers\Api\V1\Auth\ForgotPasswordController@reset')->name('password.reset');
    });

    /**
     * ===== AUTHENTICATED ROUTES =====
     */
    Route::middleware('auth:sanctum')->group(function () {

        // Logout
        Route::post('auth/logout', 'App\Http\Controllers\Api\V1\Auth\LoginController@logout')->name('auth.logout');

        // User Profile
        Route::prefix('profile')->name('profile.')->group(function () {
            Route::get('/', 'App\Http\Controllers\Api\V1\Profile\ProfileController@show')->name('show');
            Route::put('/', 'App\Http\Controllers\Api\V1\Profile\ProfileController@update')->name('update');
            Route::put('password', 'App\Http\Controllers\Api\V1\Profile\ProfileController@changePassword')->name('password');
        });

        /**
         * ===== TEACHER ROUTES =====
         */
        Route::middleware('role:teacher,professeur,prof_principal')
            ->prefix('teacher')
            ->name('teacher.')
            ->group(function () {

                // Dashboard data
                Route::get('dashboard', 'App\Http\Controllers\Api\V1\Teacher\DashboardController@index')->name('dashboard');

                // My Classes and Students
                Route::get('my-students', 'App\Http\Controllers\Api\V1\Teacher\StudentController@myStudents')->name('my-students');
                Route::get('students/search', 'App\Http\Controllers\Api\V1\Teacher\StudentController@search')->name('students.search');

                // Live Bulletin (🔥 STAR FEATURE)
                Route::prefix('grades')->name('grades.')->group(function () {
                    Route::get('bulletin/{studentId}/{sequence}', 'App\Http\Controllers\Api\V1\Teacher\GradeController@getBulletin')->name('bulletin');
                    Route::post('save', 'App\Http\Controllers\Api\V1\Teacher\GradeController@store')->name('save');
                    Route::get('class-stats/{classId}', 'App\Http\Controllers\Api\V1\Teacher\GradeController@getClassStats')->name('class-stats');
                    Route::get('export/{classId}', 'App\Http\Controllers\Api\V1\Teacher\GradeController@exportGrades')->name('export');
                });

                // Attendance
                Route::apiResource('attendance', 'App\Http\Controllers\Api\V1\Teacher\AttendanceController');

                // 🔥 Template Digitizer (OCR) - Prof Principal only
                Route::middleware('role:prof_principal')
                    ->prefix('bulletin-templates')
                    ->name('templates.')
                    ->group(function () {
                        Route::get('/', 'App\Http\Controllers\Api\V1\Teacher\BulletinTemplateController@index')->name('index');
                        Route::post('/', 'App\Http\Controllers\Api\V1\Teacher\BulletinTemplateController@store')->name('store');
                        Route::get('{id}', 'App\Http\Controllers\Api\V1\Teacher\BulletinTemplateController@show')->name('show');
                        Route::put('{id}', 'App\Http\Controllers\Api\V1\Teacher\BulletinTemplateController@update')->name('update');
                        Route::post('{id}/upload', 'App\Http\Controllers\Api\V1\Teacher\BulletinTemplateController@upload')->name('upload');
                        Route::post('{id}/process-ocr', 'App\Http\Controllers\Api\V1\Teacher\BulletinTemplateController@processOCR')->name('process-ocr');
                    });

                // Bulletins (Report Cards) - Prof Principal only
                Route::middleware('role:prof_principal')
                    ->prefix('bulletins')
                    ->name('bulletins.')
                    ->group(function () {
                        Route::get('class/{classId}/{sequence}', 'App\Http\Controllers\Api\V1\Teacher\BulletinController@getClassBulletins')->name('class');
                        Route::get('{id}', 'App\Http\Controllers\Api\V1\Teacher\BulletinController@show')->name('show');
                        Route::post('lock-sequence', 'App\Http\Controllers\Api\V1\Teacher\BulletinController@lockSequence')->name('lock-sequence');
                        Route::post('export-pdf', 'App\Http\Controllers\Api\V1\Teacher\BulletinController@exportPDF')->name('export-pdf');
                    });

                // 🔥 OCR Zone Detection System
                Route::middleware('role:prof_principal')
                    ->prefix('bulletin/ocr')
                    ->name('bulletin.ocr.')
                    ->group(function () {
                        Route::post('upload', 'App\Http\Controllers\Teacher\BulletinOCRAPIController@upload')->name('upload');
                        Route::get('{id}/ocr-zones', 'App\Http\Controllers\Teacher\BulletinOCRAPIController@getZones')->name('zones');
                        Route::post('{id}/ocr-zones', 'App\Http\Controllers\Teacher\BulletinOCRAPIController@saveZones')->name('save-zones');
                    });
            });

        /**
         * ===== PARENT ROUTES =====
         */
        Route::middleware('role:parent')
            ->prefix('parent')
            ->name('parent.')
            ->group(function () {

                Route::get('dashboard', 'App\Http\Controllers\Api\V1\Parent\DashboardController@index')->name('dashboard');

                // Children management
                Route::get('children', 'App\Http\Controllers\Api\V1\Parent\ChildrenController@index')->name('children');
                Route::get('children/{childId}/performance', 'App\Http\Controllers\Api\V1\Parent\ChildrenController@performance')->name('child.performance');

                // Payments (🔥 Mobile Money Integration)
                Route::prefix('payments')->name('payments.')->group(function () {
                    Route::get('{childId}', 'App\Http\Controllers\Api\V1\Parent\PaymentController@index')->name('index');
                    Route::post('initiate', 'App\Http\Controllers\Api\V1\Parent\PaymentController@initiate')->name('initiate');
                    Route::get('status/{paymentId}', 'App\Http\Controllers\Api\V1\Parent\PaymentController@checkStatus')->name('status');
                    Route::get('history/{childId}', 'App\Http\Controllers\Api\V1\Parent\PaymentController@history')->name('history');
                    Route::get('statistics/{childId}', 'App\Http\Controllers\Api\V1\Parent\PaymentController@statistics')->name('statistics');
                });

                // Notifications and Alerts
                Route::get('alerts/{childId}', 'App\Http\Controllers\Api\V1\Parent\AlertController@index')->name('alerts');
                Route::get('notifications', 'App\Http\Controllers\Api\V1\Parent\NotificationController@index')->name('notifications');
            });

        /**
         * ===== STUDENT ROUTES =====
         */
        Route::middleware('role:student')
            ->prefix('student')
            ->name('student.')
            ->group(function () {

                Route::get('dashboard', 'App\Http\Controllers\Api\V1\Student\DashboardController@index')->name('dashboard');
                Route::get('grades', 'App\Http\Controllers\Api\V1\Student\GradesController@index')->name('grades');
                Route::get('performance', 'App\Http\Controllers\Api\V1\Student\GradesController@performance')->name('performance');
                Route::get('attendance', 'App\Http\Controllers\Api\V1\Student\AttendanceController@index')->name('attendance');
                Route::get('assignments', 'App\Http\Controllers\Api\V1\Student\AssignmentController@index')->name('assignments');
            });

        /**
         * ===== ADMIN ROUTES =====
         */
        Route::middleware('role:admin,censeur,intendant')
            ->prefix('admin')
            ->name('admin.')
            ->group(function () {

                Route::get('dashboard', 'App\Http\Controllers\Api\V1\Admin\DashboardController@index')->name('dashboard');

                // Users management
                Route::apiResource('users', 'App\Http\Controllers\Api\V1\Admin\UserController');
                Route::post('users/import', 'App\Http\Controllers\Api\V1\Admin\UserController@import')->name('users.import');

                // Classes
                Route::apiResource('classes', 'App\Http\Controllers\Api\V1\Admin\ClasseController');

                // Subjects
                Route::apiResource('subjects', 'App\Http\Controllers\Api\V1\Admin\SubjectController');

                // 🔥 Teacher Assignments (Drag & Drop)
                Route::prefix('assignments')->name('assignments.')->group(function () {
                    Route::get('/', 'App\Http\Controllers\Api\V1\Admin\AssignmentController@index')->name('index');
                    Route::post('/', 'App\Http\Controllers\Api\V1\Admin\AssignmentController@store')->name('store');
                    Route::put('{id}', 'App\Http\Controllers\Api\V1\Admin\AssignmentController@update')->name('update');
                    Route::delete('{id}', 'App\Http\Controllers\Api\V1\Admin\AssignmentController@destroy')->name('destroy');
                    Route::get('available-teachers', 'App\Http\Controllers\Api\V1\Admin\AssignmentController@availableTeachers')->name('available-teachers');
                    Route::get('statistics', 'App\Http\Controllers\Api\V1\Admin\AssignmentController@statistics')->name('statistics');
                });

                // Financial management
                Route::prefix('financial')->name('financial.')->group(function () {
                    Route::get('overview', 'App\Http\Controllers\Api\V1\Admin\FinancialController@overview')->name('overview');
                    Route::get('payments', 'App\Http\Controllers\Api\V1\Admin\FinancialController@payments')->name('payments');
                    Route::get('unpaid-students', 'App\Http\Controllers\Api\V1\Admin\FinancialController@unpaidStudents')->name('unpaid-students');
                    Route::get('statistics', 'App\Http\Controllers\Api\V1\Admin\FinancialController@statistics')->name('statistics');
                    Route::get('export/{format}', 'App\Http\Controllers\Api\V1\Admin\FinancialController@export')->name('export');
                });
            });

        /**
         * ===== CHAT ROUTES (See conversations, messages, etc) =====
         */
        Route::prefix('chat')->name('chat.')->group(function () {
            // Conversations
            Route::get('conversations', 'App\Http\Controllers\Api\V1\Chat\ConversationController@index')->name('conversations.index');
            Route::post('conversations', 'App\Http\Controllers\Api\V1\Chat\ConversationController@store')->name('conversations.store');
            Route::get('conversations/{conversationId}', 'App\Http\Controllers\Api\V1\Chat\ConversationController@show')->name('conversations.show');
            Route::post('conversations/{conversationId}/participants', 'App\Http\Controllers\Api\V1\Chat\ConversationController@addParticipants')->name('conversations.participants.add');
            Route::delete('conversations/{conversationId}/participants/{userId}', 'App\Http\Controllers\Api\V1\Chat\ConversationController@removeParticipant')->name('conversations.participants.remove');
            Route::patch('conversations/{conversationId}/archive', 'App\Http\Controllers\Api\V1\Chat\ConversationController@archive')->name('conversations.archive');

            // Messages
            Route::get('conversations/{conversationId}/messages', 'App\Http\Controllers\Api\V1\Chat\MessageController@index')->name('messages.index');
            Route::post('conversations/{conversationId}/messages', 'App\Http\Controllers\Api\V1\Chat\MessageController@store')->name('messages.store');
            Route::put('messages/{messageId}', 'App\Http\Controllers\Api\V1\Chat\MessageController@update')->name('messages.update');
            Route::delete('messages/{messageId}', 'App\Http\Controllers\Api\V1\Chat\MessageController@destroy')->name('messages.destroy');

            // Reactions
            Route::post('messages/{messageId}/reactions', 'App\Http\Controllers\Api\V1\Chat\MessageController@addReaction')->name('messages.reactions.add');
            Route::delete('messages/{messageId}/reactions/{emoji}', 'App\Http\Controllers\Api\V1\Chat\MessageController@removeReaction')->name('messages.reactions.remove');

            // Typing Indicators
            Route::post('conversations/{conversationId}/typing', 'App\Http\Controllers\Api\V1\Chat\TypingIndicatorController@markTyping')->name('conversations.typing.mark');
            Route::get('conversations/{conversationId}/typing', 'App\Http\Controllers\Api\V1\Chat\TypingIndicatorController@getTypingUsers')->name('conversations.typing.get');
        });

        /**
         * ===== SHARED ROUTES (All authenticated users) =====
         */
        
        // Announcements
        Route::get('announcements', 'App\Http\Controllers\Api\V1\Announcement\AnnouncementController@index')->name('announcements.index');
        Route::get('announcements/{id}', 'App\Http\Controllers\Api\V1\Announcement\AnnouncementController@show')->name('announcements.show');

        // Notifications
        Route::prefix('notifications')->name('notifications.')->group(function () {
            Route::get('/', 'App\Http\Controllers\Api\V1\Notification\NotificationController@index')->name('index');
            Route::post('{id}/read', 'App\Http\Controllers\Api\V1\Notification\NotificationController@markAsRead')->name('read');
            Route::post('read-all', 'App\Http\Controllers\Api\V1\Notification\NotificationController@markAllAsRead')->name('read-all');
        });
    });

});
