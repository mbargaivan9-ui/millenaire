<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VerifyTablesExist extends Command
{
    protected $signature = 'verify:tables';
    protected $description = 'Verify all required tables exist in the database';

    public function handle()
    {
        $this->info('Verifying Required Tables...');
        $this->info('============================');
        
        $requiredTables = [
            'users' => 'Users (core)',
            'students' => 'Student profiles',
            'teachers' => 'Teacher profiles',
            'guardians' => 'Guardian profiles',
            'classes' => 'Classes',
            'subjects' => 'Subjects',
            'marks' => 'Marks (CRITICAL)',
            'report_cards' => 'Report Cards (CRITICAL)',
            'grades' => 'Grades',
            'class_subject_teacher' => 'Class-Subject-Teacher',
            'attendances' => 'Attendances',
            'disciplines' => 'Disciplines',
            'schedules' => 'Schedules',
            'payments' => 'Payments',
            'absences' => 'Absences',
            'messages' => 'Messages',
            'conversations' => 'Conversations',
        ];
        
        $allExist = true;
        foreach ($requiredTables as $table => $description) {
            $exists = DB::connection()->getSchemaBuilder()->hasTable($table);
            $status = $exists ? '✅' : '❌';
            $this->line("{$status} {$description}: {$table}");
            if (!$exists) {
                $allExist = false;
            }
        }
        
        $this->info('============================');
        if ($allExist) {
            $this->info('✅ ALL REQUIRED TABLES FOUND');
            $this->line('✅ Teacher dashboard should load without errors!');
            return 0;
        } else {
            $this->error('❌ SOME TABLES ARE MISSING');
            return 1;
        }
    }
}
