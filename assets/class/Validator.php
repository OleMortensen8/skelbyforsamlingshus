<?php
namespace App;

/**
 * Validator - Input Validation System
 * 
 * Validates user input against security rules and type requirements
 * Provides comprehensive validation methods and error reporting
 */
class Validator
{
    private $config;
    private $errors = [];
    private $data = [];

    public function __construct()
    {
        $this->config = include __DIR__ . '/../config/security.php';
    }

    /**
     * Create validator instance with data
     * 
     * @param array $data Data to validate
     * @return self
     */
    public static function make($data = [])
    {
        $validator = new self();
        $validator->data = $data;
        return $validator;
    }

    /**
     * Set data to validate
     * 
     * @param array $data Data array
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Get validation errors
     * 
     * @return array Array of errors
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Check if validation passed
     * 
     * @return bool True if no errors
     */
    public function passes()
    {
        return empty($this->errors);
    }

    /**
     * Check if validation failed
     * 
     * @return bool True if errors exist
     */
    public function fails()
    {
        return !$this->passes();
    }

    /**
     * Get first error for a field
     * 
     * @param string $field Field name
     * @return string|null Error message or null
     */
    public function getFirstError($field)
    {
        return $this->errors[$field][0] ?? null;
    }

    /**
     * Get all errors for a field
     * 
     * @param string $field Field name
     * @return array Array of error messages
     */
    public function getErrors($field = null)
    {
        if ($field === null) {
            return $this->errors;
        }

        return $this->errors[$field] ?? [];
    }

    /**
     * Validate email address
     * 
     * @param string $field Field name
     * @param string|null $email Email to validate (or use data)
     * @return $this
     */
    public function email($field, $email = null)
    {
        if ($email === null) {
            $email = $this->data[$field] ?? null;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->addError($field, "Invalid email format");
        }

        return $this;
    }

    /**
     * Validate required field
     * 
     * @param string $field Field name
     * @return $this
     */
    public function required($field)
    {
        $value = $this->data[$field] ?? null;

        if ($value === null || $value === '' || (is_array($value) && empty($value))) {
            $this->addError($field, "This field is required");
        }

        return $this;
    }

    /**
     * Validate minimum length
     * 
     * @param string $field Field name
     * @param int $length Minimum length
     * @return $this
     */
    public function minLength($field, $length)
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && strlen($value) < $length) {
            $this->addError($field, "Must be at least {$length} characters");
        }

        return $this;
    }

    /**
     * Validate maximum length
     * 
     * @param string $field Field name
     * @param int $length Maximum length
     * @return $this
     */
    public function maxLength($field, $length)
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && strlen($value) > $length) {
            $this->addError($field, "Cannot exceed {$length} characters");
        }

        return $this;
    }

    /**
     * Validate field is a valid URL
     * 
     * @param string $field Field name
     * @return $this
     */
    public function url($field)
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && !filter_var($value, FILTER_VALIDATE_URL)) {
            $this->addError($field, "Invalid URL format");
        }

        return $this;
    }

    /**
     * Validate field is a valid IP address
     * 
     * @param string $field Field name
     * @param string $type Type: 'ipv4', 'ipv6', or 'both'
     * @return $this
     */
    public function ip($field, $type = 'both')
    {
        $value = $this->data[$field] ?? null;

        if ($value === null) {
            return $this;
        }

        $flags = match ($type) {
            'ipv4' => FILTER_FLAG_IPV4,
            'ipv6' => FILTER_FLAG_IPV6,
            default => FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6
        };

        if (!filter_var($value, FILTER_VALIDATE_IP, $flags)) {
            $this->addError($field, "Invalid IP address");
        }

        return $this;
    }

    /**
     * Validate field contains only integers
     * 
     * @param string $field Field name
     * @return $this
     */
    public function integer($field)
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && !is_numeric($value) || (is_numeric($value) && strpos($value, '.') !== false)) {
            $this->addError($field, "Must be an integer");
        }

        return $this;
    }

    /**
     * Validate field is numeric
     * 
     * @param string $field Field name
     * @return $this
     */
    public function numeric($field)
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && !is_numeric($value)) {
            $this->addError($field, "Must be a number");
        }

        return $this;
    }

    /**
     * Validate field is within numeric range
     * 
     * @param string $field Field name
     * @param int $min Minimum value
     * @param int $max Maximum value
     * @return $this
     */
    public function between($field, $min, $max)
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null) {
            if (!is_numeric($value) || $value < $min || $value > $max) {
                $this->addError($field, "Must be between {$min} and {$max}");
            }
        }

        return $this;
    }

    /**
     * Validate field matches regex pattern
     * 
     * @param string $field Field name
     * @param string $pattern Regex pattern
     * @return $this
     */
    public function regex($field, $pattern)
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && !preg_match($pattern, $value)) {
            $this->addError($field, "Invalid format");
        }

        return $this;
    }

    /**
     * Validate field is alphanumeric
     * 
     * @param string $field Field name
     * @param bool $allowDashes Allow dashes
     * @param bool $allowUnderscores Allow underscores
     * @return $this
     */
    public function alphanumeric($field, $allowDashes = false, $allowUnderscores = false)
    {
        $value = $this->data[$field] ?? null;

        if ($value === null) {
            return $this;
        }

        $pattern = '^[a-zA-Z0-9';
        if ($allowDashes) $pattern .= '-';
        if ($allowUnderscores) $pattern .= '_';
        $pattern .= ']+$';

        if (!preg_match('/' . $pattern . '/', $value)) {
            $this->addError($field, "Contains invalid characters");
        }

        return $this;
    }

    /**
     * Validate field value is in allowed list
     * 
     * @param string $field Field name
     * @param array $allowed Allowed values
     * @return $this
     */
    public function in($field, $allowed)
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && !in_array($value, $allowed, true)) {
            $this->addError($field, "Invalid value");
        }

        return $this;
    }

    /**
     * Validate field value is not in forbidden list
     * 
     * @param string $field Field name
     * @param array $forbidden Forbidden values
     * @return $this
     */
    public function notIn($field, $forbidden)
    {
        $value = $this->data[$field] ?? null;

        if ($value !== null && in_array($value, $forbidden, true)) {
            $this->addError($field, "This value is not allowed");
        }

        return $this;
    }

    /**
     * Validate field matches another field
     * 
     * @param string $field Field name
     * @param string $matchField Field to match against
     * @return $this
     */
    public function matches($field, $matchField)
    {
        $value = $this->data[$field] ?? null;
        $matchValue = $this->data[$matchField] ?? null;

        if ($value !== $matchValue) {
            $this->addError($field, "Does not match {$matchField}");
        }

        return $this;
    }

    /**
     * Validate file upload
     * 
     * @param string $field Form field name
     * @param array $allowedTypes Allowed MIME types
     * @param int $maxSize Maximum file size in bytes
     * @return $this
     */
    public function file($field, $allowedTypes = [], $maxSize = null)
    {
        if (!isset($_FILES[$field])) {
            $this->addError($field, "No file uploaded");
            return $this;
        }

        $file = $_FILES[$field];
        $allowedConfig = $this->config['file_upload'];

        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $this->addError($field, $this->getUploadErrorMessage($file['error']));
            return $this;
        }

        // Check file size
        $fileSize = $maxSize ?? $allowedConfig['max_size'];
        if ($file['size'] > $fileSize) {
            $this->addError($field, "File size exceeds " . ($fileSize / 1024 / 1024) . "MB");
            return $this;
        }

        // Check MIME type
        if (!empty($allowedTypes)) {
            $mimeType = mime_content_type($file['tmp_name']);
            if (!in_array($mimeType, $allowedTypes)) {
                $this->addError($field, "File type not allowed");
                return $this;
            }
        }

        // Check against allowed file types config
        $allowedExtensions = $allowedConfig['allowed_extensions'];
        if (!empty($allowedExtensions)) {
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, $allowedExtensions)) {
                $this->addError($field, "File extension not allowed");
            }
        }

        return $this;
    }

    /**
     * Validate date string
     * 
     * @param string $field Field name
     * @param string $format Date format (e.g., 'Y-m-d')
     * @return $this
     */
    public function date($field, $format = 'Y-m-d')
    {
        $value = $this->data[$field] ?? null;

        if ($value === null) {
            return $this;
        }

        $parsed = DateTime::createFromFormat($format, $value);
        $isValid = $parsed && $parsed->format($format) === $value;

        if (!$isValid) {
            $this->addError($field, "Invalid date format");
        }

        return $this;
    }

    /**
     * Custom validation callback
     * 
     * @param string $field Field name
     * @param callable $callback Validation callback
     * @param string $message Error message if validation fails
     * @return $this
     */
    public function custom($field, $callback, $message = 'Validation failed')
    {
        $value = $this->data[$field] ?? null;

        if (!$callback($value, $this->data)) {
            $this->addError($field, $message);
        }

        return $this;
    }

    /**
     * Validate field is a valid password
     * 
     * Uses PasswordManager for comprehensive password validation
     * 
     * @param string $field Field name
     * @return $this
     */
    public function password($field)
    {
        $value = $this->data[$field] ?? null;

        if ($value === null) {
            return $this;
        }

        $passwordManager = new PasswordManager();
        $errors = $passwordManager->validatePolicy($value);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->addError($field, $error);
            }
        }

        return $this;
    }

    /**
     * Get upload error message
     * 
     * @param int $errorCode Upload error code
     * @return string Error message
     */
    private function getUploadErrorMessage($errorCode)
    {
        return match ($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File exceeds server limits',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit',
            UPLOAD_ERR_PARTIAL => 'File upload was incomplete',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Server error: no temp directory',
            UPLOAD_ERR_CANT_WRITE => 'Server error: cannot write file',
            UPLOAD_ERR_EXTENSION => 'File type extension blocked',
            default => 'Unknown upload error'
        };
    }

    /**
     * Add error message
     * 
     * @param string $field Field name
     * @param string $message Error message
     */
    private function addError($field, $message)
    {
        if (!isset($this->errors[$field])) {
            $this->errors[$field] = [];
        }

        $this->errors[$field][] = $message;
    }

    /**
     * Validate multiple fields at once
     * 
     * @param array $rules Array of field => validation rules
     * @return $this
     */
    public function validate($rules)
    {
        foreach ($rules as $field => $fieldRules) {
            // Parse rule string or array
            if (is_string($fieldRules)) {
                $fieldRules = array_map('trim', explode('|', $fieldRules));
            }

            foreach ($fieldRules as $rule) {
                // Parse rule with parameters: rule_name:param1,param2
                if (strpos($rule, ':') !== false) {
                    [$ruleName, $params] = explode(':', $rule, 2);
                    $params = array_map('trim', explode(',', $params));
                } else {
                    $ruleName = $rule;
                    $params = [];
                }

                // Apply rule
                switch ($ruleName) {
                    case 'required':
                        $this->required($field);
                        break;
                    case 'email':
                        $this->email($field);
                        break;
                    case 'min':
                        $this->minLength($field, (int)$params[0]);
                        break;
                    case 'max':
                        $this->maxLength($field, (int)$params[0]);
                        break;
                    case 'url':
                        $this->url($field);
                        break;
                    case 'ip':
                        $this->ip($field, $params[0] ?? 'both');
                        break;
                    case 'integer':
                        $this->integer($field);
                        break;
                    case 'numeric':
                        $this->numeric($field);
                        break;
                    case 'between':
                        $this->between($field, (int)$params[0], (int)$params[1]);
                        break;
                    case 'in':
                        $this->in($field, $params);
                        break;
                    case 'password':
                        $this->password($field);
                        break;
                }
            }
        }

        return $this;
    }

    /**
     * Get HTML5 validation attributes
     * 
     * @param string $field Field name
     * @param array $rules Validation rules
     * @return string HTML attributes string
     */
    public static function getHtml5Attributes($field, $rules = [])
    {
        $attributes = [];

        foreach ($rules as $rule) {
            if (strpos($rule, ':') !== false) {
                [$ruleName, $params] = explode(':', $rule, 2);
            } else {
                $ruleName = $rule;
                $params = null;
            }

            switch ($ruleName) {
                case 'required':
                    $attributes[] = 'required';
                    break;
                case 'email':
                    $attributes[] = 'type="email"';
                    break;
                case 'numeric':
                    $attributes[] = 'type="number"';
                    break;
                case 'min':
                    $attributes[] = 'minlength="' . htmlspecialchars($params) . '"';
                    break;
                case 'max':
                    $attributes[] = 'maxlength="' . htmlspecialchars($params) . '"';
                    break;
                case 'url':
                    $attributes[] = 'type="url"';
                    break;
            }
        }

        return implode(' ', $attributes);
    }
}
