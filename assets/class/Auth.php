<?php
namespace App;

/**
 * Auth Class
 *
 * Authorization middleware to protect restricted pages.
 */
class Auth
{
    private $user;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Include secure session configuration
        require_once __DIR__ . '/../config/session_config.php';

        // Initialize user
        require_once __DIR__ . '/User.php';
        $this->user = new User();
    }

    /**
     * Require specific role
     *
     * Redirects to login page if user is not logged in or doesn't have the required role
     *
     * @param string|array $roles Role or array of roles
     * @param string $redirect URL to redirect to after login (optional)
     */
    public function requireRole($roles, $redirect = null)
    {
        // First require login
        $user = $this->requireLogin($redirect);

        // Then check role
        if (!$user->hasRole($roles)) {
            // User doesn't have the required role
            http_response_code(403); // Forbidden
            include __DIR__ . '/../view/403.php';
            exit;
        }

        return $user;
    }

    /**
     * Require authentication
     *
     * Redirects to login page if user is not logged in
     *
     * @param string $redirect URL to redirect to after login (optional)
     */
    public function requireLogin($redirect = null)
    {
        if (!$this->user->isLoggedIn()) {
            // Store the requested URL for redirection after login
            if ($redirect !== null) {
                $_SESSION['redirect_after_login'] = $redirect;
            } elseif (!empty($_SERVER['REQUEST_URI'])) {
                $_SESSION['redirect_after_login'] = $_SERVER['REQUEST_URI'];
            }

            // Redirect to login page
            header('Location: /login.php');
            exit;
        }

        return $this->user;
    }

    /**
     * Check if user is logged in
     *
     * @return bool True if user is logged in, false otherwise
     */
    public function isLoggedIn()
    {
        return $this->user->isLoggedIn();
    }

    /**
     * Check if user has a specific role
     *
     * @param string|array $roles Role or array of roles
     * @return bool True if user has the role, false otherwise
     */
    public function hasRole($roles)
    {
        return $this->user->hasRole($roles);
    }

    /**
     * Get the current user
     *
     * @return User The current user
     */
    public function getUser()
    {
        return $this->user;
    }
}