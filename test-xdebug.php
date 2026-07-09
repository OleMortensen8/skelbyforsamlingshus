#!/usr/bin/env php
<?php
/**
 * Test script to verify Xdebug installation and code coverage capability
 * 
 * This script checks if Xdebug is installed and properly configured for code coverage.
 * Run this script after installing Xdebug to verify that everything is working correctly.
 */

echo "Testing Xdebug installation...\n";
echo "-----------------------------\n\n";

// Check if Xdebug is installed
if (!extension_loaded('xdebug')) {
    echo "❌ Xdebug is NOT installed or not enabled.\n";
    echo "Please run the install-xdebug.sh script to install Xdebug.\n";
    exit(1);
}

echo "✅ Xdebug is installed.\n";

// Get Xdebug version
$version = phpversion('xdebug');
echo "   Version: $version\n\n";

// Check Xdebug mode
$mode = ini_get('xdebug.mode');
if (empty($mode)) {
    echo "⚠️ xdebug.mode is not set in your php.ini file.\n";
    echo "   For code coverage, you should set xdebug.mode to include 'coverage'.\n";
} else {
    echo "   Mode: $mode\n";
    if (strpos($mode, 'coverage') !== false) {
        echo "✅ Coverage mode is enabled.\n";
    } else {
        echo "❌ Coverage mode is NOT enabled.\n";
        echo "   Please add 'coverage' to xdebug.mode in your php.ini file.\n";
        echo "   Example: xdebug.mode=coverage,develop,debug\n";
    }
}

echo "\n";

// Check if code coverage is available
if (function_exists('xdebug_code_coverage_started')) {
    echo "✅ Code coverage functions are available.\n";
} else {
    echo "❌ Code coverage functions are NOT available.\n";
    echo "   This might be because coverage mode is not enabled.\n";
}

echo "\n";

// Check if PHPUnit can use Xdebug for code coverage
echo "Testing PHPUnit code coverage capability...\n";
$output = [];
$return_var = 0;
exec('vendor/bin/phpunit --version', $output, $return_var);

if ($return_var !== 0) {
    echo "❌ Could not run PHPUnit. Make sure it's installed.\n";
} else {
    echo "✅ PHPUnit is installed: " . $output[0] . "\n";

    // Run a simple test with coverage
    echo "\nRunning a simple test with coverage...\n";
    $output = [];
    exec('vendor/bin/phpunit --coverage-text tests/BookableCellTest.php 2>&1', $output);

    $coverage_error = false;
    $coverage_success = false;

    foreach ($output as $line) {
        if (strpos($line, 'No code coverage driver') !== false) {
            $coverage_error = true;
        }
        if (strpos($line, 'Code Coverage Report') !== false) {
            $coverage_success = true;
        }
    }

    if ($coverage_error) {
        echo "❌ PHPUnit could not generate code coverage. Check the Xdebug configuration.\n";
    } elseif ($coverage_success) {
        echo "✅ PHPUnit successfully generated code coverage.\n";
        echo "   You can now run: ./generate-coverage.sh\n";
    } else {
        echo "⚠️ Could not determine if code coverage is working.\n";
        echo "   Try running: ./generate-coverage.sh --no-browser\n";
    }
}

echo "\n-----------------------------\n";
echo "For more information about Xdebug, visit: https://xdebug.org/docs/\n";
