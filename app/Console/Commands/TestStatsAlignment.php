<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BulletinNgConfig;
use App\Models\BulletinNgSession;
use App\Models\User;
use App\Http\Controllers\Teacher\BulletinNgController;
use App\Services\BulletinCalculationService;

class TestStatsAlignment extends Command
{
    protected $signature = 'test:stats-alignment';
    protected $description = 'Test that Step 5 and Step 7 produce identical statistics';

    public function handle()
    {
        $this->line('========== TEST: Step 5 vs Step 7 Stats Alignment ==========');
        $this->newLine();

        // Find a teacher with active config
        $teacher = User::where('role', 'TeacherPrincipal')->first();
        if (!$teacher) {
            $this->error('❌ No teacher found');
            return 1;
        }

        $config = BulletinNgConfig::where('prof_principal_id', $teacher->id)
            ->where('is_active', true)
            ->first();

        if (!$config) {
            $this->error('❌ No active config found for teacher');
            return 1;
        }

        $this->info("✅ Found Config: {$config->name} (Trimestre {$config->trimestre})");

        // Get session
        $session = $config->sessions()
            ->where('trimestre_number', $config->trimestre)
            ->where('sequence_number', $config->sequence)
            ->latest()
            ->first();

        if (!$session) {
            $this->warn('⚠️  No session found');
            return 1;
        }

        $this->info("✅ Using Session: {$session->id}");
        $this->newLine();

        // Create controller instance
        $calcService = app(BulletinCalculationService::class);
        $controller = new class($calcService) extends BulletinNgController {
            public function __construct($calc) {
                $this->calculationService = $calc;
            }
            
            public function testCalculateStats($config, $session) {
                return $this->calculateStats($config, $session);
            }
            
            public function testCalculateClassStats($config, $students) {
                return $this->calculateClassStats($config, $students);
            }
        };

        // Get students
        $students = $config->students()
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();

        if ($students->isEmpty()) {
            $this->warn('⚠️  No active students found');
            return 1;
        }

        $this->info("✅ Found {$students->count()} active students");
        $this->newLine();

        // TEST: Compare stats
        $this->line('--- COMPARING STATISTICS ---');
        $stats_step5 = $controller->testCalculateStats($config, $session);
        $stats_step7 = $controller->testCalculateClassStats($config, $students);

        $this->newLine();
        $this->info('📊 STEP 5 (calculateStats):');
        $this->line("  avg:     {$stats_step5['avg']}");
        $this->line("  pct:     {$stats_step5['pct']}%");
        $this->line("  max:     {$stats_step5['max']}");
        $this->line("  min:     {$stats_step5['min']}");
        $this->line("  passing: {$stats_step5['passing']}");
        $this->line("  students: " . count($stats_step5['avgs']));

        $this->newLine();
        $this->info('📊 STEP 7 (calculateClassStats):');
        $this->line("  avg:     {$stats_step7['avg']}");
        $this->line("  pct:     {$stats_step7['pct']}%");
        $this->line("  max:     {$stats_step7['max']}");
        $this->line("  min:     {$stats_step7['min']}");
        $this->line("  passing: {$stats_step7['passing']}");
        $this->line("  students: " . count($stats_step7['avgs']));

        // VALIDATION
        $this->newLine();
        $this->line('--- VALIDATION ---');
        $all_passed = true;

        $checks = [
            'avg' => ['name' => 'Class Average', 'tolerance' => 0.01],
            'pct' => ['name' => 'Success Rate %', 'tolerance' => 1],
            'max' => ['name' => 'Highest Grade', 'tolerance' => 0.01],
            'min' => ['name' => 'Lowest Grade', 'tolerance' => 0.01],
            'passing' => ['name' => 'Passing Count', 'tolerance' => 0],
        ];

        foreach ($checks as $field => $check) {
            $diff = abs($stats_step5[$field] - $stats_step7[$field]);
            $tolerance = $check['tolerance'];
            $passed = $diff <= $tolerance;
            $all_passed = $all_passed && $passed;
            
            $status = $passed ? "✅" : "❌";
            $this->line("$status {$check['name']}: |{$stats_step5[$field]} - {$stats_step7[$field]}| = $diff (tolerance: $tolerance)");
        }

        // Compare individual student averages
        $this->newLine();
        $this->line('--- INDIVIDUAL AVERAGES ---');
        $avgs_match = true;
        foreach ($students->take(5) as $student) {
            $avg5 = $stats_step5['avgs'][$student->id] ?? 0;
            $avg7 = $stats_step7['avgs'][$student->id] ?? 0;
            
            $match = abs($avg5 - $avg7) <= 0.01;
            if (!$match) {
                $this->warn("❌ Student {$student->id}: Step5={$avg5}, Step7={$avg7}");
                $avgs_match = false;
            }
        }

        if ($avgs_match) {
            $this->info("✅ Student averages match (checked {$students->count()} students)");
        }

        // Final result
        $this->newLine();
        $this->line('========== RESULT ==========');
        if ($all_passed && $avgs_match) {
            $this->info('✅ ✅ ✅ SUCCESS: Step 5 and Step 7 produce IDENTICAL statistics!');
            return 0;
        } else {
            $this->error('❌ FAILURE: Statistics do not match between Step 5 and Step 7');
            return 1;
        }
    }
}
