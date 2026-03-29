<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BulletinNgConfig;
use App\Models\BulletinNgNote;
use App\Services\BulletinCalculationService;

class DebugStep7Stats extends Command
{
    protected $signature = 'debug:step7-stats';
    protected $description = 'Debug Step 7 statistics calculation';

    public function handle()
    {
        $this->line('========== STEP 7 DEBUG - STATISTICS CALCULATION ==========');
        $this->newLine();

        // Find a config
        $config = BulletinNgConfig::first();
        if (!$config) {
            $this->error('❌ No config found');
            return 1;
        }

        $this->info("📋 Config: {$config->name} (Trimestre {$config->trimestre})");
        $this->newLine();

        $students = $config->students()
            ->where('is_active', true)
            ->orderBy('ordre')
            ->get();

        $this->info("👥 Total active students: {$students->count()}");
        $this->newLine();

        // Get calculation service
        $calcService = app(BulletinCalculationService::class);

        // Check first 3 students
        $this->line('--- INDIVIDUAL STUDENT DATA ---');
        $this->newLine();

        $statsArray = [];

        foreach ($students->take(5) as $student) {
            $this->line("📍 Student: {$student->nom}");
            $this->line("   ID in DB: {$student->id}");

            // Count notes
            $notesCount = BulletinNgNote::where('ng_student_id', $student->id)
                ->where('config_id', $config->id)
                ->whereNotNull('note')
                ->count();

            $this->line("   Notes with values: $notesCount");

            // Show sample notes
            $sampleNotes = BulletinNgNote::where('ng_student_id', $student->id)
                ->where('config_id', $config->id)
                ->whereNotNull('note')
                ->with('subject')
                ->take(3)
                ->get();

            if ($sampleNotes->count() > 0) {
                foreach ($sampleNotes as $note) {
                    $subj = $note->subject->nom ?? 'Unknown';
                    $coef = $note->subject->coefficient ?? '?';
                    $this->line("     • {$subj}: {$note->note}/20 (Coef: $coef, Seq: {$note->sequence_number})");
                }
            } else {
                $this->line("     (No notes found)");
            }

            // Calculate sequence averages
            $seq1 = $calcService->calculateSequenceAverage($student->id, 1, $config->id);
            $seq2 = $calcService->calculateSequenceAverage($student->id, 2, $config->id);
            
            // Calculate trimester average
            $trimAvg = $calcService->calculateTrimesterAverage($student->id, $config->trimestre, $config->id);

            $this->line("   📊 Calculations:");
            $this->line("      • Sequence 1 Avg: $seq1");
            $this->line("      • Sequence 2 Avg: $seq2");
            $this->line("      • Trimester Avg: $trimAvg");

            $statsArray[$student->id] = $trimAvg;
            $this->newLine();
        }

        // Now run the actual controller calculation
        $this->line('--- WHAT CONTROLLER RETURNS ---');
        $this->newLine();

        // Simulate what calculateClassStats does
        $studentAverages = [];
        foreach ($students as $student) {
            $avg = $calcService->calculateTrimesterAverage(
                $student->id,
                $config->trimestre,
                $config->id
            );
            $studentAverages[$student->id] = $avg;
        }

        if (!empty($studentAverages)) {
            $classAverage = array_sum($studentAverages) / count($studentAverages);
            $passingCount = 0;
            foreach ($studentAverages as $avg) {
                if ($avg >= 10) {
                    $passingCount++;
                }
            }

            $this->line("📊 Class Statistics (from calculateClassStats):");
            $this->line("   • Class Average: " . round($classAverage, 2));
            $this->line("   • Passing (>= 10): $passingCount / " . $students->count());
            $this->line("   • Max: " . round(max($studentAverages), 2));
            $this->line("   • Min: " . round(min($studentAverages), 2));

            $this->newLine();
            $this->line("📊 Individual Averages returned in \$stats['avgs']:");
            foreach ($students->take(5) as $student) {
                $avg = $studentAverages[$student->id] ?? null;
                if ($avg !== null) {
                    $this->line("   " . str_pad($student->nom, 30) . ": " . round($avg, 2));
                }
            }
        }

        $this->newLine();
        $this->info('✅ Debug complete');
        return 0;
    }
}
