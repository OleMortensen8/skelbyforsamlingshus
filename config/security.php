<?php

/**
 * Security Configuration
 * 
 * Centralized security settings for the application
 */

return [
    // ========================================
    // PASSWORD HASHING
    // ========================================
    'password' => [
        'algorithm' => PASSWORD_BCRYPT,
        'cost' => 12,                           // PHP recommended: 10-12
        'time_cost' => 2,                       // For Argon2 (optional)
        'memory_cost' => 65536,                 // For Argon2 (optional)
        'parallelism' => 1,                     // For Argon2 (optional)
    ],

    // ========================================
    // SESSION CONFIGURATION
    // ========================================
    'session' => [
        'name' => 'SKELBY_SESSION',
        'lifetime' => 3600,                     // 1 hour in seconds
        'idle_timeout' => 900,                  // 15 minutes of inactivity
        'renew_after' => 600,                   // Renew token every 10 minutes
        'cookie_secure' => (getenv('ENVIRONMENT') === 'production'),  // HTTPS only
        'cookie_httponly' => true,              // JS cannot access cookie
        'cookie_samesite' => 'Strict',          // CSRF protection (Strict, Lax, None)
        'cookie_path' => '/',
        'cookie_domain' => '',                  // Empty = current domain
        'use_cookies' => true,
        'use_only_cookies' => true,             // No URL-based sessions
    ],

    // ========================================
    // LOGIN ATTEMPT LIMITING
    // ========================================
    'login' => [
        'max_attempts' => 5,                    // Failed attempts before lockout
        'lockout_duration' => 900,              // 15 minutes in seconds
        'attempt_window' => 300,                // Window to count attempts (5 min)
        'rate_limit_per_minute' => 10,          // Max login attempts per minute
        'ip_whitelist' => [],                   // Optional: trusted IPs
    ],

    // ========================================
    // SECURITY HEADERS
    // ========================================
    'headers' => [
        'content_security_policy' => [
            'enabled' => true,
            'policy' => "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data: https:; font-src 'self' data:; connect-src 'self';"
        ],
        'x_content_type_options' => 'nosniff',
        'x_frame_options' => 'SAMEORIGIN',
        'x_xss_protection' => '1; mode=block',
        'referrer_policy' => 'strict-origin-when-cross-origin',
        'permissions_policy' => 'geolocation=(), microphone=(), camera=()',
        'strict_transport_security' => [
            'enabled' => (getenv('ENVIRONMENT') === 'production'),
            'max_age' => 31536000,              // 1 year
            'include_domains' => true,
            'preload' => true,
        ]
    ],

    // ========================================
    // CSRF PROTECTION
    // ========================================
    'csrf' => [
        'enabled' => true,
        'token_length' => 32,
        'token_name' => '_token',               // Form field name
        'header_name' => 'X-CSRF-Token',        // Header name
        'excluded_methods' => ['GET', 'HEAD', 'OPTIONS'],
        'excluded_routes' => [                  // Routes that don't need CSRF
            '/api/*',                           // All API routes
            '/webhook/*',                       // Webhooks
        ],
    ],

    // ========================================
    // INPUT VALIDATION
    // ========================================
    'validation' => [
        'max_input_vars' => 1000,
        'max_file_size' => 10 * 1024 * 1024,    // 10 MB
        'allowed_file_types' => [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
            'text/csv',
        ],
    ],

    // ========================================
    // AUDIT LOGGING
    // ========================================
    'audit' => [
        'enabled' => true,
        'log_level' => [
            'login' => true,
            'logout' => true,
            'login_failed' => true,
            'permission_denied' => true,
            'data_modified' => true,
            'security_events' => true,
        ],
        'retention_days' => 90,                 // Delete logs after 90 days
        'sensitive_fields' => [                 // Don't log these
            'password',
            'password_hash',
            'credit_card',
            'ssn',
            'api_key',
            'token',
        ],
    ],

    // ========================================
    // ENCRYPTION
    // ========================================
    'encryption' => [
        'enabled' => false,                     // Enable if needed
        'algorithm' => 'AES-256-GCM',
        'key' => getenv('ENCRYPTION_KEY'),
    ],

    // ========================================
    // TWO-FACTOR AUTHENTICATION (Future)
    // ========================================
    '2fa' => [
        'enabled' => false,
        'methods' => ['totp', 'email'],         // Time-based OTP, Email
        'issuer' => 'Skelby Forsamlinghus',
        'window' => 1,                          // Allow ±1 time window
    ],

    // ========================================
    // API SECURITY
    // ========================================
    'api' => [
        'enabled' => false,                     // Enable if building API
        'rate_limit' => 100,                    // Requests per minute
        'require_https' => true,
        'allow_cors' => false,
        'cors_origins' => [],
    ],

    // ========================================
    // PASSWORD REQUIREMENTS
    // ========================================
    'password_policy' => [
        'min_length' => 8,
        'max_length' => 128,
        'require_uppercase' => true,
        'require_lowercase' => true,
        'require_numbers' => true,
        'require_special' => true,
        'special_characters' => '!@#$%^&*()_+-=[]{}|;:,.<>?',
        'prevent_common' => true,               // Check against common passwords
        'prevent_reuse' => 5,                   // Can't reuse last N passwords
        'expiration_days' => 0,                 // 0 = no expiration
    ],

    // ========================================
    // IP & DEVICE TRACKING
    // ========================================
    'tracking' => [
        'track_ip' => true,
        'track_user_agent' => true,
        'detect_new_device' => true,
        'require_verification' => false,        // Require verification for new device
    ],

    // ========================================
    // ENVIRONMENT-SPECIFIC OVERRIDES
    // ========================================
    'environments' => [
        'development' => [
            'password.cost' => 10,              // Faster for testing
            'session.cookie_secure' => false,   // Allow HTTP
        ],
        'testing' => [
            'password.cost' => 4,               // Very fast for tests
            'login.max_attempts' => 999,        // Unlimited for testing
        ],
        'production' => [
            'password.cost' => 12,
            'session.cookie_secure' => true,
            'headers.strict_transport_security.enabled' => true,
        ],
    ],
];
