<?php

namespace App\Services;

class GradeCalculationService
{
    /**
     * Calculate grade from marks
     */
    public function calculateGrade($marks, $maxMarks = 20)
    {
        if ($maxMarks == 0) return 0;
        return ($marks / $maxMarks) * 20;
    }

    /**
     * Get grade letter from numeric grade
     */
    public function getGradeLetter($grade)
    {
        if ($grade >= 16) return 'A';
        if ($grade >= 14) return 'B';
        if ($grade >= 12) return 'C';
        if ($grade >= 10) return 'D';
        return 'F';
    }
}
