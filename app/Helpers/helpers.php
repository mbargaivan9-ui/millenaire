<?php
/**
 * APP HELPER FUNCTIONS
 *
 * Global helper functions used throughout the application
 */

/**
 * Get the current academic year
 *
 * Academic year runs from September to August.
 * If current month is September or later, the year is the current year.
 * Otherwise, it's the previous year.
 *
 * Example: In March 2026, returns "2025-2026"
 *          In November 2026, returns "2026-2027"
 *
 * @return string Academic year in format "YYYY-YYYY" (2025-2026)
 */
if (!function_exists('currentAcademicYear')) {
    function currentAcademicYear(): string {
        $year = now()->month >= 9 ? now()->year : now()->year - 1;
        return $year . '-' . ($year + 1);
    }
}
