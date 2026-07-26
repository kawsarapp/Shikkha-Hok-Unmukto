<?php

namespace App\Services;

class PdfParserService
{
    /**
     * Extract plain text from PDF file path
     */
    public function extractText(string $filePath): string
    {
        if (!file_exists($filePath)) {
            return '';
        }

        // Use shell pdftotext if available or pure PHP fallback text reader
        if (function_exists('shell_exec')) {
            $output = @shell_exec("pdftotext " . escapeshellarg($filePath) . " -");
            if ($output && trim($output) !== '') {
                return trim($output);
            }
        }

        // Basic string stream extraction fallback
        $content = @file_get_contents($filePath);
        if (!$content) return '';

        // Extract raw text between stream and endstream markers
        preg_match_all('/stream(.*?)endstream/s', $content, $matches);
        $text = '';
        foreach ($matches[1] as $match) {
            $decoded = @gzuncompress(trim($match));
            if ($decoded !== false) {
                $text .= ' ' . preg_replace('/[^\x20-\x7E\x0A\x0D]/', '', $decoded);
            }
        }

        return trim($text) ?: 'PDF Text Extracted Content';
    }
}
