<?php

namespace App\Support;

class SecuritySanitizer
{
    /**
     * Sanitize cell value to prevent CSV / Excel Formula Injection (CSV Injection).
     * Disallows formulas starting with =, +, -, @, tab, or carriage return.
     */
    public static function sanitizeSpreadsheetCell(mixed $value): mixed
    {
        if (!is_string($value) || $value === '') {
            return $value;
        }

        // Direct check on first character for formula operators and tab/CR characters
        if (in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        // Also check if after leading spaces it begins with a formula trigger
        $trimmed = ltrim($value, " ");
        if ($trimmed !== '' && in_array($trimmed[0], ['=', '+', '-', '@'], true)) {
            return "'" . $value;
        }

        return $value;
    }

    /**
     * Sanitize an array of row data for spreadsheet or CSV output.
     */
    public static function sanitizeSpreadsheetRow(array $row): array
    {
        return array_map([self::class, 'sanitizeSpreadsheetCell'], $row);
    }

    /**
     * Sanitize filename to prevent directory traversal and header injection.
     */
    public static function sanitizeFilename(string $filename): string
    {
        $clean = str_replace(['..', '/', '\\', "\0", "\r", "\n"], '', $filename);
        return preg_replace('/[^a-zA-Z0-9_\-\. ]/', '', $clean) ?: 'file';
    }
}
