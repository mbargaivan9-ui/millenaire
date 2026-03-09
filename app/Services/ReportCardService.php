<?php

namespace App\Services;

use App\Models\Teacher;
use App\Models\Classe;
use App\Models\ReportCard;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

/**
 * ReportCardService
 * Handles all business logic related to report cards
 * Follows Single Responsibility Principle
 */
class ReportCardService
{
    /**
     * Get principal classes for a teacher
     * 
     * @param Teacher $teacher
     * @return Classe|null
     * @throws \Exception
     */
    public function getPrincipalClass(Teacher $teacher): ?Classe
    {
        if (!$teacher->is_prof_principal) {
            throw new \Exception('Teacher is not a principal teacher');
        }

        return $teacher->principalClasses()->first();
    }

    /**
     * Get paginated report cards for a class
     * 
     * @param Classe $classe
     * @param string|null $term
     * @param int $perPage
    * @return LengthAwarePaginator
     */
    public function getReportCards(Classe $classe, ?string $term = null, int $perPage = 50): LengthAwarePaginator
    {
        $query = ReportCard::where('class_id', $classe->id);

        if ($term) {
            $query->where('term', $term);
        }

        return $query->with('student.user')
            ->orderByDesc('term_average')
            ->paginate($perPage);
    }

    /**
     * Validate teacher authorization for report card
     * 
     * @param Teacher $teacher
     * @param ReportCard $reportCard
     * @return bool
     * @throws \Exception
     */
    public function validateTeacherAuthorization(Teacher $teacher, ReportCard $reportCard): bool
    {
        if (!$teacher->is_prof_principal) {
            throw new \Exception('Teacher is not a principal teacher');
        }

        if (!$reportCard->classe) {
            throw new \Exception('Report card has no associated class');
        }

        if ($reportCard->classe->prof_principal_id !== $teacher->user_id) {
            throw new \Exception('Teacher is not authorized for this report card');
        }

        return true;
    }

    /**
     * Update report card with validation data
     * 
     * @param ReportCard $reportCard
     * @param array $validatedData
     * @return bool
     */
    public function updateReportCard(ReportCard $reportCard, array $validatedData): bool
    {
        return $reportCard->update($validatedData);
    }

    /**
     * Get available terms for report cards
     * 
     * @return array
     */
    public function getAvailableTerms(): array
    {
        return ['Term1', 'Term2', 'Term3'];
    }
}
