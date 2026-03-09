<?php

/**
 * GradeCalculationService
 *
 * Service de calcul des moyennes, rangs et appréciations
 * Phase 4 — Section 5.1 / Phase 6 — Système de Bulletins
 *
 * @package App\Services
 */

namespace App\Services;

use App\Models\Classe;
use App\Models\Mark;
use App\Models\Student;
use App\Models\Subject;
use App\Models\EstablishmentSetting;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GradeCalculationService
{
    /**
     * Calculer la moyenne d'un élève pour une séquence donnée,
     * en prenant en compte les coefficients des matières.
     *
     * @return array{moyenne: float|null, total_points: float, total_coef: float}
     */
    public function computeStudentAverage(
        Student $student,
        Classe  $class,
        int     $term,
        int     $sequence
    ): array {
        $marks = Mark::where([
            'student_id' => $student->id,
            'class_id'   => $class->id,
            'term'       => $term,
            'sequence'   => $sequence,
        ])
        ->whereNotNull('score')
        ->with('subject')
        ->get();

        if ($marks->isEmpty()) {
            return ['moyenne' => null, 'total_points' => 0, 'total_coef' => 0, 'rang' => null];
        }

        $totalPoints = 0.0;
        $totalCoef   = 0.0;

        foreach ($marks as $mark) {
            $coef         = (float)($mark->subject?->coefficient ?? 1);
            $totalPoints += (float)$mark->score * $coef;
            $totalCoef   += $coef;
        }

        $moyenne = $totalCoef > 0 ? round($totalPoints / $totalCoef, 2) : null;

        // Recalcul du rang dans la classe
        $rang = $this->computeRank($student, $class, $term, $sequence, $moyenne);

        return [
            'moyenne'       => $moyenne,
            'total_points'  => $totalPoints,
            'total_coef'    => $totalCoef,
            'rang'          => $rang,
        ];
    }

    /**
     * Calculer le rang d'un élève dans sa classe.
     */
    public function computeRank(
        Student $student,
        Classe  $class,
        int     $term,
        int     $sequence,
        ?float  $studentMoyenne = null
    ): int {
        // Charger toutes les moyennes de la classe (depuis la BDD ou recalcul)
        $classAverages = $this->getClassAverages($class, $term, $sequence);

        if ($studentMoyenne === null) {
            $computed = $this->computeStudentAverage($student, $class, $term, $sequence);
            $studentMoyenne = $computed['moyenne'];
        }

        if ($studentMoyenne === null) return 0;

        // Rang = nombre d'élèves avec une moyenne > celle de cet élève, + 1
        $rank = 1;
        foreach ($classAverages as $sid => $avg) {
            if ($sid !== $student->id && $avg > $studentMoyenne) {
                $rank++;
            }
        }

        return $rank;
    }

    /**
     * Récupérer toutes les moyennes d'une classe pour une séquence.
     *
     * @return array<int, float> [$studentId => moyenne]
     */
    public function getClassAverages(Classe $class, int $term, int $sequence): array
    {
        $rows = DB::table('marks')
            ->join('subjects', 'marks.subject_id', '=', 'subjects.id')
            ->where('marks.class_id', $class->id)
            ->where('marks.term', $term)
            ->where('marks.sequence', $sequence)
            ->whereNotNull('marks.score')
            ->select(
                'marks.student_id',
                DB::raw('SUM(marks.score * COALESCE(subjects.coefficient, 1)) as total_points'),
                DB::raw('SUM(COALESCE(subjects.coefficient, 1)) as total_coef')
            )
            ->groupBy('marks.student_id')
            ->get();

        $averages = [];
        foreach ($rows as $row) {
            if ($row->total_coef > 0) {
                $averages[$row->student_id] = round($row->total_points / $row->total_coef, 2);
            }
        }

        return $averages;
    }

    /**
     * Calculer et mettre à jour les rangs de TOUS les élèves d'une classe.
     * Appelé après chaque sauvegarde de note (asynchrone recommandé).
     */
    public function recalculateClassRanks(Classe $class, int $term, int $sequence): void
    {
        $averages = $this->getClassAverages($class, $term, $sequence);

        // Trier par moyenne décroissante
        arsort($averages);

        $rank = 1;
        $prevMoyenne = null;
        $prevRank    = 1;
        $i           = 0;

        foreach ($averages as $studentId => $moyenne) {
            $i++;
            if ($prevMoyenne !== null && $moyenne < $prevMoyenne) {
                $prevRank = $rank;
            }
            $currentRank = ($prevMoyenne !== null && $moyenne === $prevMoyenne) ? $prevRank : $rank;
            $prevMoyenne = $moyenne;
            $rank        = $i + 1;

            // Mettre à jour le bulletin
            \App\Models\Bulletin::where([
                'student_id' => $studentId,
                'class_id'   => $class->id,
                'term'       => $term,
                'sequence'   => $sequence,
            ])->update([
                'rang'    => $currentRank,
                'moyenne' => $moyenne,
            ]);
        }
    }

    /**
     * Suggérer une appréciation selon le barème configuré dans EstablishmentSetting.
     */
    public function suggestAppreciation(float $moyenne, string $locale = 'fr'): array
    {
        $settings = EstablishmentSetting::getInstance();

        // Barème par défaut ou configuré
        $scale = [
            ['min' => 19, 'max' => 20, 'fr' => 'Excellent',       'en' => 'Excellent',       'color' => '#8b5cf6'],
            ['min' => 16, 'max' => 19, 'fr' => 'Très Bien',        'en' => 'Very Good',       'color' => '#10b981'],
            ['min' => 13, 'max' => 16, 'fr' => 'Bien',              'en' => 'Good',            'color' => '#3b82f6'],
            ['min' => 10, 'max' => 13, 'fr' => 'Assez Bien',        'en' => 'Fair',            'color' => '#f59e0b'],
            ['min' => 0,  'max' => 10, 'fr' => 'Insuffisant',       'en' => 'Insufficient',    'color' => '#ef4444'],
        ];

        // Override avec barème personnalisé si configuré
        if ($settings->grade_label_0) {
            $scale = $this->buildCustomScale($settings);
        }

        foreach ($scale as $entry) {
            if ($moyenne >= $entry['min'] && $moyenne <= $entry['max']) {
                return [
                    'label' => $locale === 'fr' ? $entry['fr'] : $entry['en'],
                    'color' => $entry['color'],
                ];
            }
        }

        return ['label' => '—', 'color' => '#94a3b8'];
    }

    /**
     * Construire le barème depuis les settings admin.
     */
    private function buildCustomScale(EstablishmentSetting $settings): array
    {
        $scale = [];
        $ranges = [
            0 => [0, 10],
            1 => [10, 12],
            2 => [12, 14],
            3 => [14, 16],
            4 => [16, 20],
        ];
        $colors = ['#ef4444', '#f59e0b', '#3b82f6', '#10b981', '#8b5cf6'];

        for ($i = 0; $i <= 4; $i++) {
            $label = $settings->{"grade_label_{$i}"};
            if ($label) {
                $scale[] = [
                    'min'   => $ranges[$i][0],
                    'max'   => $ranges[$i][1],
                    'fr'    => $label,
                    'en'    => $label,
                    'color' => $colors[$i],
                ];
            }
        }

        return $scale ?: $this->defaultScale();
    }

    /**
     * Calculer la moyenne d'une matière pour toute la classe.
     */
    public function computeSubjectClassAverage(
        int $subjectId,
        int $classId,
        int $term,
        int $sequence
    ): ?float {
        $result = Mark::where([
            'subject_id' => $subjectId,
            'class_id'   => $classId,
            'term'       => $term,
            'sequence'   => $sequence,
        ])
        ->whereNotNull('score')
        ->avg('score');

        return $result !== null ? round((float)$result, 2) : null;
    }

    /**
     * Calcul de la taux de complétion par matière (pour Prof Principal).
     * Retourne [subjectId => ['name', 'filled', 'total', 'pct']]
     */
    public function getCompletionBySubject(Classe $class, int $term, int $sequence): array
    {
        $studentCount = $class->students()->count();
        if ($studentCount === 0) return [];

        $subjects = $class->subjects()->get();
        $result   = [];

        foreach ($subjects as $subject) {
            $filled = Mark::where([
                'subject_id' => $subject->id,
                'class_id'   => $class->id,
                'term'       => $term,
                'sequence'   => $sequence,
            ])->whereNotNull('score')->count();

            $result[$subject->id] = [
                'name'   => $subject->name,
                'filled' => $filled,
                'total'  => $studentCount,
                'pct'    => $studentCount > 0 ? (int)round($filled / $studentCount * 100) : 0,
            ];
        }

        return $result;
    }

    /**
     * Calcul note anglophone (lettre A-F ou pourcentage) pour section anglophone.
     */
    public function convertToAngloponeGrade(float $score20, string $format = 'letter'): string
    {
        $pct = $score20 / 20 * 100;

        if ($format === 'percentage') {
            return round($pct) . '%';
        }

        // Letter grade
        if ($pct >= 90) return 'A+';
        if ($pct >= 80) return 'A';
        if ($pct >= 75) return 'B+';
        if ($pct >= 70) return 'B';
        if ($pct >= 65) return 'C+';
        if ($pct >= 60) return 'C';
        if ($pct >= 55) return 'D+';
        if ($pct >= 50) return 'D';
        return 'F';
    }

    private function defaultScale(): array
    {
        return [
            ['min'=>19,'max'=>20,'fr'=>'Excellent','en'=>'Excellent','color'=>'#8b5cf6'],
            ['min'=>16,'max'=>19,'fr'=>'Très Bien','en'=>'Very Good','color'=>'#10b981'],
            ['min'=>13,'max'=>16,'fr'=>'Bien','en'=>'Good','color'=>'#3b82f6'],
            ['min'=>10,'max'=>13,'fr'=>'Assez Bien','en'=>'Fair','color'=>'#f59e0b'],
            ['min'=>0,'max'=>10,'fr'=>'Insuffisant','en'=>'Insufficient','color'=>'#ef4444'],
        ];
    }
}
