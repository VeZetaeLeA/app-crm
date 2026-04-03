<?php
namespace App\Utils;

class CsvExporter
{
    /**
     * Export array data to CSV and stream it to the browser.
     *
     * @param string $filename
     * @param array $headers
     * @param array $data
     * @return void
     */
    public static function export(string $filename, array $headers, array $data)
    {
        // Set headers for download
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        
        // Add CORS headers if cross-domain download is needed
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Expose-Headers: Content-Disposition');

        $output = fopen('php://output', 'w');
        
        // Fix for Excel UTF-8 encoding
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Add headers row
        fputcsv($output, $headers);

        // Add data rows
        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        fclose($output);
        exit;
    }
}
