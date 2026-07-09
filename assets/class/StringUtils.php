<?php
namespace App;

class StringUtils
{
    /**
     * Truncates a string to a specified length and adds an ellipsis if truncated
     *
     * @param string $string The string to truncate
     * @param int $length The maximum length of the string
     * @param string $ellipsis The string to append if truncated
     * @return string The truncated string
     */
    public static function truncate(string $string, int $length = 100, string $ellipsis = '...'): string
    {
        if (mb_strlen($string) <= $length) {
            return $string;
        }

        return mb_substr($string, 0, $length) . $ellipsis;
    }

    /**
     * Converts a string to title case
     *
     * @param string $string The string to convert
     * @return string The title-cased string
     */
    public static function toTitleCase(string $string): string
    {
        return mb_convert_case($string, MB_CASE_TITLE, 'UTF-8');
    }
}