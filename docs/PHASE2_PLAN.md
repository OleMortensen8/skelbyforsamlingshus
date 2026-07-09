# Phase 2: Backend Security & Authentication - Implementation Plan

**Status:** Ready to Start  
**Target Duration:** 3 weeks  
**Priority:** HIGH - Foundation for all backend work

---

## Overview

Phase 2 focuses on building a robust, secure authentication and authorization system. This foundation will support all subsequent phases (data layer, controllers, error handling).

---

## Phase 2 Objectives

1. ✅ Implement proper authentication system (login/logout)
2. ✅ Create role-based access control (RBAC)
3. ✅ Add comprehensive security measures
4. ✅ Build audit logging system
5. ✅ Establish security best practices
6. ✅ Create security headers middleware

---

## Detailed Tasks

### 2.1 User Authentication System

#### Files to Create/Modify:

**`assets/class/Authentication.php`** - Core authentication logic
```
Responsibilities:
- Login/logout functionality
- Password verification with bcrypt
- Session management
- Token generation
- Login attempt tracking
- Password reset handling
```

**`assets/class/PasswordManager.php`** - Password handling
```
Methods:
- hash($password) - Hash with bcrypt
- verify($password, $hash) - Verify password
- generateResetToken() - Generate reset token
- validateResetToken($token) - Validate token
- requiresRehash($hash) - Check if rehash needed
```

**`assets/class/SessionManager.php`** - Session handling
```
Methods:
- start() - Initialize secure session
- destroy() - End session
- regenerate() - Prevent fixation
- setTimeout($minutes) - Set timeout
- isExpired() - Check if expired
- touch() - Update activity time
```

#### Key Features:
- Password hashing with `password_hash()` (PHP built-in)
- Secure password verification
- Session regeneration on login
- Session timeout mechanism
- Configurable session duration
- Activity tracking

#### Security Measures:
- HTTPOnly cookies (prevent XSS access)
- Secure flag (HTTPS only)
- SameSite attribute (CSRF protection)
- Session ID rotation
- Login rate limiting
- Failed attempt tracking

---

### 2.2 Role-Based Access Control (RBAC)

#### Files to Create:

**`assets/class/Permission.php`** - Permission management
```
Responsibilities:
- Define application permissions
- Check user permissions
- Grant/revoke permissions
```

**`assets/class/Role.php`** - Role management
```
Roles:
- admin: Full system access
- organizer: Can create/manage events
- member: Can book, view events
- guest: View-only access
```

**`assets/class/Authorization.php`** - Authorization logic
```
Methods:
- can($permission) - Check permission
- canAny($permissions) - Check any permission
- canAll($permissions) - Check all permissions
- authorize($permission) - Throw if denied
```

#### Role Definitions:

```php
// Admin - Full access
Admin:
  - manage_users
  - manage_events
  - manage_bookings
  - manage_settings
  - view_analytics
  - manage_members
  - view_audit_log

// Organizer - Can create/manage events
Organizer:
  - create_event
  - edit_own_events
  - view_bookings
  - manage_event_bookings
  - view_reports

// Member - Can use services
Member:
  - view_events
  - book_event
  - view_own_bookings
  - edit_profile
  - cancel_own_booking

// Guest - View only
Guest:
  - view_events
  - view_gallery
```

---

### 2.3 Security Headers & Middleware

#### Files to Create:

**`assets/class/SecurityMiddleware.php`** - Security headers
```
Headers to implement:
- Content-Security-Policy
- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- Referrer-Policy: strict-origin-when-cross-origin
- Permissions-Policy
- Strict-Transport-Security (HSTS)
```

**`assets/class/CsrfProtection.php`** - CSRF token handling
```
Methods:
- generateToken() - Create new token
- getToken() - Retrieve session token
- validateToken($token) - Verify token
- addToForm($form) - Add to form
```

#### Implementation:
- Apply security headers to all responses
- Generate CSRF tokens for all forms
- Validate CSRF on POST/PUT/DELETE
- Content Security Policy for resources
- Secure cookie attributes

---

### 2.4 Input Validation & Sanitization

#### Files to Create:

**`assets/class/Validator.php`** - Input validation
```
Methods:
- validate($data, $rules)
- email($value)
- url($value)
- date($value, $format)
- phone($value)
- text($value)
- integer($value)
- required($value)
- min($value, $min)
- max($value, $max)
- regex($value, $pattern)
- unique($value, $table, $column)
```

**`assets/class/Sanitizer.php`** - Output sanitization
```
Methods:
- html($value) - Escape for HTML output
- url($value) - Escape for URL
- attribute($value) - Escape for HTML attribute
- javascript($value) - Escape for JS
- sql($value) - For legacy support (prepared statements preferred)
- filename($filename) - Safe filename
```

#### Examples:
```php
// Server-side validation
$validator = new Validator();
$errors = $validator->validate($_POST, [
  'email' => 'required|email',
  'password' => 'required|min:8|max:128',
  'name' => 'required|text|max:100',
  'age' => 'integer|min:18|max:120'
]);

if (!empty($errors)) {
  // Handle validation errors
}

// Output sanitization
echo Sanitizer::html($user_input);
```

---

### 2.5 Audit Logging

#### Files to Create:

**`assets/class/AuditLog.php`** - Audit trail
```
Logged Events:
- User login/logout
- Failed login attempts
- Permission changes
- Data modifications
- Admin actions
- Security events
```

**`assets/class/AuditLogger.php`** - Logging functionality
```
Methods:
- log($action, $data)
- getLog($filters)
- getUserLog($user_id)
- getEventLog($event)
- clear($before_date)
```

#### Log Entry Structure:
```php
[
  'id' => auto-increment,
  'user_id' => who did it,
  'action' => what happened,
  'table' => affected table,
  'record_id' => affected record,
  'old_value' => previous value,
  'new_value' => new value,
  'ip_address' => user IP,
  'user_agent' => browser info,
  'timestamp' => when it happened
]
```

---

### 2.6 Database Updates

#### SQL Changes:

**Users Table Enhancement:**
```sql
ALTER TABLE users ADD COLUMN (
  password_hash VARCHAR(255) NOT NULL,
  last_login DATETIME,
  login_attempts INT DEFAULT 0,
  locked_until DATETIME,
  email_verified BOOLEAN DEFAULT FALSE,
  email_verified_at DATETIME,
  two_factor_enabled BOOLEAN DEFAULT FALSE,
  remember_token VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**New Tables:**
```sql
CREATE TABLE roles (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(50) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP
);

CREATE TABLE permissions (
  id INT PRIMARY KEY AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL UNIQUE,
  description TEXT,
  created_at TIMESTAMP
);

CREATE TABLE role_permissions (
  role_id INT,
  permission_id INT,
  PRIMARY KEY (role_id, permission_id),
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
  FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
);

CREATE TABLE user_roles (
  user_id INT,
  role_id INT,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, role_id),
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);

CREATE TABLE audit_logs (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT,
  action VARCHAR(100) NOT NULL,
  table_name VARCHAR(100),
  record_id INT,
  old_values JSON,
  new_values JSON,
  ip_address VARCHAR(45),
  user_agent TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
  INDEX (created_at),
  INDEX (user_id),
  INDEX (action)
);

CREATE TABLE login_attempts (
  id INT PRIMARY KEY AUTO_INCREMENT,
  email VARCHAR(255),
  ip_address VARCHAR(45),
  attempted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  successful BOOLEAN DEFAULT FALSE,
  INDEX (email),
  INDEX (ip_address),
  INDEX (attempted_at)
);
```

---

### 2.7 Configuration Setup

#### Files to Create:

**`config/security.php`** - Security configuration
```php
return [
  'password' => [
    'algo' => PASSWORD_BCRYPT,         // Algorithm
    'cost' => 12,                       // Cost factor
  ],
  'session' => [
    'lifetime' => 3600,                 // 1 hour
    'refresh' => 600,                   // 10 minutes idle
    'cookie_secure' => true,            // HTTPS only
    'cookie_httponly' => true,          // JS cannot access
    'cookie_samesite' => 'Strict',      // CSRF protection
  ],
  'login' => [
    'max_attempts' => 5,                // Failed attempts
    'lockout_time' => 900,              // 15 minutes
    'rate_limit' => 10,                 // Per minute
  ],
  'headers' => [
    'csp' => "default-src 'self'; ...",
    'hsts' => 'max-age=31536000',
  ]
];
```

**`config/permissions.php`** - Permission definitions
```php
return [
  'admin' => [
    'manage_users',
    'manage_events',
    'manage_bookings',
    'manage_settings',
    // ... all permissions
  ],
  'organizer' => [
    'create_event',
    'edit_own_events',
    'view_bookings',
    // ...
  ],
  // ... etc
];
```

---

### 2.8 Login/Logout Pages Update

#### Update `login.php`:
- Use new `Authentication` class
- Implement CSRF protection
- Add rate limiting
- Secure password handling
- Success/error messaging
- Remember me functionality

#### Update `logout.php`:
- Session destruction
- Audit logging
- Cookie cleanup
- CSRF token invalidation

---

### 2.9 Authorization Checks

#### Implement in Pages:
```php
<?php
// Require authentication
$auth = new Authentication();
if (!$auth->isLoggedIn()) {
  header('Location: /login.php');
  exit;
}

// Require specific role
$user = $auth->getUser();
if (!$user->hasRole('admin')) {
  http_response_code(403);
  include 'assets/view/403.php';
  exit;
}

// Check specific permission
if (!$user->can('manage_events')) {
  http_response_code(403);
  include 'assets/view/403.php';
  exit;
}
?>
```

---

## Implementation Order

1. **Week 1:**
   - [ ] Create authentication classes
   - [ ] Password manager
   - [ ] Session manager
   - [ ] Database migration
   - [ ] Configuration files

2. **Week 2:**
   - [ ] RBAC system (Role, Permission, Authorization)
   - [ ] Security headers
   - [ ] CSRF protection
   - [ ] Input validation & sanitization
   - [ ] Audit logging

3. **Week 3:**
   - [ ] Login/logout functionality
   - [ ] Authorization checks in pages
   - [ ] Rate limiting
   - [ ] Error handling
   - [ ] Testing & verification
   - [ ] Documentation

---

## Testing Requirements

### Unit Tests:
- [ ] Password hashing and verification
- [ ] Session management
- [ ] Permission checking
- [ ] Validator rules
- [ ] Sanitizer functions
- [ ] Token generation

### Integration Tests:
- [ ] Login flow
- [ ] Logout flow
- [ ] Permission enforcement
- [ ] CSRF protection
- [ ] Rate limiting
- [ ] Audit logging

### Security Tests:
- [ ] SQL injection attempts
- [ ] XSS attempts
- [ ] CSRF attacks
- [ ] Session fixation
- [ ] Brute force protection
- [ ] Password reset security

---

## Security Checklist

- [ ] All passwords hashed with bcrypt
- [ ] All forms have CSRF tokens
- [ ] All inputs validated server-side
- [ ] All outputs properly escaped
- [ ] Security headers sent
- [ ] HTTPS enforced
- [ ] Cookies marked secure/httponly
- [ ] Session timeouts configured
- [ ] Failed logins rate limited
- [ ] Audit logs maintained
- [ ] No sensitive data in logs
- [ ] No debug info in production

---

## Success Criteria

- ✅ Complete authentication system working
- ✅ Role-based access control enforced
- ✅ All security headers present
- ✅ No OWASP Top 10 vulnerabilities
- ✅ Audit logs recorded correctly
- ✅ 80%+ test coverage
- ✅ Documentation complete
- ✅ Security code review passed

---

## Dependencies

- PHP 8.1+ (for modern password functions)
- MySQL 5.7+ (for JSON in audit logs)
- Existing database from Phase 1 setup

---

## Rollout Strategy

1. **Local Testing**: Develop and test locally
2. **Staging**: Deploy to staging environment
3. **Security Review**: External security review
4. **Gradual Rollout**: 
   - New users use new system first
   - Migrate existing users
   - Monitor for issues
5. **Full Deployment**: Once validated

---

## Risks & Mitigations

| Risk | Mitigation |
|------|-----------|
| Session hijacking | HTTPOnly, Secure, SameSite cookies |
| Brute force | Rate limiting, account lockout |
| Weak passwords | Enforce minimum complexity |
| Password leaks | Bcrypt hashing, not reversible |
| CSRF attacks | Token validation on all forms |
| Session fixation | Token regeneration on login |

---

## Documentation Required

- [ ] Security implementation guide
- [ ] Authentication API documentation
- [ ] RBAC setup instructions
- [ ] Security headers explanation
- [ ] Audit log reference
- [ ] Developer security guidelines

---

## Handoff to Phase 3

After Phase 2 completion, Phase 3 (Database & ORM Layer) will:
- Build repositories with security context
- Use Permission system in queries
- Implement audit logging in data access
- Use authenticated user context

---

**Ready for Phase 2 Implementation**

This foundation will make all subsequent phases more secure and maintainable.
