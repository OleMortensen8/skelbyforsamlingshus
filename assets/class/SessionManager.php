<?php
namespace App;

/**
 * SessionManager - Secure Session Handling
 * 
 * Manages secure session creation, renewal, and destruction
 * Implements session timeout and activity tracking
 */
class SessionManager
{
    private $config;
    private $isStarted = false;

    public function __construct()
    {
        $this->config = include __DIR__ . '/../config/security.php';
    }

    /**
     * Initialize secure session
     * 
     * Must be called before any SESSION operations
     * 
     * @throws Exception
     */
    public function start()
    {
        if ($this->isStarted) {
            return;
        }

        // Configure session settings
        $sessionConfig = $this->config['session'];

        // Set session name
        session_name($sessionConfig['name']);

        // Configure cookie parameters
        session_set_cookie_params([
            'lifetime' => $sessionConfig['lifetime'],
            'path' => $sessionConfig['cookie_path'],
            'domain' => $sessionConfig['cookie_domain'],
            'secure' => $sessionConfig['cookie_secure'],
            'httponly' => $sessionConfig['cookie_httponly'],
            'samesite' => $sessionConfig['cookie_samesite'],
        ]);

        // Set session options
        ini_set('session.use_only_cookies', $sessionConfig['use_only_cookies'] ? 1 : 0);
        ini_set('session.use_cookies', $sessionConfig['use_cookies'] ? 1 : 0);
        ini_set('session.gc_maxlifetime', $sessionConfig['lifetime']);

        // Start the session
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $this->isStarted = true;

        // Initialize session tracking
        if (!isset($_SESSION['_created'])) {
            $_SESSION['_created'] = time();
            $_SESSION['_last_activity'] = time();
            $_SESSION['_ip_address'] = $this->getClientIp();
            $_SESSION['_user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? '';
        }

        // Check for session timeout
        $this->checkTimeout();

        // Check for suspicious activity (IP/User-Agent change)
        $this->checkSuspiciousActivity();

        return $this;
    }

    /**
     * Check if session is active
     * 
     * @return bool True if session is started
     */
    public function isActive()
    {
        return $this->isStarted && session_status() === PHP_SESSION_ACTIVE;
    }

    /**
     * Get a session value
     * 
     * @param string $key The session key
     * @param mixed $default Default value if key doesn't exist
     * @return mixed The session value or default
     */
    public function get($key, $default = null)
    {
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set a session value
     * 
     * @param string $key The session key
     * @param mixed $value The value to set
     * @return $this
     */
    public function set($key, $value)
    {
        $_SESSION[$key] = $value;
        $_SESSION['_last_activity'] = time();
        return $this;
    }

    /**
     * Delete a session value
     * 
     * @param string $key The session key to delete
     * @return $this
     */
    public function delete($key)
    {
        unset($_SESSION[$key]);
        return $this;
    }

    /**
     * Check if session key exists
     * 
     * @param string $key The session key
     * @return bool True if key exists
     */
    public function has($key)
    {
        return isset($_SESSION[$key]);
    }

    /**
     * Destroy the current session
     * 
     * @return $this
     */
    public function destroy()
    {
        if ($this->isActive()) {
            // Log session destruction
            $this->logEvent('session_destroyed');

            // Clear all session data
            $_SESSION = [];

            // Delete the session cookie
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }

            // Destroy session
            session_destroy();
            $this->isStarted = false;
        }

        return $this;
    }

    /**
     * Regenerate session ID (prevent fixation attacks)
     * 
     * Should be called after login
     * 
     * @param bool $deleteOld Whether to delete old session data
     * @return $this
     */
    public function regenerateId($deleteOld = true)
    {
        if ($this->isActive()) {
            session_regenerate_id($deleteOld);
            $this->logEvent('session_regenerated');
        }

        return $this;
    }

    /**
     * Check for session timeout
     * 
     * @throws Exception If session has timed out
     */
    private function checkTimeout()
    {
        $sessionConfig = $this->config['session'];
        $lastActivity = $_SESSION['_last_activity'] ?? 0;
        $now = time();

        // Check idle timeout
        if ($now - $lastActivity > $sessionConfig['idle_timeout']) {
            $this->destroy();
            throw new Exception('Session expired due to inactivity');
        }

        // Update last activity
        $_SESSION['_last_activity'] = $now;
    }

    /**
     * Check for suspicious activity (IP or User-Agent change)
     * 
     * @throws Exception If suspicious activity detected
     */
    private function checkSuspiciousActivity()
    {
        $tracking = $this->config['tracking'];

        if (!$tracking['detect_new_device']) {
            return;
        }

        $currentIp = $this->getClientIp();
        $currentAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

        $storedIp = $_SESSION['_ip_address'] ?? null;
        $storedAgent = $_SESSION['_user_agent'] ?? null;

        $mismatchedIp = $tracking['track_ip'] && $currentIp !== $storedIp;
        $mismatchedAgent = $tracking['track_user_agent'] && $currentAgent !== $storedAgent;

        if ($mismatchedIp || $mismatchedAgent) {
            if ($tracking['require_verification']) {
                // In production, you might require re-authentication
                $this->logEvent('suspicious_activity_detected', [
                    'ip_mismatch' => $mismatchedIp,
                    'agent_mismatch' => $mismatchedAgent,
                    'previous_ip' => $storedIp,
                    'current_ip' => $currentIp,
                ]);
            } else {
                // Just log it for monitoring
                $this->logEvent('session_property_changed', [
                    'ip_mismatch' => $mismatchedIp,
                    'agent_mismatch' => $mismatchedAgent,
                ]);

                // Update stored values
                $_SESSION['_ip_address'] = $currentIp;
                $_SESSION['_user_agent'] = $currentAgent;
            }
        }
    }

    /**
     * Get client IP address
     * 
     * Handles proxies and various server configurations
     * 
     * @return string Client IP address
     */
    private function getClientIp()
    {
        // Check for IP from CloudFlare
        if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            return $_SERVER['HTTP_CF_CONNECTING_IP'];
        }

        // Check for IP from proxy
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            return trim($ips[0]);
        }

        // Check for IP from X-Forwarded-For behind proxy
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }

        // Check for IP from CLIENT_IP
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        }

        // Direct connection
        return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    /**
     * Log session event to audit log
     * 
     * @param string $event Event name
     * @param array $data Additional data
     */
    private function logEvent($event, $data = [])
    {
        // Log to file for now
        // In production, this would go to database or external service
        $log = [
            'timestamp' => date('Y-m-d H:i:s'),
            'event' => $event,
            'user_id' => $_SESSION['user_id'] ?? null,
            'ip' => $this->getClientIp(),
            'data' => $data,
        ];

        error_log(json_encode($log), 3, __DIR__ . '/../../storage/logs/session.log');
    }

    /**
     * Get all session data (excluding internal keys)
     * 
     * @return array Session data
     */
    public function all()
    {
        $data = $_SESSION;
        
        // Remove internal tracking keys
        unset($data['_created']);
        unset($data['_last_activity']);
        unset($data['_ip_address']);
        unset($data['_user_agent']);
        
        return $data;
    }

    /**
     * Get session metadata
     * 
     * @return array Session metadata
     */
    public function getMetadata()
    {
        return [
            'created' => $_SESSION['_created'] ?? null,
            'last_activity' => $_SESSION['_last_activity'] ?? null,
            'ip_address' => $_SESSION['_ip_address'] ?? null,
            'duration' => time() - ($_SESSION['_created'] ?? time()),
            'idle_time' => time() - ($_SESSION['_last_activity'] ?? time()),
        ];
    }

    /**
     * Extend session lifetime
     * 
     * Useful for "remember me" functionality
     * 
     * @param int $seconds Additional seconds to add
     * @return $this
     */
    public function extend($seconds = 3600)
    {
        if ($this->isActive()) {
            $_SESSION['_last_activity'] = time() - (
                $this->config['session']['idle_timeout'] - $seconds
            );
        }

        return $this;
    }

    /**
     * Mark session as requiring re-authentication
     * 
     * Useful before sensitive operations
     * 
     * @param string $reason Reason for re-authentication
     * @return $this
     */
    public function requireReAuth($reason = null)
    {
        $_SESSION['_require_reauth'] = true;
        $_SESSION['_reauth_reason'] = $reason;
        $_SESSION['_reauth_timestamp'] = time();

        return $this;
    }

    /**
     * Check if re-authentication is required
     * 
     * @return bool True if re-auth is required
     */
    public function requiresReAuth()
    {
        return $_SESSION['_require_reauth'] ?? false;
    }

    /**
     * Clear re-authentication requirement
     * 
     * @return $this
     */
    public function clearReAuth()
    {
        unset($_SESSION['_require_reauth']);
        unset($_SESSION['_reauth_reason']);
        unset($_SESSION['_reauth_timestamp']);

        return $this;
    }
}
