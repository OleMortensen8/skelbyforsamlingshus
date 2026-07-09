<?php
/**
 * Session Configuration
 *
 * This file contains secure session settings for the SkelbyForsamlinghus project.
 */

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    // Set secure session parameters before starting the session

    // Ensure cookies are only sent over HTTPS in production
    if (getenv('ENVIRONMENT') !== 'development') {
        ini_set('session.cookie_secure', 1);
    }

    // Prevent JavaScript access to session cookies
    ini_set('session.cookie_httponly', 1);

    // Set SameSite attribute to prevent CSRF attacks
    ini_set('session.cookie_samesite', 'Lax');

    // Use cookies for session management (not URL)
    ini_set('session.use_only_cookies', 1);

    // Set session lifetime to 2 hours (7200 seconds)
    ini_set('session.gc_maxlifetime', 7200);
    ini_set('session.cookie_lifetime', 7200);

    // Set session name to something not easily guessable
    session_name('skelby_session');

    // Start the session
    session_start();

    // Protect against session fixation attacks by regenerating session ID periodically
    if (!isset($_SESSION['created'])) {
        $_SESSION['created'] = time();
    } else if (time() - $_SESSION['created'] > 1800) {
        // Regenerate session ID every 30 minutes
        session_regenerate_id(true);
        $_SESSION['created'] = time();
    }

    // Implement session timeout
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > 7200)) {
        // Last activity was more than 2 hours ago
        session_unset();
        session_destroy();
        session_start();
    }

    // Update last activity time
    $_SESSION['last_activity'] = time();
}