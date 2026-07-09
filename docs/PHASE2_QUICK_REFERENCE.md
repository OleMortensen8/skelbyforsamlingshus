# Phase 2 Security - Quick Reference Guide

## Quick Start

### 1. Initialize Security Components

```php
<?php
require_once 'bootstrap.php';

// Create database connection
$database = new Database();

// Initialize authentication
$auth = new Authentication($database);

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();
?>
```

### 2. Protect a Form with CSRF Token

```php
<?php
$csrfProtection = new CsrfProtection($auth->getSessionManager());
$token = $csrfProtection->generate();
?>

<form method="post">
    <input type="hidden" name="_token" value="<?php echo Sanitizer::escapeAttr($token); ?>">
    <!-- Form fields -->
</form>
```

### 3. Validate and Sanitize Input

```php
$validator = Validator::make($_POST);
$sanitizer = new Sanitizer();

$validator
    ->required('email')
    ->email('email')
    ->required('name')
    ->minLength('name', 2);

if ($validator->fails()) {
    $errors = $validator->getErrors('email');
} else {
    $email = $sanitizer->sanitizeEmail($_POST['email']);
    $name = $sanitizer->sanitizeString($_POST['name']);
}
```

### 4. Check User Permissions

```php
$auth = new Authorization($database, $userId, $userRole);

if ($auth->can('booking.view')) {
    // Show bookings
}

if (!$auth->can('booking.delete')) {
    $error = "You don't have permission to delete";
}
```

### 5. Log Security Events

```php
$auditLogger = new AuditLogger($database);

$auditLogger->logAuthEvent('login_success', $userId, ['email' => $email]);
$auditLogger->logSecurityEvent('password_changed', [], $userId);
$auditLogger->logUserAction('created_booking', ['booking_id' => 123], $userId);
```

---

## Common Patterns

### Pattern: Protected Page

```php
<?php
require_once 'bootstrap.php';

$database = new Database();
$auth = new Authentication($database);
$authorization = new Authorization($database);

// Require login
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$user = $auth->getCurrentUser();

// Require specific permission
if (!$authorization->can('booking.view')) {
    http_response_code(403);
    die('Access Denied');
}

// Now safe to load page
?>
```

### Pattern: Form Handler

```php
<?php
$csrfProtection = new CsrfProtection($auth->getSessionManager());
$validator = Validator::make($_POST);
$sanitizer = new Sanitizer();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF
    if (!$csrfProtection->verify($_POST['_token'] ?? null)) {
        $error = 'Invalid security token';
    }
    
    // Validate input
    $validator->required('field1')->minLength('field1', 2);
    
    if ($validator->fails()) {
        $errors = $validator->getErrors();
    } else {
        // Sanitize
        $field1 = $sanitizer->sanitizeString($_POST['field1']);
        
        // Process...
        $auditLogger->logUserAction('action_taken', ['field' => $field1], $userId);
    }
}
?>
```

### Pattern: API Endpoint

```php
<?php
header('Content-Type: application/json');

$database = new Database();
$auth = new Authentication($database);

if (!$auth->isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$csrfProtection = new CsrfProtection($auth->getSessionManager());
if (!$csrfProtection->verify($_POST['_token'] ?? null)) {
    http_response_code(403);
    echo json_encode(['error' => 'CSRF validation failed']);
    exit;
}

$validator = Validator::make($_POST);
$validator->required('id')->integer('id');

if ($validator->fails()) {
    http_response_code(400);
    echo json_encode(['errors' => $validator->getErrors()]);
    exit;
}

// Process request
echo json_encode(['success' => true, 'data' => $result]);
?>
```

---

## Class Reference

### Authentication
```php
// Login/Logout
$result = $auth->login($email, $password, $rememberMe);
$auth->logout();

// User Info
$auth->isLoggedIn()
$auth->getCurrentUser()
$auth->getCurrentUserId()
$auth->getCurrentUserRole()

// Password Management
$auth->changePassword($current, $new)
$auth->requestPasswordReset($email)
$auth->resetPassword($token, $newPassword)

// Advanced
$auth->verifyCurrentPassword($password)
$auth->requireReAuth($reason)
```

### CsrfProtection
```php
$token = $csrf->generate();
$csrf->verify($_POST['_token'] ?? null);
$csrf->getToken('input');  // HTML input
$csrf->regenerate();
$csrf->validateOrigin();
```

### Validator
```php
$v = Validator::make($_POST);
$v->required('field');
$v->email('email');
$v->minLength('name', 2);
$v->password('pwd');
$v->passes();
$v->fails();
$v->getErrors('field');
```

### Sanitizer
```php
Sanitizer::sanitize($input, 'string');
Sanitizer::sanitizeEmail($email);
Sanitizer::sanitizeUrl($url);
Sanitizer::escape($html);          // HTML context
Sanitizer::escapeAttr($attr);      // HTML attribute
Sanitizer::escapeJs($js);          // JavaScript
Sanitizer::escapeCss($css);        // CSS
Sanitizer::escapeJson($data);      // JSON
```

### Authorization
```php
$auth = new Authorization($database, $userId, $role);
$auth->can('permission.name');
$auth->hasRole('admin');
$auth->canAccessResource('booking', 'edit', $ownerId);
$auth->grantPermissionToUser($userId, 'permission');
```

### AuditLogger
```php
$logger = new AuditLogger($database);
$logger->log('type', 'action', $data, $userId);
$logger->logAuthEvent('login_success', $userId);
$logger->logSecurityEvent('suspicious_activity', $data);
$logger->getLogs(['user_id' => $userId], 50);
$logger->getSecurityReport(30);
```

### PasswordManager
```php
$pm = new PasswordManager();
$hash = $pm->hash($password);
$pm->verify($password, $hash);
$pm->validatePolicy($password);  // Returns errors array
$pm->generatePassword(16);
$pm->getStrength($password);     // 0-100
```

### SessionManager
```php
$sm = new SessionManager();
$sm->start();
$sm->set('key', $value);
$sm->get('key');
$sm->delete('key');
$sm->regenerateId(true);
$sm->destroy();
```

---

## Error Handling

### Authentication Errors
```php
$result = $auth->login($email, $password);
if (!$result['success']) {
    $error = $result['error'];        // User-friendly message
    $code = $result['code'];          // Error code
    
    // Common codes:
    // RATE_LIMITED - Too many attempts
    // INVALID_EMAIL - Invalid format
    // ACCOUNT_INACTIVE - Account disabled
    // INVALID_CREDENTIALS - Wrong password
}
```

### Validation Errors
```php
if ($validator->fails()) {
    foreach ($validator->getErrors() as $field => $messages) {
        echo "Field: $field<br>";
        foreach ($messages as $message) {
            echo "  - $message<br>";
        }
    }
}
```

### Security Errors
```php
if (!$csrfProtection->verify($_POST['_token'] ?? null)) {
    // Log and show generic error
    http_response_code(403);
    die('Security validation failed');
}
```

---

## Configuration

### Override Settings

Create `config/security_local.php`:
```php
<?php
return [
    'session' => [
        'lifetime' => 7200,  // 2 hours
        'idle_timeout' => 1800,  // 30 minutes
    ],
    'login_attempts' => [
        'max_attempts' => 10,
        'lockout_duration' => 1800,
    ]
];
?>
```

Load in bootstrap.php:
```php
$config = array_replace_recursive(
    include 'config/security.php',
    file_exists('config/security_local.php') ? include 'config/security_local.php' : []
);
```

---

## Performance Tips

1. **Cache Permissions:**
   - Authorization class caches permissions in memory
   - Clear cache when permissions change: `$auth->clearCache()`

2. **Batch Operations:**
   - Use prepared statements for database queries
   - Minimize database calls in loops

3. **Session Efficiency:**
   - Don't store large objects in session
   - Use session only for user ID and role

4. **Password Hashing:**
   - Use cost 10 in development (faster testing)
   - Use cost 12 in production (more secure)

---

## Security Checklist

- [ ] All user inputs validated with Validator
- [ ] All user outputs escaped with Sanitizer
- [ ] All forms protected with CSRF tokens
- [ ] Protected pages check `isLoggedIn()`
- [ ] Admin pages check permissions
- [ ] Database queries use prepared statements
- [ ] Sensitive data logged without passwords
- [ ] Error messages don't reveal system details
- [ ] Passwords never logged or displayed
- [ ] HTTPS enforced in production
- [ ] Security headers configured
- [ ] Audit logs reviewed regularly

---

## Database Indexes

Check that these indexes exist for performance:
```sql
-- Users
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_role ON users(role);
CREATE INDEX idx_users_status ON users(status);

-- Audit Logs
CREATE INDEX idx_audit_logs_user_id ON audit_logs(user_id);
CREATE INDEX idx_audit_logs_created_at ON audit_logs(created_at);
CREATE INDEX idx_audit_logs_type ON audit_logs(type);
```

---

## Troubleshooting

### "Session expired"
- Check `SESSION_IDLE_TIMEOUT` (default 15 min)
- Verify session storage is writable
- Check system clock synchronization

### CSRF token invalid
- Ensure browser accepts cookies
- Check HTTPS vs HTTP mismatch
- Verify form method is POST
- Ensure token is being generated

### Permission denied error
- Verify user's role in database
- Check role has required permission
- Ensure user isn't soft-deleted
- Clear permission cache: `$auth->clearCache()`

### Audit logs not recording
- Check logs directory is writable
- Verify database connection working
- Check audit_logs table exists
- Review error logs for database errors

---

## Testing Endpoints

Use these curl commands to test:

```bash
# Test login
curl -X POST http://localhost:8000/login.php \
  -d "email=user@example.com&password=Password123!"

# Test protected endpoint
curl -H "Cookie: PHPSESSID=xxx" \
  http://localhost:8000/protected.php

# Test API with CSRF
curl -X POST http://localhost:8000/api/endpoint \
  -d "_token=xxx&data=value" \
  -H "Cookie: PHPSESSID=xxx"
```

---

## Version History

- **v1.0** - Phase 2 Implementation
  - Authentication system
  - RBAC authorization
  - CSRF protection
  - Input validation & sanitization
  - Audit logging
  - Date: 2024

---

## Additional Resources

- [OWASP Top 10](https://owasp.org/www-project-top-ten/)
- [PHP Security Best Practices](https://www.php.net/manual/en/security.php)
- [NIST Cybersecurity Framework](https://www.nist.gov/cyberframework)
- [CWE Top 25](https://cwe.mitre.org/top25/)

