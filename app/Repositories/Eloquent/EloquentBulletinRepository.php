<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Interfaces\BulletinRepositoryInterface;
use App\Models\ReportCard;
use Illuminate\Support\Collection;

/**
 * EloquentBulletinRepository
 * 
 * SOLID - Concrete implementation of BulletinRepositoryInterface
 */
class EloquentBulletinRepository implements BulletinRepositoryInterface
{
    /**
     * Get a bulletin by ID.
     *
     * @param int $id
     * @return ReportCard|null
     */
    public function find(int $id): ?ReportCard
    {
        return ReportCard::with(['student', 'classe', 'grades'])
            ->find($id);
    }

    /**
     * Get a bulletin for a specific student and sequence.
     *
     * @param int $studentId
     * @param int $sequence
     * @param int $academicYear
     * @return ReportCard|null
     */
    public function getStudentBulletin(int $studentId, int $sequence, int $academicYear): ?ReportCard
    {
        return ReportCard::where('student_id', $studentId)
            ->where('sequence', $sequence)
            ->where('academic_year', $academicYear)
            ->with('grades')
            ->first();
    }

    /**
     * Get all bulletins for a class.
     *
     * @param int $classId
     * @param int $sequence
     * @param int $academicYear
     * @return Collection
     */
    public function getClassBulletins(int $classId, int $sequence, int $academicYear): Collection
    {
        return ReportCard::whereHas('student', function ($query) use ($classId) {
                $query->where('classe_id', $classId);
            })
            ->where('sequence', $sequence)
            ->where('academic_year', $academicYear)
            ->with(['student', 'grades'])
            ->get();
    }

    /**
     * Create a new bulletin.
     *
     * @param array $data
     * @return ReportCard
     */
    public function create(array $data): ReportCard
    {
        return ReportCard::create($data);
    }

    /**
     * Update a bulletin.
     *
     * @param ReportCard $bulletin
     * @param array $data
     * @return ReportCard
     */
    public function update(ReportCard $bulletin, array $data): ReportCard
    {
        $bulletin->update($data);
        return $bulletin->refresh();
    }

    /**
     * Lock a bulletin (prevent further editing).
     *
     * @param ReportCard $bulletin
     * @return ReportCard
     */
    public function lock(ReportCard $bulletin): ReportCard
    {
        $bulletin->update([
            'is_locked' => true,
            'locked_at' => now(),
        ]);
        return $bulletin->refresh();
    }

    /**
     * Check if bulletin is locked.
     *
     * @param ReportCard $bulletin
     * @return bool
     */
    public function isLocked(ReportCard $bulletin): bool
    {
        return $bulletin->is_locked === true;
    }

    /**
     * Generate bulletin PDF export.
     *
     * @param ReportCard $bulletin
     * @return string Path to PDF file
     */
    public function generatePDF(ReportCard $bulletin): string
    {
        // Delegate to PDF generation service
        // This is implemented in a dedicated service
        throw new \Exception('Use BulletinGenerationService for PDF operations');
    }

    /**
     * Export multiple bulletins as a single PDF.
     *
     * @param Collection $bulletins
     * @return string Path to PDF file
     */
    public function exportMultiplePDF(Collection $bulletins): string
    {
        // Delegate to PDF generation service
        throw new \Exception('Use BulletinGenerationService for PDF operations');
    }

    /**
     * Get bulletins by status.
     *
     * @param string $status 'draft', 'completed', 'locked'
     * @param int|null $classId
     * @return Collection
     */
    public function getByStatus(string $status, ?int $classId = null): Collection
    {
        $query = ReportCard::where('status', $status);

        if ($classId) {
            $query->whereHas('student', function ($q) use ($classId) {
                $q->where('classe_id', $classId);
            });
        }

        return $query->with(['student', 'grades'])
            ->get();
    }
}
