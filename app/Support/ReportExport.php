<?php

namespace App\Support;

/**
 * CSV and PDF downloads for report tables. Rows are arrays of plain values (already formatted for PDF).
 */
class ReportExport
{
    public static function csv($filename, array $header, $rows)
    {
        return response()->streamDownload(function () use ($header, $rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, $header);
            foreach ($rows as $row) {
                // a leading = + - @ would be run as a formula by spreadsheet apps
                fputcsv($out, array_map(function ($cell) {
                    $cell = (string) $cell;
                    return preg_match('/^[=+\-@\t\r]/', $cell) && !is_numeric($cell) ? "'" . $cell : $cell;
                }, (array) $row));
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * $options: subtitle, totals (row array), align (column index => 'right'), landscape (bool), summary (label => value)
     */
    public static function pdf($filename, $title, array $header, $rows, array $options = [])
    {
        $settings = settings();
        return \Barryvdh\DomPDF\Facade\Pdf::loadView('report.pdf_table', [
            'title' => $title,
            'subtitle' => $options['subtitle'] ?? null,
            'header' => $header,
            'rows' => $rows,
            'totals' => $options['totals'] ?? null,
            'align' => $options['align'] ?? [],
            'summary' => $options['summary'] ?? [],
            'settings' => $settings,
        ])->setPaper('a4', !empty($options['landscape']) ? 'landscape' : 'portrait')->download($filename);
    }
}
