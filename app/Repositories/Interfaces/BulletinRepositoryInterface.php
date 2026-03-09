<?php

namespace App\Repositories\Interfaces;

use App\Models\ReportCard;
use Illuminate\Support\Collection;

/**
 * BulletinRepositoryInterface (ReportCard)
 * 
 * SOLID - Repository Pattern for Bulletin/ReportCard data access
 */
interface BulletinRepositoryInterface
{
    /**
     * Get a bulletin by ID.
     *
     * @param int $id
     * @return ReportCard|null
     */
    public function find(int $id): ?ReportCard;

    /**
     * Get a bulletin for a specific student and sequence.
     *
     * @param int $studentId
     * @param int $sequence
     * @param int $academicYear
     * @return ReportCard|null
     */
    public function getStudentBulletin(int $studentId, int $sequence, int $academicYear): ?ReportCard;

    /**
     * Get all bulletins for a class.
     *
     * @param int $classId
     * @param int $sequence
     * @param int $academicYear
     * @return Collection
     */
    public function getClassBulletins(int $classId, int $sequence, int $academicYear): Collection;

    /**
     * Create a new bulletin.
     *
     * @param array $data
     * @return ReportCard
     */
    public function create(array $data): ReportCard;

    /**
     * Update a bulletin.
     *
     * @param ReportCard $bulletin
     * @param array $data
     * @return ReportCard
     */
    public function update(ReportCard $bulletin, array $data): ReportCard;

    /**
     * Lock a bulletin (prevent further editing).
     *
     * @param ReportCard $bulletin
     * @return ReportCard
     */
    public function lock(ReportCard $bulletin): ReportCard;

    /**
     * Check if bulletin is locked.
     *
     * @param ReportCard $bulletin
     * @return bool
     */
    public function isLocked(ReportCard $bulletin): bool;

    /**
     * Generate bulletin PDF export.
     *
     * @param ReportCard $bulletin
     * @return string Path to PDF file
     */
    public function generatePDF(ReportCard $bulletin): string;

    /**
     * Export multiple bulletins as a single PDF.
     *
     * @param Collection $bulletins
     * @return string Path to PDF file
     */
    public function exportMultiplePDF(Collection $bulletins): string;

    /**
     * Get bulletins by status.
     *
     * @param string $status 'draft', 'completed', 'locked'
     * @param int|null $classId
     * @return Collection
     */
    public function getByStatus(string $status, ?int $classId = null): Collection;
}
