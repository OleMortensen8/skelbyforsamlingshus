<?php

/**
 * Security Headers Configuration
 *
 * This file sets security headers including Content Security Policy (CSP)
 * to help protect against various attacks like XSS, clickjacking, etc.
 */

// Enforce HTTPS in production environment
// Skip HTTPS enforcement for localhost and development environment
if (
    getenv('ENVIRONMENT') !== 'development' &&
    !isset($_SERVER['HTTP_HOST']) ||
    (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== 'localhost:8080')
) {
    if (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] !== 'on') {
        // Redirect to HTTPS version of the URL
        $redirect = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: ' . $redirect);
        exit();
    }
}

// The Cap CAPTCHA widget (self-hosted, served from assets/js/vendor/) fetches
// challenges directly from the configured Cap server, so that origin needs to
// be allowed in connect-src. It also compiles a WASM module (needs
// 'wasm-unsafe-eval' in script-src) and runs its solver in a Web Worker
// spawned from a blob: URL (needs worker-src, since it isn't covered by
// script-src's blob: exemption once worker-src is unset and falls back).
$capServerUrl = getenv('CAP_SERVER_URL') ?: '';
$capConnectSrc = $capServerUrl !== '' ? ' ' . $capServerUrl : '';

// Content Security Policy (CSP)
// Restricts the sources of content that can be loaded on the page
$cspDirectives = [
    "default-src" => "'self'",
    "script-src" => "'self' 'unsafe-inline' 'wasm-unsafe-eval' https://ajax.googleapis.com",
    "worker-src" => "'self' blob:",
    "style-src" => "'self' 'unsafe-inline'",
    "img-src" => "'self' data: https://www.openstreetmap.org https://*.tile.openstreetmap.org https://" . ($_SERVER['HTTP_HOST'] ?? 'skelby-forsamlingshus.dk'),
    "font-src" => "'self'",
    "connect-src" => "'self' https://*.tile.openstreetmap.org" . $capConnectSrc,
    "frame-src" => "'self' https://www.openstreetmap.org https://sway.office.com https://sway.cloud.microsoft",
    "object-src" => "'none'",
    "base-uri" => "'self'",
    "form-action" => "'self'",
    "frame-ancestors" => "'self'",
    // Send CSP violation reports to a local logging endpoint so real-world
    // violations can be caught in production instead of relying only on
    // manual testing. report-to is the modern replacement for report-uri;
    // both are sent since browser support for report-to's Reporting-Endpoints
    // header is still inconsistent.
    "report-uri" => "/csp-report.php",
    "report-to" => "csp-endpoint"
];

// Only add upgrade-insecure-requests in production environments
if (
    getenv('ENVIRONMENT') !== 'development' &&
    !isset($_SERVER['HTTP_HOST']) ||
    (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== 'localhost:8080')
) {
    $cspDirectives["upgrade-insecure-requests"] = "";
}

// Build the CSP header value
$cspValue = '';
foreach ($cspDirectives as $directive => $value) {
    if (!empty($value)) {
        $cspValue .= $directive . ' ' . $value . '; ';
    } else {
        $cspValue .= $directive . '; ';
    }
}

// Set Content Security Policy header
header("Content-Security-Policy: " . trim($cspValue));

// Register the "csp-endpoint" group referenced by the CSP's report-to
// directive above (modern Reporting API). Report-To is the legacy header
// form still needed for browsers that don't yet support Reporting-Endpoints.
header('Reporting-Endpoints: csp-endpoint="/csp-report.php"');
header('Report-To: ' . json_encode([
    'group' => 'csp-endpoint',
    'max_age' => 10886400,
    'endpoints' => [['url' => '/csp-report.php']]
]));

// X-Content-Type-Options
// Prevents browsers from MIME-sniffing a response away from the declared content-type
header("X-Content-Type-Options: nosniff");

// X-Frame-Options
// Protects against clickjacking attacks
header("X-Frame-Options: SAMEORIGIN");

// X-XSS-Protection
// Enables the cross-site scripting (XSS) filter in browsers
header("X-XSS-Protection: 1; mode=block");

// Referrer-Policy
// Controls how much referrer information should be included with requests
header("Referrer-Policy: strict-origin-when-cross-origin");

// Permissions-Policy (formerly Feature-Policy)
// Allows a site to control which features and APIs can be used in the browser
header("Permissions-Policy: geolocation=(), microphone=(), camera=()");

// Strict-Transport-Security (HSTS)
// Forces browsers to use HTTPS for the specified domain
// Only set HSTS in production environments
if (
    isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' &&
    (getenv('ENVIRONMENT') !== 'development' &&
        (!isset($_SERVER['HTTP_HOST']) ||
            (isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST'] !== 'localhost' && $_SERVER['HTTP_HOST'] !== 'localhost:8080')))
) {
    header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload");
}
