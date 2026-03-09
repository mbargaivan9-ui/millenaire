<?php

namespace App\Helpers;

class Helper
{
    /**
     * Sanitize filename to prevent path traversal attacks
     *
     * @param string $filename
     * @return string
     */
    private static function sanitizeFilename(string $filename): string
    {
        // Remove any path separators and dangerous characters
        $filename = basename($filename);
        // Remove any characters that are not alphanumeric, dash, underscore, or dot
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '', $filename);
        // Ensure filename is not empty
        return empty($filename) ? 'export' : $filename;
    }

    /**
     * Export data to Excel/CSV format
     * 
     * @param mixed $data
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public static function exportToExcel($data, string $filename): \Illuminate\Http\Response
    {
        // Sanitize filename to prevent path traversal
        $filename = self::sanitizeFilename($filename);
        // Placeholder implementation - would require a package like maatwebsite/excel
        $filepath = storage_path("exports/{$filename}.xlsx");
        
        if (!file_exists($filepath)) {
            return response()->json(['error' => 'File not found'], 404);
        }
        
        return response()->download($filepath);
    }

    /**
     * Export data to CSV format
     * 
     * @param mixed $data
     * @param string $filename
     * @return \Illuminate\Http\Response
     */
    public static function exportToCSV($data, string $filename): \Illuminate\Http\Response
    {
        // Sanitize filename to prevent path traversal
        $filename = self::sanitizeFilename($filename);
        
        // Create CSV file
        $filepath = storage_path("exports/{$filename}.csv");
        
        if (!is_dir(dirname($filepath))) {
            mkdir(dirname($filepath), 0755, true);
        }

        $file = fopen($filepath, 'w');
        
        if ($file === false) {
            return response()->json(['error' => 'Cannot create file'], 500);
        }
        
        if ($data instanceof \Illuminate\Database\Eloquent\Collection) {
            $data = $data->toArray();
        } else if (!is_array($data)) {
            $data = (array)$data;
        }

        if (!empty($data)) {
            // Write header
            $firstRow = reset($data);
            if (is_array($firstRow) || is_object($firstRow)) {
                fputcsv($file, array_keys((array)$firstRow));
                
                // Write data
                foreach ($data as $row) {
                    fputcsv($file, (array)$row);
                }
            }
        }

        fclose($file);
        
        return response()->download($filepath, "{$filename}.csv", [
            'Content-Type' => 'text/csv',
        ])->deleteFileAfterSend(true);
    }
}
