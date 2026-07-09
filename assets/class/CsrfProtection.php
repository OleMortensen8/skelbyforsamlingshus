<?php
namespace App;

/**
 * CsrfProtection - Cross-Site Request Forgery Protection
 * 
 * Implements token-based CSRF protection with double-submit cookie pattern
 * and synchronizer token pattern support
 */
class CsrfProtection
{
    private $config;
    private $sessionManager;
    private const TOKEN_LENGTH = 32;
    private const TOKEN_NAME = '_csrf_token';
    private const FIELD_NAME = '_token';

    public function __construct(SessionManager $sessionManager)
    {
        $this->config = include __DIR__ . '/../config/security.php';
        $this->sessionManager = $sessionManager;
    }

    /**
     * Generate a new CSRF token
     * 
     * Regenerates token if it doesn't exist or if configured to regenerate on each request
     * 
     * @return string CSRF token
     */
    public function generate()
    {
        $csrfConfig = $this->config['csrf'];

        // Check if token exists and regeneration isn't forced
        $existingToken = $this->sessionManager->get(self::TOKEN_NAME);
        if ($existingToken && !$csrfConfig['regenerate_per_request']) {
            return $existingToken;
        }

        // Generate new token
        $token = bin2hex(random_bytes(self::TOKEN_LENGTH));

        // Store in session
        $this->sessionManager->set(self::TOKEN_NAME, $token);

        // Store token metadata
        $this->sessionManager->set('_csrf_token_generated', time());

        return $token;
    }

    /**
     * Verify CSRF token from request
     * 
     * Checks token from POST/GET parameters or custom header
     * 
     * @param string|null $token Token to verify (if null, extracts from request)
     * @return bool True if token is valid
     */
    public function verify($token = null)
    {
        if (!$this->sessionManager->isActive()) {
            return false;
        }

        // Extract token from request if not provided
        if ($token === null) {
            $token = $this->extractTokenFromRequest();
        }

        if ($token === null) {
            return false;
        }

        // Get stored token
        $storedToken = $this->sessionManager->get(self::TOKEN_NAME);

        if ($storedToken === null) {
            return false;
        }

        // Use timing-safe comparison
        $isValid = hash_equals($storedToken, $token);

        // Check token age
        $csrfConfig = $this->config['csrf'];
        $tokenAge = time() - ($this->sessionManager->get('_csrf_token_generated') ?? time());

        if ($tokenAge > $csrfConfig['token_expiry']) {
            return false;
        }

        // Log verification
        $this->log('csrf_token_verified', ['valid' => $isValid]);

        return $isValid;
    }

    /**
     * Extract CSRF token from request
     * 
     * Checks POST data, GET data, and custom headers
     * 
     * @return string|null Token or null if not found
     */
    private function extractTokenFromRequest()
    {
        // Check POST parameter
        if (!empty($_POST[self::FIELD_NAME])) {
            return $_POST[self::FIELD_NAME];
        }

        // Check GET parameter (for forms that use GET)
        if (!empty($_GET[self::FIELD_NAME])) {
            return $_GET[self::FIELD_NAME];
        }

        // Check custom header (for AJAX requests)
        $headers = getallheaders();
        $headerName = 'X-CSRF-Token';

        if (!empty($headers[$headerName])) {
            return $headers[$headerName];
        }

        // Check alternative header format
        if (!empty($headers['x-csrf-token'])) {
            return $headers['x-csrf-token'];
        }

        // Check $_SERVER for header (for CLI/alternative servers)
        if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            return $_SERVER['HTTP_X_CSRF_TOKEN'];
        }

        return null;
    }

    /**
     * Get token for use in forms
     * 
     * Returns token HTML hidden input, token string, or array depending on format
     * 
     * @param string $format Format: 'input' (HTML), 'token' (string), 'array' (associative array)
     * @return string|array Token in specified format
     */
    public function getToken($format = 'input')
    {
        $token = $this->generate();

        return match ($format) {
            'input' => sprintf('<input type="hidden" name="%s" value="%s">', 
                htmlspecialchars(self::FIELD_NAME), 
                htmlspecialchars($token)),
            'array' => [self::FIELD_NAME => $token],
            'token' => $token,
            default => $token
        };
    }

    /**
     * Get token field name
     * 
     * @return string Token field name
     */
    public function getFieldName()
    {
        return self::FIELD_NAME;
    }

    /**
     * Regenerate token after successful verification
     * 
     * Useful for securing sensitive operations
     * 
     * @return string New token
     */
    public function regenerate()
    {
        $this->sessionManager->delete(self::TOKEN_NAME);
        $token = $this->generate();
        $this->log('csrf_token_regenerated', []);
        return $token;
    }

    /**
     * Get token for AJAX requests as header
     * 
     * @return array Associative array with header name and token
     */
    public function getHeaderToken()
    {
        return [
            'X-CSRF-Token' => $this->generate()
        ];
    }

    /**
     * Validate request method for CSRF-protected operations
     * 
     * GET requests should generally be safe (idempotent)
     * POST/PUT/DELETE/PATCH should have CSRF tokens
     * 
     * @return bool True if method is safe or token is valid
     */
    public function validateRequestMethod()
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $safeMethods = ['GET', 'HEAD', 'OPTIONS', 'TRACE'];

        // Safe methods don't need CSRF token
        if (in_array($method, $safeMethods)) {
            return true;
        }

        // Unsafe methods need CSRF token
        return $this->verify();
    }

    /**
     * Check SameSite cookie compliance
     * 
     * @return array Compliance report
     */
    public function checkCompliance()
    {
        $csrfConfig = $this->config['csrf'];
        $sessionConfig = $this->config['session'];

        return [
            'samesite_set' => $sessionConfig['cookie_samesite'] !== 'None',
            'samesite_value' => $sessionConfig['cookie_samesite'],
            'httponly_set' => $sessionConfig['cookie_httponly'],
            'secure_set' => $sessionConfig['cookie_secure'],
            'token_random' => true,
            'token_length' => self::TOKEN_LENGTH,
            'expiry_seconds' => $csrfConfig['token_expiry'],
        ];
    }

    /**
     * Validate origin and referer headers
     * 
     * @return bool True if origin is valid
     */
    public function validateOrigin()
    {
        $csrfConfig = $this->config['csrf'];

        if (!$csrfConfig['check_origin']) {
            return true;
        }

        $originHeader = $_SERVER['HTTP_ORIGIN'] ?? null;
        $referer = $_SERVER['HTTP_REFERER'] ?? null;

        $expectedHost = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? null;

        // Check Origin header (preferred for POST requests)
        if ($originHeader) {
            $originHost = parse_url($originHeader, PHP_URL_HOST);
            if ($originHost !== $expectedHost) {
                $this->log('csrf_origin_mismatch', [
                    'received' => $originHost,
                    'expected' => $expectedHost
                ]);
                return false;
            }
        }

        // Check Referer header (fallback)
        if ($referer) {
            $refererHost = parse_url($referer, PHP_URL_HOST);
            if ($refererHost !== $expectedHost) {
                // Don't reject on referer mismatch, just log it
                // Some users might have disabled referer for privacy
                $this->log('csrf_referer_mismatch', [
                    'received' => $refererHost,
                    'expected' => $expectedHost
                ]);
            }
        }

        return true;
    }

    /**
     * Get CSRF protection metadata
     * 
     * @return array Metadata array
     */
    public function getMetadata()
    {
        return [
            'token_exists' => $this->sessionManager->has(self::TOKEN_NAME),
            'token_age' => time() - ($this->sessionManager->get('_csrf_token_generated') ?? time()),
            'token_expiry' => $this->config['csrf']['token_expiry'],
            'regenerate_per_request' => $this->config['csrf']['regenerate_per_request'],
            'check_origin' => $this->config['csrf']['check_origin'],
            'same_site' => $this->config['session']['cookie_samesite'],
        ];
    }

    /**
     * Double-submit cookie pattern helper
     * 
     * Returns token suitable for both cookie and form submission
     * 
     * @return array Array with token and cookie information
     */
    public function getDoubleSubmitCookie()
    {
        $token = $this->generate();
        $csrfConfig = $this->config['csrf'];
        $sessionConfig = $this->config['session'];

        return [
            'token' => $token,
            'cookie_name' => self::TOKEN_NAME,
            'cookie_value' => $token,
            'cookie_path' => $sessionConfig['cookie_path'],
            'cookie_domain' => $sessionConfig['cookie_domain'],
            'cookie_secure' => $sessionConfig['cookie_secure'],
            'cookie_httponly' => false, // Cookie must be readable by JavaScript for double-submit
            'cookie_samesite' => $sessionConfig['cookie_samesite'],
            'max_age' => $csrfConfig['token_expiry'],
        ];
    }

    /**
     * Create middleware-friendly validation response
     * 
     * @return array Response with validation result and error details
     */
    public function getValidationResponse()
    {
        $isValid = $this->validateRequestMethod();

        if ($isValid) {
            return [
                'valid' => true,
                'message' => 'CSRF token verified'
            ];
        }

        $this->log('csrf_validation_failed', [
            'method' => $_SERVER['REQUEST_METHOD'] ?? 'UNKNOWN',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN'
        ]);

        return [
            'valid' => false,
            'message' => 'CSRF token validation failed',
            'error' => 'Invalid or missing CSRF token',
            'code' => 'CSRF_VALIDATION_FAILED'
        ];
    }

    /**
     * Log CSRF events
     * 
     * @param string $event Event name
     * @param array $data Event data
     */
    private function log($event, $data = [])
    {
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN',
            'user_id' => $_SESSION['user_id'] ?? null,
            'data' => $data,
        ];

        error_log(json_encode($log), 3, __DIR__ . '/../../storage/logs/csrf.log');
    }

    /**
     * Clean up expired tokens from session
     * 
     * @return int Number of tokens cleaned
     */
    public function cleanup()
    {
        $csrfConfig = $this->config['csrf'];
        $tokenAge = time() - ($this->sessionManager->get('_csrf_token_generated') ?? time());

        if ($tokenAge > $csrfConfig['token_expiry']) {
            $this->sessionManager->delete(self::TOKEN_NAME);
            $this->sessionManager->delete('_csrf_token_generated');
            return 1;
        }

        return 0;
    }

    /**
     * Get detailed protection information for debugging
     * 
     * @return array Detailed information
     */
    public function getProtectionInfo()
    {
        return [
            'token_field_name' => self::FIELD_NAME,
            'token_session_key' => self::TOKEN_NAME,
            'token_length' => self::TOKEN_LENGTH,
            'verification_methods' => [
                'POST parameter: ' . self::FIELD_NAME,
                'GET parameter: ' . self::FIELD_NAME,
                'HTTP header: X-CSRF-Token',
            ],
            'configuration' => [
                'token_expiry' => $this->config['csrf']['token_expiry'],
                'regenerate_per_request' => $this->config['csrf']['regenerate_per_request'],
                'check_origin' => $this->config['csrf']['check_origin'],
                'check_referer' => $this->config['csrf']['check_referer'] ?? false,
            ],
            'session_cookie' => [
                'secure' => $this->config['session']['cookie_secure'],
                'httponly' => $this->config['session']['cookie_httponly'],
                'samesite' => $this->config['session']['cookie_samesite'],
            ]
        ];
    }
}
