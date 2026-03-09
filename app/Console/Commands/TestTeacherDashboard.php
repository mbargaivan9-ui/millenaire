<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\ClassSubjectTeacher;
use App\Models\Mark;
use App\Models\ReportCard;
use Illuminate\Console\Command;

class TestTeacherDashboard extends Command
{
    protected $signature = 'test:teacher-dashboard';
    protected $description = 'Test teacher dashboard data loading';

    public function handle()
    {
        try {
            $this->info('Testing Teacher Dashboard Data Loading...');
            $this->info('=====================================');
            
            // Test 1: Get teacher
            $teacher = User::where('email', 'prof.mathematiques@millenniaire.test')->first();
            if (!$teacher) {
                $this->error('❌ Teacher user not found');
                return 1;
            }
            $this->line('✅ Teacher found: ' . $teacher->name);
            
            // Test 2: Get profile
            $profile = $teacher->teacher;
            if (!$profile) {
                $this->error('❌ Teacher profile not found');
                return 1;
            }
            $this->line('✅ Teacher profile found (ID: ' . $profile->id . ')');
            
            // Test 3: Get classes
            $classIds = ClassSubjectTeacher::where('teacher_id', $profile->id)
                ->distinct('class_id')
                ->pluck('class_id');
            $this->line('✅ Classes assigned: ' . count($classIds));
            
            // Test 4: Check marks table
            $marksCount = Mark::count();
            $this->line('✅ Marks in database: ' . $marksCount);
            
            // Test 5: Check report cards
            $reportCardsCount = ReportCard::count();
            $this->line('✅ Report cards in database: ' . $reportCardsCount);
            
            $this->info('=====================================');
            $this->info('✅ ALL TESTS PASSED!');
            $this->line('Teacher dashboard should now load without errors.');
            
            return 0;
            
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            $this->error($e->getTraceAsString());
            return 1;
        }
    }
}
