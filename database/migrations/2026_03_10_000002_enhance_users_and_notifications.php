<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── Enhance users table ──
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'first_name'))
                $table->string('first_name')->nullable()->after('name');
            if (!Schema::hasColumn('users', 'last_name'))
                $table->string('last_name')->nullable()->after('first_name');
            if (!Schema::hasColumn('users', 'bio'))
                $table->text('bio')->nullable()->after('profile_photo');
            if (!Schema::hasColumn('users', 'theme'))
                $table->string('theme')->default('light')->after('bio');
            if (!Schema::hasColumn('users', 'email_notifications'))
                $table->boolean('email_notifications')->default(true);
            if (!Schema::hasColumn('users', 'push_notifications'))
                $table->boolean('push_notifications')->default(true);
            if (!Schema::hasColumn('users', 'in_app_notifications'))
                $table->boolean('in_app_notifications')->default(true);
            if (!Schema::hasColumn('users', 'notif_security'))
                $table->boolean('notif_security')->default(true);
            if (!Schema::hasColumn('users', 'notif_grades'))
                $table->boolean('notif_grades')->default(true);
            if (!Schema::hasColumn('users', 'notif_payments'))
                $table->boolean('notif_payments')->default(true);
            if (!Schema::hasColumn('users', 'notif_announcements'))
                $table->boolean('notif_announcements')->default(true);
            if (!Schema::hasColumn('users', 'notif_messages'))
                $table->boolean('notif_messages')->default(true);
            if (!Schema::hasColumn('users', 'notif_absences'))
                $table->boolean('notif_absences')->default(true);
            if (!Schema::hasColumn('users', 'two_factor_enabled'))
                $table->boolean('two_factor_enabled')->default(false);
            if (!Schema::hasColumn('users', 'force_password_change'))
                $table->boolean('force_password_change')->default(false);
        });

        // ── Enhance notifications table ──
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'action_url'))
                $table->string('action_url')->nullable();
            if (!Schema::hasColumn('notifications', 'icon'))
                $table->string('icon')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $cols = ['first_name','last_name','bio','theme','email_notifications',
                'push_notifications','in_app_notifications','notif_security',
                'notif_grades','notif_payments','notif_announcements','notif_messages',
                'notif_absences','two_factor_enabled','force_password_change'];
            foreach ($cols as $col) {
                if (Schema::hasColumn('users', $col)) $table->dropColumn($col);
            }
        });
    }
};
