<?php
namespace App;

/**
 * Authentication - User Authentication System
 * 
 * Combines SessionManager and PasswordManager for complete authentication
 * Includes login, logout, verification, and rate limiting
 */
class Authentication
{
    private $sessionManager;
    private $passwordManager;
    private $config;
    private $database;
    private $loginAttempts = [];

    public function __construct(Database $database)
    {
        $this->sessionManager = new SessionManager();
        $this->passwordManager = new PasswordManager();
        $this->config = include __DIR__ . '/../config/security.php';
        $this->database = $database;

        // Start session
        $this->sessionManager->start();
    }

    /**
     * Authenticate user with email and password
     * 
     * @param string $email User email
     * @param string $password User password
     * @param bool $rememberMe Whether to extend session lifetime
     * @return array Status array with success flag and user data or error
     */
    public function login($email, $password, $rememberMe = false)
    {
        $loginConfig = $this->config['login_attempts'];
        $email = trim($email);

        // Check rate limiting
        $attemptCheck = $this->checkLoginAttempts($email);
        if ($attemptCheck !== true) {
            $this->logAuthEvent('login_failed_rate_limit', ['email' => $email]);
            return [
                'success' => false,
                'error' => $attemptCheck,
                'code' => 'RATE_LIMITED'
            ];
        }

        // Validate input format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->recordFailedAttempt($email);
            $this->logAuthEvent('login_failed_invalid_email', ['email' => $email]);
            return [
                'success' => false,
                'error' => 'Invalid email format',
                'code' => 'INVALID_EMAIL'
            ];
        }

        // Fetch user from database
        try {
            $user = $this->database->findOne('users', ['email' => $email]);
        } catch (Exception $e) {
            $this->logAuthEvent('login_failed_database_error', ['email' => $email]);
            return [
                'success' => false,
                'error' => 'System error during login',
                'code' => 'DATABASE_ERROR'
            ];
        }

        // User not found - use timing-safe comparison to prevent timing attacks
        if (!$user) {
            $this->recordFailedAttempt($email);
            // Still verify password to maintain consistent timing
            $this->passwordManager->verify($password, '$2y$12$invalid');
            $this->logAuthEvent('login_failed_user_not_found', ['email' => $email]);
            return [
                'success' => false,
                'error' => 'Invalid email or password',
                'code' => 'INVALID_CREDENTIALS'
            ];
        }

        // Check if account is active
        if ($user['status'] !== 'active') {
            $this->recordFailedAttempt($email);
            $this->logAuthEvent('login_failed_inactive_account', ['user_id' => $user['id']]);
            return [
                'success' => false,
                'error' => 'This account is inactive',
                'code' => 'ACCOUNT_INACTIVE'
            ];
        }

        // Verify password
        if (!$this->passwordManager->verify($password, $user['password_hash'])) {
            $this->recordFailedAttempt($email);
            $this->logAuthEvent('login_failed_invalid_password', ['user_id' => $user['id']]);
            return [
                'success' => false,
                'error' => 'Invalid email or password',
                'code' => 'INVALID_CREDENTIALS'
            ];
        }

        // Check if password needs rehashing
        if ($this->passwordManager->needsRehash($user['password_hash'])) {
            $newHash = $this->passwordManager->hash($password);
            try {
                $this->database->update('users', 
                    ['password_hash' => $newHash],
                    ['id' => $user['id']]
                );
            } catch (Exception $e) {
                $this->logAuthEvent('login_password_rehash_failed', ['user_id' => $user['id']]);
            }
        }

        // Clear failed login attempts
        $this->clearFailedAttempts($email);

        // Regenerate session ID to prevent fixation attacks
        $this->sessionManager->regenerateId(true);

        // Store user data in session
        $this->sessionManager->set('user_id', $user['id']);
        $this->sessionManager->set('email', $user['email']);
        $this->sessionManager->set('name', $user['name']);
        $this->sessionManager->set('role', $user['role'] ?? 'member');
        $this->sessionManager->set('logged_in', true);
        $this->sessionManager->set('login_time', time());

        // Handle "remember me"
        if ($rememberMe) {
            $this->sessionManager->extend($this->config['session']['remember_me_duration']);
        }

        // Update last login timestamp
        try {
            $this->database->update('users',
                ['last_login' => date('Y-m-d H:i:s')],
                ['id' => $user['id']]
            );
        } catch (Exception $e) {
            // Non-critical, continue login
        }

        $this->logAuthEvent('login_success', ['user_id' => $user['id']]);

        return [
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['role'] ?? 'member'
            ]
        ];
    }

    /**
     * Logout current user
     * 
     * @return bool True on successful logout
     */
    public function logout()
    {
        if ($this->isLoggedIn()) {
            $userId = $this->sessionManager->get('user_id');
            $this->logAuthEvent('logout', ['user_id' => $userId]);
        }

        $this->sessionManager->destroy();
        return true;
    }

    /**
     * Check if user is logged in
     * 
     * @return bool True if user is logged in
     */
    public function isLoggedIn()
    {
        return $this->sessionManager->has('user_id') && 
               $this->sessionManager->get('logged_in') === true;
    }

    /**
     * Get current user data
     * 
     * @return array|null Current user data or null if not logged in
     */
    public function getCurrentUser()
    {
        if (!$this->isLoggedIn()) {
            return null;
        }

        return [
            'id' => $this->sessionManager->get('user_id'),
            'email' => $this->sessionManager->get('email'),
            'name' => $this->sessionManager->get('name'),
            'role' => $this->sessionManager->get('role')
        ];
    }

    /**
     * Get current user ID
     * 
     * @return int|null Current user ID or null if not logged in
     */
    public function getCurrentUserId()
    {
        return $this->sessionManager->get('user_id');
    }

    /**
     * Get current user role
     * 
     * @return string|null Current user role or null if not logged in
     */
    public function getCurrentUserRole()
    {
        return $this->sessionManager->get('role');
    }

    /**
     * Require re-authentication before sensitive operations
     * 
     * @param string $reason Reason for re-authentication
     * @return bool True if already authenticated within grace period
     */
    public function requireReAuth($reason = null)
    {
        $lastAuthTime = $this->sessionManager->get('login_time', 0);
        $gracePeriod = $this->config['session']['reauth_grace_period'] ?? 300; // 5 minutes

        if (time() - $lastAuthTime > $gracePeriod) {
            $this->sessionManager->requireReAuth($reason);
            return false;
        }

        return true;
    }

    /**
     * Verify password for sensitive operations
     * 
     * @param string $password Password to verify
     * @return bool True if password is correct
     */
    public function verifyCurrentPassword($password)
    {
        if (!$this->isLoggedIn()) {
            return false;
        }

        $userId = $this->sessionManager->get('user_id');
        
        try {
            $user = $this->database->findOne('users', ['id' => $userId]);
            if (!$user) {
                return false;
            }

            return $this->passwordManager->verify($password, $user['password_hash']);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Change user password
     * 
     * @param string $currentPassword Current password
     * @param string $newPassword New password
     * @return array Status array
     */
    public function changePassword($currentPassword, $newPassword)
    {
        if (!$this->isLoggedIn()) {
            return [
                'success' => false,
                'error' => 'Not logged in',
                'code' => 'NOT_LOGGED_IN'
            ];
        }

        // Verify current password
        if (!$this->verifyCurrentPassword($currentPassword)) {
            $this->logAuthEvent('password_change_failed_invalid_current', 
                ['user_id' => $this->sessionManager->get('user_id')]);
            return [
                'success' => false,
                'error' => 'Current password is incorrect',
                'code' => 'INVALID_CURRENT_PASSWORD'
            ];
        }

        // Validate new password policy
        $policyErrors = $this->passwordManager->validatePolicy($newPassword);
        if (!empty($policyErrors)) {
            return [
                'success' => false,
                'error' => 'Password does not meet requirements',
                'errors' => $policyErrors,
                'code' => 'PASSWORD_POLICY_VIOLATION'
            ];
        }

        // Hash new password
        $newHash = $this->passwordManager->hash($newPassword);
        $userId = $this->sessionManager->get('user_id');

        try {
            $this->database->update('users',
                ['password_hash' => $newHash, 'password_changed' => date('Y-m-d H:i:s')],
                ['id' => $userId]
            );

            $this->logAuthEvent('password_changed', ['user_id' => $userId]);

            return ['success' => true];
        } catch (Exception $e) {
            $this->logAuthEvent('password_change_failed_database', ['user_id' => $userId]);
            return [
                'success' => false,
                'error' => 'Failed to update password',
                'code' => 'DATABASE_ERROR'
            ];
        }
    }

    /**
     * Request password reset
     * 
     * @param string $email User email
     * @return array Status array
     */
    public function requestPasswordReset($email)
    {
        $email = trim($email);

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->logAuthEvent('password_reset_invalid_email', ['email' => $email]);
            return [
                'success' => false,
                'error' => 'Invalid email format',
                'code' => 'INVALID_EMAIL'
            ];
        }

        try {
            $user = $this->database->findOne('users', ['email' => $email]);
            
            if (!$user) {
                // Don't reveal whether email exists
                $this->logAuthEvent('password_reset_user_not_found', ['email' => $email]);
                return [
                    'success' => true,
                    'message' => 'If the email exists, a reset link will be sent'
                ];
            }

            // Generate reset token
            $token = $this->passwordManager->generateResetToken();
            $tokenHash = $this->passwordManager->hashResetToken($token);
            $expiresAt = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            // Store reset token
            try {
                $this->database->update('users',
                    [
                        'reset_token' => $tokenHash,
                        'reset_token_expires' => $expiresAt,
                        'reset_requested' => date('Y-m-d H:i:s')
                    ],
                    ['id' => $user['id']]
                );

                $this->logAuthEvent('password_reset_requested', ['user_id' => $user['id']]);

                // In production, send email with reset link
                // sendPasswordResetEmail($user['email'], $token);

                return [
                    'success' => true,
                    'message' => 'If the email exists, a reset link will be sent',
                    'token' => $token // Remove in production!
                ];
            } catch (Exception $e) {
                $this->logAuthEvent('password_reset_token_failed', ['user_id' => $user['id']]);
                return [
                    'success' => false,
                    'error' => 'Failed to create reset token',
                    'code' => 'DATABASE_ERROR'
                ];
            }
        } catch (Exception $e) {
            $this->logAuthEvent('password_reset_error', ['email' => $email]);
            return [
                'success' => false,
                'error' => 'System error',
                'code' => 'SYSTEM_ERROR'
            ];
        }
    }

    /**
     * Verify and use password reset token
     * 
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return array Status array
     */
    public function resetPassword($token, $newPassword)
    {
        $tokenHash = $this->passwordManager->hashResetToken($token);

        try {
            $user = $this->database->findOne('users', ['reset_token' => $tokenHash]);

            if (!$user) {
                $this->logAuthEvent('password_reset_invalid_token', []);
                return [
                    'success' => false,
                    'error' => 'Invalid or expired reset token',
                    'code' => 'INVALID_TOKEN'
                ];
            }

            // Check token expiration
            if (strtotime($user['reset_token_expires']) < time()) {
                $this->logAuthEvent('password_reset_token_expired', ['user_id' => $user['id']]);
                return [
                    'success' => false,
                    'error' => 'Reset token has expired',
                    'code' => 'TOKEN_EXPIRED'
                ];
            }

            // Validate new password
            $policyErrors = $this->passwordManager->validatePolicy($newPassword);
            if (!empty($policyErrors)) {
                return [
                    'success' => false,
                    'error' => 'Password does not meet requirements',
                    'errors' => $policyErrors,
                    'code' => 'PASSWORD_POLICY_VIOLATION'
                ];
            }

            // Update password
            $newHash = $this->passwordManager->hash($newPassword);

            $this->database->update('users',
                [
                    'password_hash' => $newHash,
                    'password_changed' => date('Y-m-d H:i:s'),
                    'reset_token' => null,
                    'reset_token_expires' => null
                ],
                ['id' => $user['id']]
            );

            $this->logAuthEvent('password_reset_success', ['user_id' => $user['id']]);

            return ['success' => true];
        } catch (Exception $e) {
            $this->logAuthEvent('password_reset_failed', ['token_hash' => substr($tokenHash, 0, 10)]);
            return [
                'success' => false,
                'error' => 'Failed to reset password',
                'code' => 'DATABASE_ERROR'
            ];
        }
    }

    /**
     * Check login rate limiting
     * 
     * @param string $email User email
     * @return bool|string True if allowed, error message if limited
     */
    private function checkLoginAttempts($email)
    {
        $config = $this->config['login_attempts'];
        $key = 'login_attempts_' . md5($email);
        $attemptsFile = sys_get_temp_dir() . '/' . $key;

        if (!file_exists($attemptsFile)) {
            return true;
        }

        $data = json_decode(file_get_contents($attemptsFile), true);
        $attempts = $data['attempts'] ?? 0;
        $firstAttempt = $data['first_attempt'] ?? 0;
        $lockedUntil = $data['locked_until'] ?? 0;

        // Check if currently locked
        if ($lockedUntil > time()) {
            $remainingSeconds = $lockedUntil - time();
            return "Too many login attempts. Try again in {$remainingSeconds} seconds.";
        }

        // Reset attempts if window has passed
        if ($firstAttempt < time() - $config['lockout_duration']) {
            unlink($attemptsFile);
            return true;
        }

        // Check attempt count
        if ($attempts >= $config['max_attempts']) {
            return "Too many login attempts. Account locked for " . 
                   ($config['lockout_duration'] / 60) . " minutes.";
        }

        return true;
    }

    /**
     * Record failed login attempt
     * 
     * @param string $email User email
     */
    private function recordFailedAttempt($email)
    {
        $config = $this->config['login_attempts'];
        $key = 'login_attempts_' . md5($email);
        $attemptsFile = sys_get_temp_dir() . '/' . $key;

        $data = [];
        if (file_exists($attemptsFile)) {
            $data = json_decode(file_get_contents($attemptsFile), true);
        }

        $data['attempts'] = ($data['attempts'] ?? 0) + 1;
        $data['first_attempt'] = $data['first_attempt'] ?? time();

        if ($data['attempts'] >= $config['max_attempts']) {
            $data['locked_until'] = time() + $config['lockout_duration'];
        }

        file_put_contents($attemptsFile, json_encode($data));
        chmod($attemptsFile, 0600);
    }

    /**
     * Clear failed login attempts
     * 
     * @param string $email User email
     */
    private function clearFailedAttempts($email)
    {
        $key = 'login_attempts_' . md5($email);
        $attemptsFile = sys_get_temp_dir() . '/' . $key;

        if (file_exists($attemptsFile)) {
            unlink($attemptsFile);
        }
    }

    /**
     * Log authentication event
     * 
     * @param string $event Event name
     * @param array $data Event data
     */
    private function logAuthEvent($event, $data = [])
    {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $this->getClientIp(),
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
            'data' => $data,
        ];

        error_log(json_encode($log), 3, __DIR__ . '/../../storage/logs/auth.log');
    }

    /**
     * Get client IP address
     * 
     * @return string Client IP
     */
    private function getClientIp()
    {
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Get session manager instance
     * 
     * @return SessionManager
     */
    public function getSessionManager()
    {
        return $this->sessionManager;
    }

    /**
     * Get password manager instance
     * 
     * @return PasswordManager
     */
    public function getPasswordManager()
    {
        return $this->passwordManager;
    }
}
