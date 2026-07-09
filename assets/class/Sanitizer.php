<?php
namespace App;

/**
 * Sanitizer - Input Sanitization and Output Escaping
 * 
 * Sanitizes user input and safely escapes output to prevent XSS and injection attacks
 */
class Sanitizer
{
    private $config;

    public function __construct()
    {
        $this->config = include __DIR__ . '/../config/security.php';
    }

    /**
     * Sanitize input data
     * 
     * @param mixed $input Input to sanitize
     * @param string $type Type of sanitization: 'string', 'email', 'url', 'int', 'array'
     * @return mixed Sanitized data
     */
    public static function sanitize($input, $type = 'string')
    {
        $sanitizer = new self();

        return match ($type) {
            'email' => $sanitizer->sanitizeEmail($input),
            'url' => $sanitizer->sanitizeUrl($input),
            'int' => $sanitizer->sanitizeInt($input),
            'integer' => $sanitizer->sanitizeInt($input),
            'bool' => $sanitizer->sanitizeBool($input),
            'boolean' => $sanitizer->sanitizeBool($input),
            'array' => $sanitizer->sanitizeArray($input),
            'string' => $sanitizer->sanitizeString($input),
            default => $sanitizer->sanitizeString($input)
        };
    }

    /**
     * Sanitize string input
     * 
     * Removes tags and special characters, optionally preserves formatting
     * 
     * @param string $input Input string
     * @param bool $allowMarkdown Allow basic markdown formatting
     * @return string Sanitized string
     */
    public function sanitizeString($input, $allowMarkdown = false)
    {
        if (!is_string($input)) {
            return '';
        }

        // Convert line breaks
        $input = nl2br($input, false);

        // Remove null bytes
        $input = str_replace(chr(0), '', $input);

        // Strip tags
        $input = strip_tags($input);

        // Trim whitespace
        $input = trim($input);

        // Collapse multiple spaces
        $input = preg_replace('/\s+/', ' ', $input);

        return $input;
    }

    /**
     * Sanitize email
     * 
     * @param string $email Email to sanitize
     * @return string Sanitized email
     */
    public function sanitizeEmail($email)
    {
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        $email = strtolower(trim($email));

        return $email;
    }

    /**
     * Sanitize URL
     * 
     * @param string $url URL to sanitize
     * @return string Sanitized URL
     */
    public function sanitizeUrl($url)
    {
        $url = filter_var($url, FILTER_SANITIZE_URL);
        
        // Verify it's actually a valid URL
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return '';
        }

        // Block potentially dangerous protocols
        $dangerousProtocols = ['javascript:', 'data:', 'vbscript:', 'file:'];
        $urlLower = strtolower($url);

        foreach ($dangerousProtocols as $protocol) {
            if (strpos($urlLower, $protocol) === 0) {
                return '';
            }
        }

        return $url;
    }

    /**
     * Sanitize integer
     * 
     * @param mixed $input Input to convert to integer
     * @param int $default Default value if conversion fails
     * @return int Integer value
     */
    public function sanitizeInt($input, $default = 0)
    {
        $int = filter_var($input, FILTER_SANITIZE_NUMBER_INT);
        
        if (!is_numeric($int)) {
            return $default;
        }

        return (int)$int;
    }

    /**
     * Sanitize boolean
     * 
     * @param mixed $input Input to convert to boolean
     * @return bool Boolean value
     */
    public function sanitizeBool($input)
    {
        return filter_var($input, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Sanitize array recursively
     * 
     * @param array $array Array to sanitize
     * @param string $defaultType Default sanitization type for values
     * @return array Sanitized array
     */
    public function sanitizeArray($array, $defaultType = 'string')
    {
        if (!is_array($array)) {
            return [];
        }

        $sanitized = [];

        foreach ($array as $key => $value) {
            // Sanitize key
            $key = $this->sanitizeString((string)$key);

            if (is_array($value)) {
                $sanitized[$key] = $this->sanitizeArray($value, $defaultType);
            } else {
                $sanitized[$key] = $this->sanitizeString((string)$value);
            }
        }

        return $sanitized;
    }

    /**
     * Escape HTML entities for output
     * 
     * @param string $string String to escape
     * @param string $encoding Character encoding
     * @return string Escaped string
     */
    public static function escape($string, $encoding = 'UTF-8')
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, $encoding);
    }

    /**
     * Escape for use in HTML attributes
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public static function escapeAttr($string)
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Escape for use in JavaScript strings
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public static function escapeJs($string)
    {
        $replacements = [
            '\\' => '\\\\',
            '"' => '\\"',
            "'" => "\\'",
            "\n" => '\\n',
            "\r" => '\\r',
            "\t" => '\\t',
            '<' => '\\x3c',
            '>' => '\\x3e',
            '&' => '\\x26',
            '/' => '\\/',
        ];

        foreach ($replacements as $search => $replace) {
            $string = str_replace($search, $replace, $string);
        }

        return $string;
    }

    /**
     * Escape for use in CSS
     * 
     * @param string $string String to escape
     * @return string Escaped string
     */
    public static function escapeCss($string)
    {
        $output = '';
        $chars = str_split($string);

        foreach ($chars as $char) {
            $code = ord($char);

            // Whitelist safe characters
            if (($code >= 48 && $code <= 57) || // 0-9
                ($code >= 65 && $code <= 90) || // A-Z
                ($code >= 97 && $code <= 122) || // a-z
                $char === '-' || $char === '_') {
                $output .= $char;
            } else {
                // Escape as hex
                $output .= '\\' . dechex($code) . ' ';
            }
        }

        return $output;
    }

    /**
     * Escape for use in JSON
     * 
     * @param mixed $data Data to encode as JSON
     * @return string JSON string
     */
    public static function escapeJson($data)
    {
        return json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
    }

    /**
     * Escape for use in URLs
     * 
     * @param string $string String to escape
     * @return string URL-encoded string
     */
    public static function escapeUrl($string)
    {
        return urlencode($string);
    }

    /**
     * Sanitize file path to prevent directory traversal
     * 
     * @param string $path Path to sanitize
     * @return string Sanitized path
     */
    public function sanitizePath($path)
    {
        // Remove null bytes
        $path = str_replace(chr(0), '', $path);

        // Remove directory traversal attempts
        while (preg_match('#[\./]\.[\./]#', $path)) {
            $path = preg_replace('#[\./]\.[\./]#', '/', $path);
        }

        // Remove leading slashes and dots
        $path = ltrim($path, './');

        // Only allow alphanumeric, dash, underscore, and forward slash
        $path = preg_replace('/[^a-zA-Z0-9\/_-]/', '', $path);

        return $path;
    }

    /**
     * Sanitize filename to prevent directory traversal
     * 
     * @param string $filename Filename to sanitize
     * @return string Sanitized filename
     */
    public function sanitizeFilename($filename)
    {
        // Remove null bytes
        $filename = str_replace(chr(0), '', $filename);

        // Remove directory separators
        $filename = str_replace(['/', '\\', ':', '*', '?', '"', '<', '>', '|'], '', $filename);

        // Remove leading/trailing dots and spaces
        $filename = trim($filename, '. ');

        // Convert to safe filename: allow alphanumeric, dash, underscore, dot
        $filename = preg_replace('/[^a-zA-Z0-9\._-]/', '', basename($filename));

        // Ensure not empty
        if (empty($filename)) {
            $filename = 'file_' . time();
        }

        return $filename;
    }

    /**
     * Sanitize SQL input (basic protection, use prepared statements instead!)
     * 
     * WARNING: This is for reference only. Always use prepared statements with PDO.
     * 
     * @param string $input Input to sanitize
     * @return string Escaped string
     * @deprecated Use prepared statements instead
     */
    public function sanitizeSql($input)
    {
        trigger_error('sanitizeSql is deprecated. Use prepared statements with PDO instead.', E_USER_DEPRECATED);
        
        // This is a basic fallback - prepared statements are the correct approach
        return addslashes($input);
    }

    /**
     * Get clean POST data
     * 
     * Returns all POST data sanitized
     * 
     * @param string $type Sanitization type
     * @return array Sanitized POST data
     */
    public function getCleanPost($type = 'string')
    {
        return $this->sanitizeArray($_POST, $type);
    }

    /**
     * Get clean GET data
     * 
     * Returns all GET data sanitized
     * 
     * @param string $type Sanitization type
     * @return array Sanitized GET data
     */
    public function getCleanGet($type = 'string')
    {
        return $this->sanitizeArray($_GET, $type);
    }

    /**
     * Get clean REQUEST data
     * 
     * @param string $type Sanitization type
     * @return array Sanitized REQUEST data
     */
    public function getCleanRequest($type = 'string')
    {
        return $this->sanitizeArray($_REQUEST, $type);
    }

    /**
     * Strip script tags and dangerous patterns
     * 
     * @param string $input Input to clean
     * @return string Cleaned string
     */
    public function stripScripts($input)
    {
        // Remove script tags
        $input = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $input);

        // Remove event handler attributes
        $patterns = [
            '/on\w+\s*=\s*["\'][^"\']*["\']/i',
            '/on\w+\s*=\s*[^\s>]*/i',
        ];

        foreach ($patterns as $pattern) {
            $input = preg_replace($pattern, '', $input);
        }

        return $input;
    }

    /**
     * Allow safe HTML tags only
     * 
     * @param string $input Input HTML
     * @param array $allowedTags Allowed tags (e.g., ['p', 'br', 'strong', 'em'])
     * @return string Filtered HTML
     */
    public function filterHtml($input, $allowedTags = [])
    {
        if (empty($allowedTags)) {
            // Default safe tags only
            $allowedTags = ['p', 'br', 'strong', 'em', 'u', 'h1', 'h2', 'h3', 'ul', 'ol', 'li'];
        }

        $allowed = '<' . implode('><', $allowedTags) . '>';

        return strip_tags($input, $allowed);
    }

    /**
     * Sanitize database column name
     * 
     * @param string $column Column name
     * @return string Sanitized column name
     */
    public function sanitizeColumn($column)
    {
        // Only allow alphanumeric, underscore, and backticks
        if (!preg_match('/^`?[a-zA-Z_][a-zA-Z0-9_]*`?$/', $column)) {
            throw new Exception('Invalid column name: ' . $column);
        }

        // Remove backticks if present
        $column = trim($column, '`');

        // Add backticks for safety
        return '`' . $column . '`';
    }

    /**
     * Normalize whitespace
     * 
     * @param string $input Input string
     * @return string Normalized string
     */
    public function normalizeWhitespace($input)
    {
        // Trim
        $input = trim($input);

        // Collapse multiple spaces
        $input = preg_replace('/  +/', ' ', $input);

        // Normalize line endings to \n
        $input = str_replace(["\r\n", "\r"], "\n", $input);

        return $input;
    }

    /**
     * Convert special characters to HTML entities for display
     * 
     * @param string $string String to convert
     * @return string String with HTML entities
     */
    public static function htmlEncode($string)
    {
        return htmlentities($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Convert HTML entities back to characters
     * 
     * @param string $string String to decode
     * @return string Decoded string
     */
    public static function htmlDecode($string)
    {
        return html_entity_decode($string, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    }

    /**
     * Validate and sanitize upload file
     * 
     * @param array $file File from $_FILES
     * @return array Sanitized file info with errors
     */
    public function sanitizeUpload($file)
    {
        $allowedConfig = $this->config['file_upload'];
        $errors = [];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors[] = 'Upload error: ' . $file['error'];
        }

        if ($file['size'] > $allowedConfig['max_size']) {
            $errors[] = 'File size exceeds maximum';
        }

        $filename = $this->sanitizeFilename($file['name']);
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedConfig['allowed_extensions'])) {
            $errors[] = 'File type not allowed';
        }

        return [
            'original_name' => $file['name'],
            'sanitized_name' => $filename,
            'tmp_name' => $file['tmp_name'],
            'size' => $file['size'],
            'extension' => $ext,
            'errors' => $errors,
            'valid' => empty($errors)
        ];
    }
}
