<?php
/**
 * Logout Page - Secure Session Termination
 * 
 * Implements secure logout with:
 * - Proper session destruction
 * - CSRF token verification
 * - Audit logging
 * - Confirmation prompt
 */

require_once 'bootstrap.php';

// Load security classes
$database = new App\Database();
$authentication = new App\Authentication($database);
$csrfProtection = new App\CsrfProtection($authentication->getSessionManager());
$auditLogger = new App\AuditLogger($database);

// Check if user is logged in
if (!$authentication->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Get current user info for logging
$currentUser = $authentication->getCurrentUser();

// Process logout confirmation
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!$csrfProtection->verify($_POST['_token'] ?? null)) {
        die('Invalid security token. Logout failed. <a href="index.php">Return to home</a>');
    }

    // Perform logout
    $authentication->logout();
    
    // Audit the logout
    if ($currentUser) {
        $auditLogger->logAuthEvent('logout', $currentUser['id']);
    }

    // Set success cookie for redirect page
    setcookie('logout_success', '1', time() + 10, '/', '', true, true);

    // Redirect to login page with message
    header('Location: login.php?logged_out=1');
    exit;
}

// Generate CSRF token for form
$csrfToken = $csrfProtection->generate();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Logout - Skelby Forsamlinghus</title>
    <link rel="stylesheet" href="assets/css/system.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    <style>
        .logout-container {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: var(--spacing-4);
        }

        .logout-modal {
            background: white;
            padding: var(--spacing-6);
            border-radius: var(--border-radius-lg);
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            width: 100%;
            max-width: 400px;
        }

        .logout-modal h1 {
            text-align: center;
            margin-bottom: var(--spacing-4);
            color: var(--color-text-primary);
            font-size: var(--font-size-xl);
        }

        .logout-modal p {
            text-align: center;
            margin-bottom: var(--spacing-6);
            color: var(--color-text-secondary);
            line-height: 1.6;
        }

        .user-info {
            background-color: #f5f5f5;
            padding: var(--spacing-3);
            border-radius: var(--border-radius-md);
            margin-bottom: var(--spacing-6);
            font-size: var(--font-size-sm);
        }

        .user-info strong {
            color: var(--color-text-primary);
        }

        .button-container {
            display: flex;
            gap: var(--spacing-3);
            margin-bottom: 0;
        }

        .button-container button,
        .button-container a {
            flex: 1;
            padding: var(--spacing-3);
            border: none;
            border-radius: var(--border-radius-md);
            font-size: var(--font-size-base);
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s;
            font-weight: 500;
        }

        .button-container button {
            background-color: #d9534f;
            color: white;
        }

        .button-container button:hover {
            background-color: #c9302c;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(217, 83, 79, 0.3);
        }

        .button-container a {
            background-color: var(--color-border);
            color: var(--color-text-primary);
        }

        .button-container a:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>
    <div class="logout-container">
        <div class="logout-modal">
            <h1>Logout</h1>
            
            <p>Are you sure you want to log out?</p>

            <?php if ($currentUser): ?>
                <div class="user-info">
                    <strong>Logged in as:</strong> <?php echo App\Sanitizer::escape($currentUser['name'] ?? $currentUser['email']); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="logout.php">
                <!-- CSRF Token -->
                <input type="hidden" name="_token" value="<?php echo App\Sanitizer::escapeAttr($csrfToken); ?>">

                <!-- Buttons -->
                <div class="button-container">
                    <button type="submit">Yes, Log Out</button>
                    <a href="index.php">No, Go Back</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
