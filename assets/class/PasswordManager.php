<?php
namespace App;

/**
 * PasswordManager - Password Hashing & Verification
 * 
 * Handles secure password hashing, verification, and management
 * Uses PHP's native password_* functions with bcrypt algorithm
 */
class PasswordManager
{
    private $config;

    public function __construct()
    {
        $this->config = include __DIR__ . '/../config/security.php';
    }

    /**
     * Hash a password securely
     * 
     * @param string $password The plain-text password
     * @return string The hashed password
     * @throws Exception
     */
    public function hash($password)
    {
        if (empty($password)) {
            throw new Exception('Password cannot be empty');
        }

        if (strlen($password) > 72) {
            throw new Exception('Password cannot exceed 72 characters');
        }

        $options = [
            'cost' => $this->config['password']['cost'],
        ];

        $hash = password_hash($password, $this->config['password']['algorithm'], $options);

        if ($hash === false) {
            throw new Exception('Password hashing failed');
        }

        return $hash;
    }

    /**
     * Verify a password against a hash
     * 
     * @param string $password Plain-text password to verify
     * @param string $hash The hash to verify against
     * @return bool True if password matches hash
     */
    public function verify($password, $hash)
    {
        if (empty($password) || empty($hash)) {
            return false;
        }

        return password_verify($password, $hash);
    }

    /**
     * Check if a hash needs to be rehashed (algorithm/cost changed)
     * 
     * @param string $hash The hash to check
     * @return bool True if rehash is needed
     */
    public function needsRehash($hash)
    {
        $options = [
            'cost' => $this->config['password']['cost'],
        ];

        return password_needs_rehash($hash, $this->config['password']['algorithm'], $options);
    }

    /**
     * Validate password against policy requirements
     * 
     * @param string $password The password to validate
     * @return array Array of errors (empty if valid)
     */
    public function validatePolicy($password)
    {
        $errors = [];
        $policy = $this->config['password_policy'];

        // Check length
        if (strlen($password) < $policy['min_length']) {
            $errors[] = "Password must be at least {$policy['min_length']} characters";
        }

        if (strlen($password) > $policy['max_length']) {
            $errors[] = "Password cannot exceed {$policy['max_length']} characters";
        }

        // Check uppercase
        if ($policy['require_uppercase'] && !preg_match('/[A-Z]/', $password)) {
            $errors[] = 'Password must contain at least one uppercase letter';
        }

        // Check lowercase
        if ($policy['require_lowercase'] && !preg_match('/[a-z]/', $password)) {
            $errors[] = 'Password must contain at least one lowercase letter';
        }

        // Check numbers
        if ($policy['require_numbers'] && !preg_match('/[0-9]/', $password)) {
            $errors[] = 'Password must contain at least one number';
        }

        // Check special characters
        if ($policy['require_special']) {
            $specialChars = preg_quote($policy['special_characters'], '/');
            if (!preg_match("/[{$specialChars}]/", $password)) {
                $errors[] = 'Password must contain at least one special character';
            }
        }

        // Check against common passwords (basic check)
        if ($policy['prevent_common']) {
            $commonPasswords = [
                'password', '123456', 'password123', 'admin', 'letmein', 'welcome',
                'monkey', '1234567890', 'abc123', 'qwerty', 'password1',
            ];
            if (in_array(strtolower($password), $commonPasswords)) {
                $errors[] = 'This password is too common. Please choose a more unique password';
            }
        }

        return $errors;
    }

    /**
     * Generate a secure random token for password reset
     * 
     * @param int $length Length of token in bytes
     * @return string Random token (hex encoded)
     */
    public function generateResetToken($length = 32)
    {
        if ($length < 16) {
            throw new Exception('Token length must be at least 16');
        }

        $bytes = random_bytes($length);
        return bin2hex($bytes);
    }

    /**
     * Hash a reset token for storage
     * 
     * @param string $token The reset token
     * @return string Hashed token for storage
     */
    public function hashResetToken($token)
    {
        return hash('sha256', $token);
    }

    /**
     * Verify a reset token
     * 
     * @param string $token The token to verify
     * @param string $storedHash The stored hash to verify against
     * @return bool True if token matches
     */
    public function verifyResetToken($token, $storedHash)
    {
        $tokenHash = hash('sha256', $token);
        return hash_equals($storedHash, $tokenHash);
    }

    /**
     * Generate a random password that meets policy requirements
     * 
     * @param int $length Desired password length
     * @return string Generated password
     */
    public function generatePassword($length = 16)
    {
        $policy = $this->config['password_policy'];
        
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = $policy['special_characters'];

        $allChars = $uppercase . $lowercase . $numbers . $special;
        $password = '';

        // Ensure at least one of each required type
        if ($policy['require_uppercase']) {
            $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        }
        if ($policy['require_lowercase']) {
            $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        }
        if ($policy['require_numbers']) {
            $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        }
        if ($policy['require_special']) {
            $password .= $special[random_int(0, strlen($special) - 1)];
        }

        // Fill the rest randomly
        for ($i = strlen($password); $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Shuffle to avoid predictable patterns
        $password = str_shuffle($password);

        return $password;
    }

    /**
     * Get password strength score (0-100)
     * 
     * @param string $password The password to score
     * @return int Strength score
     */
    public function getStrength($password)
    {
        $score = 0;

        // Length scoring
        $length = strlen($password);
        if ($length >= 8) $score += 10;
        if ($length >= 12) $score += 10;
        if ($length >= 16) $score += 10;

        // Character type scoring
        if (preg_match('/[a-z]/', $password)) $score += 15;
        if (preg_match('/[A-Z]/', $password)) $score += 15;
        if (preg_match('/[0-9]/', $password)) $score += 15;
        if (preg_match('/[^a-zA-Z0-9]/', $password)) $score += 15;

        // Variety bonus
        if (preg_match('/[a-z].*[A-Z]|[A-Z].*[a-z]/', $password)) $score += 5;
        if (preg_match('/[a-zA-Z].*[0-9]|[0-9].*[a-zA-Z]/', $password)) $score += 5;

        return min($score, 100);
    }

    /**
     * Get password strength label
     * 
     * @param int $score The strength score
     * @return string Strength label
     */
    public function getStrengthLabel($score)
    {
        if ($score < 20) return 'Very Weak';
        if ($score < 40) return 'Weak';
        if ($score < 60) return 'Fair';
        if ($score < 80) return 'Good';
        return 'Strong';
    }
}
