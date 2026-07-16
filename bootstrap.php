<?php
// Include autoloaders
require_once "vendor/autoload.php";

// Simple PSR-4 autoloader for App namespace as fallback
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/assets/class/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

use App\EventManager;

// Load environment variables (before security headers, so CSP can reference them)
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Set security headers
include "assets/config/security_headers.php";

libxml_use_internal_errors(true);

// Load XML file
$xmlFilePath = __DIR__ . "/arrangementer.xml";
if (!file_exists($xmlFilePath)) {
    error_log("Error: arrangementer.xml not found at $xmlFilePath");
    die("Error: Events file not found.");
}

$xml = simplexml_load_file($xmlFilePath);
if ($xml === false) {
    $errors = libxml_get_errors();
    foreach ($errors as $error) {
        error_log("XML Error: " . $error->message . " in " . $xmlFilePath);
    }
    libxml_clear_errors();
    die("Error: Could not load events. Please try again later.");
}
$event = new EventManager();
