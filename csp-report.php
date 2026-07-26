<?php

/**
 * CSP violation report collector.
 *
 * Browsers POST a JSON report body here whenever a page violates the
 * Content-Security-Policy declared in assets/config/security_headers.php
 * (via the report-uri/report-to directives). Reports are logged to
 * storage/logs/csp-violations.log for later review; nothing else is done
 * with them (no email/alerting) to avoid this becoming an abuse vector.
 */

require_once __DIR__ . '/bootstrap.php';

// Browsers send CSP reports as POST with Content-Type
// application/csp-report or application/reports+json. Reject anything else.
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    http_response_code(405);
    exit;
}

$rawBody = file_get_contents('php://input');

// Cap the accepted body size to prevent excessive disk usage from abuse.
if ($rawBody === false || strlen($rawBody) > 8192) {
    http_response_code(204);
    exit;
}

$decoded = json_decode($rawBody, true);
if (json_last_error() !== JSON_ERROR_NONE || empty($decoded)) {
    http_response_code(204);
    exit;
}

$logDir = __DIR__ . '/storage/logs';
if (!is_dir($logDir)) {
    mkdir($logDir, 0755, true);
}

$entry = [
    'time' => date('c'),
    'ip' => $_SERVER['REMOTE_ADDR'] ?? '',
    'report' => $decoded,
];

error_log(json_encode($entry) . PHP_EOL, 3, $logDir . '/csp-violations.log');

// Browsers don't read the response body/status meaningfully; 204 is conventional.
http_response_code(204);
