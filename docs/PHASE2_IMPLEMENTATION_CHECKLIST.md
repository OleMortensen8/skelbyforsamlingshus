# Phase 2 Security - Implementation Checklist

## Pre-Implementation

- [ ] Back up current database
- [ ] Back up entire application
- [ ] Create feature branch: `git checkout -b feature/phase2-security`
- [ ] Review [PHASE2_SECURITY.md](./PHASE2_SECURITY.md)
- [ ] Review [PHASE2_QUICK_REFERENCE.md](./PHASE2_QUICK_REFERENCE.md)
- [ ] Set up test environment

## Database Setup

- [ ] Create database tables from migration file:
  ```bash
  mysql -u username -p database_name < assets/sql/phase2_security_migrations.sql
  ```

- [ ] Verify tables created:
  ```sql
  SHOW TABLES LIKE 'roles';
  SHOW TABLES LIKE 'permissions';
  SHOW TABLES LIKE 'role_permissions';
  SHOW TABLES LIKE 'user_permissions';
  SHOW TABLES LIKE 'audit_logs';
  ```

- [ ] Verify users table columns added:
  ```sql
  DESCRIBE users;
  ```

- [ ] Check default roles inserted:
  ```sql
  SELECT * FROM roles;
  ```

- [ ] Check default permissions inserted:
  ```sql
  SELECT COUNT(*) FROM permissions;
  ```

## Migrate Existing Users

- [ ] Hash existing passwords for all users:
  ```php
  // Create migration script: admin/migrate_passwords.php
  $passwordManager = new PasswordManager();
  $users = $database->findMany('users', []);
  foreach ($users as $user) {
      if (empty($user['password_hash']) && !empty($user['password'])) {
          $hash = $passwordManager->hash($user['password']);
          $database->update('users', 
              ['password_hash' => $hash],
              ['id' => $user['id']]
          );
      }
  }
  ```

- [ ] Assign roles to existing users:
  ```sql
  UPDATE users SET role = 'member' WHERE role IS NULL OR role = '';
  ```

- [ ] Verify all users have password hashes:
  ```sql
  SELECT COUNT(*) FROM users WHERE password_hash IS NULL OR password_hash = '';
  ```

## Configuration Setup

- [ ] Create `.env` file in project root:
  ```
  APP_ENV=production
  DB_HOST=localhost
  DB_NAME=skelby_forsamlinghus
  DB_USER=root
  DB_PASS=password
  SECURITY_BCRYPT_COST=12
  SESSION_LIFETIME=3600
  SESSION_IDLE_TIMEOUT=900
  LOGIN_MAX_ATTEMPTS=5
  LOGIN_LOCKOUT_DURATION=900
  ```

- [ ] Verify bootstrap.php loads security config:
  ```php
  $config = include 'config/security.php';
  ```

- [ ] Test environment-specific overrides (if any)

## Update Core Files

### bootstrap.php

- [ ] Add security class autoloading:
  ```php
  // After existing includes
  require_once 'assets/class/PasswordManager.php';
  require_once 'assets/class/SessionManager.php';
  require_once 'assets/class/Authentication.php';
  require_once 'assets/class/CsrfProtection.php';
  require_once 'assets/class/Validator.php';
  require_once 'assets/class/Sanitizer.php';
  require_once 'assets/class/Authorization.php';
  require_once 'assets/class/AuditLogger.php';
  ```

- [ ] Or use PSR-4 autoloader (better approach)

### index.php (Entry Point)

- [ ] Verify it initializes Authentication
- [ ] Check it sanitizes all user inputs
- [ ] Ensure CSRF tokens on all forms

### Protected Pages

For each page requiring login (`booking.php`, `medlem.php`, etc):

- [ ] Add authentication check:
  ```php
  $auth = new Authentication(new Database());
  if (!$auth->isLoggedIn()) {
      header('Location: login.php');
      exit;
  }
  ```

- [ ] Add permission check if needed:
  ```php
  $authorization = new Authorization($database, $userId, $userRole);
  if (!$authorization->can('booking.view')) {
      http_response_code(403);
      die('Access Denied');
  }
  ```

- [ ] Add CSRF tokens to all forms:
  ```php
  $csrfProtection = new CsrfProtection($auth->getSessionManager());
  $token = $csrfProtection->generate();
  ```

## Update Forms

For each form in the application:

- [ ] Add CSRF token hidden field:
  ```html
  <input type="hidden" name="_token" value="<?php echo Sanitizer::escapeAttr($csrfToken); ?>">
  ```

- [ ] Add input validation:
  ```php
  $validator = Validator::make($_POST);
  $validator->required('field')->minLength('field', 2);
  ```

- [ ] Add input sanitization:
  ```php
  $sanitizer = new Sanitizer();
  $field = $sanitizer->sanitizeString($_POST['field']);
  ```

- [ ] Add audit logging:
  ```php
  $auditLogger->logUserAction('form_submitted', ['form' => 'booking'], $userId);
  ```

## Test Authentication

- [ ] Test login with correct credentials - Should succeed
- [ ] Test login with wrong password - Should show generic error
- [ ] Test login with non-existent email - Should show generic error
- [ ] Test rate limiting:
  - [ ] Make 6 login attempts with wrong password
  - [ ] 6th attempt should be blocked
  - [ ] Wait 15 minutes (or check lockout file)
  - [ ] Should be able to login again
  
- [ ] Test CSRF protection:
  - [ ] Remove CSRF token from form
  - [ ] Submit form - Should fail with security error
  - [ ] Token should not be in hidden field
  - [ ] Should show error on submit

- [ ] Test session timeout (in development):
  - [ ] Login successfully
  - [ ] Wait for timeout (adjust config to 1 min for testing)
  - [ ] Next request should require re-login

- [ ] Test logout:
  - [ ] Login successfully
  - [ ] Click logout
  - [ ] Should show confirmation
  - [ ] Click "Yes, Log Out"
  - [ ] Should redirect to login
  - [ ] Session should be destroyed
  - [ ] Visiting protected page should redirect to login

- [ ] Test "Remember Me":
  - [ ] Login with "Remember Me" checked
  - [ ] Session should extend to 7 days
  - [ ] Browser should show session cookie with expiry

## Test Authorization

- [ ] Create test users with different roles
- [ ] Test admin user:
  - [ ] Can access all pages
  - [ ] Can perform all actions
  
- [ ] Test moderator user:
  - [ ] Can moderate content
  - [ ] Cannot access admin pages
  
- [ ] Test member user:
  - [ ] Can only view own content
  - [ ] Cannot edit others' content
  
- [ ] Test guest user:
  - [ ] Can view public content
  - [ ] Cannot perform actions

## Test Input Validation

- [ ] Email field:
  - [ ] Reject: invalid emails
  - [ ] Accept: valid emails
  
- [ ] Password field:
  - [ ] Reject: too short (< 8 chars)
  - [ ] Reject: no uppercase
  - [ ] Reject: no numbers
  - [ ] Reject: no special chars
  - [ ] Accept: valid passwords
  
- [ ] Name field:
  - [ ] Reject: too short (< 2 chars)
  - [ ] Reject: XSS attempts like `<script>`
  - [ ] Accept: valid names
  
- [ ] File uploads:
  - [ ] Reject: large files
  - [ ] Reject: dangerous file types
  - [ ] Accept: allowed file types

## Test Output Escaping

- [ ] User-generated content should be escaped:
  - [ ] HTML tags not rendered as code
  - [ ] JavaScript not executed
  - [ ] SQL injections not interpreted
  
- [ ] Check HTML output:
  ```html
  <!-- Should have quotes escaped -->
  <input value="escaped value">
  ```
  
- [ ] Check JSON output:
  ```json
  {"escaped": "\\u0022quoted\\u0022"}
  ```

## Test Audit Logging

- [ ] Login events logged:
  ```sql
  SELECT * FROM audit_logs WHERE type='auth' AND action='login_success' LIMIT 1;
  ```

- [ ] Password change events logged
- [ ] Admin actions logged
- [ ] Failed login attempts logged

## Security Headers

- [ ] Set security headers in config:
  ```php
  // In bootstrap or .htaccess
  header("X-Content-Type-Options: nosniff");
  header("X-Frame-Options: SAMEORIGIN");
  header("X-XSS-Protection: 1; mode=block");
  header("Content-Security-Policy: default-src 'self'");
  ```

- [ ] Verify headers in browser (F12 > Network)

## SSL/TLS (Production)

- [ ] Install SSL certificate
- [ ] Configure HTTPS in nginx/Apache
- [ ] Set Secure flag in session config:
  ```php
  'cookie_secure' => true, // HTTPS only
  ```

- [ ] Redirect HTTP to HTTPS
- [ ] Test mixed content warnings

## Performance Testing

- [ ] Test login page load time (should be < 100ms)
- [ ] Test protected page with authorization check (should be < 200ms)
- [ ] Monitor memory usage:
  - [ ] Check session memory usage
  - [ ] Check permission cache size
  - [ ] Check query performance

- [ ] Check database indexes:
  ```sql
  SHOW INDEX FROM users;
  SHOW INDEX FROM audit_logs;
  SHOW INDEX FROM roles;
  SHOW INDEX FROM permissions;
  ```

## Cleanup & Maintenance

- [ ] Remove old User class if no longer needed
- [ ] Remove old session_config.php if using new system
- [ ] Remove old CSRF implementation if using new system
- [ ] Set up cron job for audit log cleanup:
  ```bash
  # Run monthly
  0 0 1 * * php /path/to/maintenance/cleanup_audit_logs.php
  ```

## Documentation

- [ ] Update API documentation
- [ ] Update user manual for password requirements
- [ ] Update admin manual for permission management
- [ ] Document new environment variables
- [ ] Document new database tables
- [ ] Create troubleshooting guide

## Deployment

- [ ] Run tests on development environment
- [ ] Run tests on staging environment
- [ ] Get security review (if applicable)
- [ ] Plan maintenance window
- [ ] Create rollback plan
- [ ] Notify users of new password requirements
- [ ] Monitor logs during deployment
- [ ] Verify all features work in production
- [ ] Check performance in production

## Post-Deployment

- [ ] Monitor audit logs for issues
- [ ] Check error logs for exceptions
- [ ] Verify all users can login
- [ ] Check failed login attempts
- [ ] Verify rate limiting works
- [ ] Monitor database performance
- [ ] Check email sends (for password reset)

## Rollback Plan (If Needed)

- [ ] Restore database from backup
- [ ] Restore code from git previous commit
- [ ] Clear sessions
- [ ] Notify users

## Security Audit

- [ ] Run OWASP ZAP scan
- [ ] Run Burp Suite scan (if available)
- [ ] Code review of security implementation
- [ ] Penetration testing (if applicable)
- [ ] Verify all security headers
- [ ] Check certificate validity (HTTPS)

## Long-term Maintenance

- [ ] Update Password Policy (if needed)
- [ ] Review Audit Logs weekly
- [ ] Review Permissions monthly
- [ ] Update Security Config annually
- [ ] Monitor for security vulnerabilities
- [ ] Keep dependencies updated
- [ ] Test disaster recovery quarterly
- [ ] Conduct security training for team

## Sign-Off

- [ ] Development complete: _______________  Date: _______
- [ ] Testing complete: _______________  Date: _______
- [ ] Security review complete: _______________  Date: _______
- [ ] Deployment complete: _______________  Date: _______
- [ ] Post-deployment verification: _______________  Date: _______

---

## Common Issues & Solutions

### Issue: "Class not found" errors
**Solution:** Ensure all class files are in `assets/class/` and included in bootstrap.php

### Issue: CSRF token validation failing
**Solution:** 
- Check browser accepts cookies
- Verify token field name is `_token`
- Ensure form method is POST

### Issue: Login rate limiting not working
**Solution:**
- Check temp directory is writable
- Verify lockout_duration config
- Clear temp directory if stuck

### Issue: Audit logs not saving
**Solution:**
- Check database connection
- Verify audit_logs table exists
- Check logs directory permissions
- Review error logs

### Issue: Session timing out too quickly
**Solution:**
- Adjust SESSION_IDLE_TIMEOUT in .env
- Check system clock is correct
- Verify session storage location is not being cleared

---

## Quick Test Script

Create `admin/test_security.php`:

```php
<?php
require_once '../bootstrap.php';

echo "=== Security Configuration Test ===\n\n";

// Test password manager
echo "1. PasswordManager:\n";
$pm = new PasswordManager();
$hash = $pm->hash('TestPassword123!');
echo "   Hash created: " . (strlen($hash) > 0 ? "✓" : "✗") . "\n";
echo "   Verification: " . ($pm->verify('TestPassword123!', $hash) ? "✓" : "✗") . "\n";

// Test CSRF
echo "\n2. CsrfProtection:\n";
$sm = new SessionManager();
$sm->start();
$csrf = new CsrfProtection($sm);
$token = $csrf->generate();
echo "   Token generated: " . (strlen($token) > 0 ? "✓" : "✗") . "\n";
echo "   Token verified: " . ($csrf->verify($token) ? "✓" : "✗") . "\n";

// Test Validator
echo "\n3. Validator:\n";
$v = Validator::make(['email' => 'test@example.com']);
$v->email('email');
echo "   Validation: " . ($v->passes() ? "✓" : "✗") . "\n";

// Test Sanitizer
echo "\n4. Sanitizer:\n";
$s = new Sanitizer();
$sanitized = $s->sanitizeString('<script>alert("xss")</script>');
echo "   XSS filtered: " . (!strpos($sanitized, '<script>') ? "✓" : "✗") . "\n";

// Test Database
echo "\n5. Database Tables:\n";
$db = new Database();
$roleCount = count($db->findMany('roles', []));
echo "   Roles table: " . ($roleCount > 0 ? "✓ ($roleCount roles)" : "✗") . "\n";
$permCount = count($db->findMany('permissions', []));
echo "   Permissions table: " . ($permCount > 0 ? "✓ ($permCount permissions)" : "✗") . "\n";

echo "\n=== All systems operational ===\n";
?>
```

Run with: `php admin/test_security.php`

