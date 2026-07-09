-- Phase 2: Backend Security Database Migrations
-- Created: 2024
-- Purpose: Schema for authentication, RBAC, and audit logging

-- ====================================================================
-- ALTER USERS TABLE - Add security-related columns
-- ====================================================================

ALTER TABLE users ADD COLUMN IF NOT EXISTS password_hash VARCHAR(255) NOT NULL DEFAULT '' AFTER password;
ALTER TABLE users ADD COLUMN IF NOT EXISTS password_changed DATETIME DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token VARCHAR(255) DEFAULT NULL UNIQUE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS reset_token_expires DATETIME DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_login DATETIME DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS last_password_change DATETIME DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS status VARCHAR(50) NOT NULL DEFAULT 'active' COMMENT 'active, inactive, suspended, deleted';
ALTER TABLE users ADD COLUMN IF NOT EXISTS role VARCHAR(50) NOT NULL DEFAULT 'member' COMMENT 'User role for RBAC';
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_enabled BOOLEAN DEFAULT FALSE;
ALTER TABLE users ADD COLUMN IF NOT EXISTS two_factor_secret VARCHAR(255) DEFAULT NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS backup_codes LONGTEXT DEFAULT NULL COMMENT 'JSON array of backup codes';
ALTER TABLE users ADD COLUMN IF NOT EXISTS created_at DATETIME DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE users ADD COLUMN IF NOT EXISTS updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP;

-- Add indexes for performance
CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_users_status ON users(status);
CREATE INDEX IF NOT EXISTS idx_users_created_at ON users(created_at);

-- ====================================================================
-- ROLES TABLE - Define application roles
-- ====================================================================

CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Role name (admin, member, moderator, etc)',
    description TEXT,
    level INT DEFAULT 0 COMMENT 'Role hierarchy level for comparison',
    is_system BOOLEAN DEFAULT FALSE COMMENT 'System roles cannot be deleted',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_roles_name (name),
    INDEX idx_roles_level (level)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Application roles for role-based access control';

-- ====================================================================
-- PERMISSIONS TABLE - Define granular permissions
-- ====================================================================

CREATE TABLE IF NOT EXISTS permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE COMMENT 'Permission identifier (e.g., booking.view)',
    description TEXT,
    category VARCHAR(50) DEFAULT NULL COMMENT 'Permission category/module (booking, user, admin)',
    is_system BOOLEAN DEFAULT FALSE COMMENT 'System permissions cannot be deleted',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_permissions_name (name),
    INDEX idx_permissions_category (category)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Granular permissions for fine-grained access control';

-- ====================================================================
-- ROLE_PERMISSIONS TABLE - Map roles to permissions
-- ====================================================================

CREATE TABLE IF NOT EXISTS role_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    role_id INT NOT NULL,
    permission_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_role_permission (role_id, permission_id),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    INDEX idx_role_permissions_role_id (role_id),
    INDEX idx_role_permissions_permission_id (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Junction table mapping roles to permissions';

-- ====================================================================
-- USER_PERMISSIONS TABLE - Grant direct permissions to users
-- ====================================================================

CREATE TABLE IF NOT EXISTS user_permissions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    permission_id INT NOT NULL,
    granted_by INT DEFAULT NULL COMMENT 'Admin who granted this permission',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_user_permission (user_id, permission_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_permissions_user_id (user_id),
    INDEX idx_user_permissions_permission_id (permission_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Junction table for user-specific permissions outside of roles';

-- ====================================================================
-- AUDIT_LOGS TABLE - Comprehensive audit trail
-- ====================================================================

CREATE TABLE IF NOT EXISTS audit_logs (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    type VARCHAR(50) NOT NULL COMMENT 'Event type: security, auth, user_action, data_change, access, error, admin',
    action VARCHAR(100) NOT NULL COMMENT 'Specific action performed',
    user_id INT DEFAULT NULL COMMENT 'User who performed action',
    ip_address VARCHAR(45) DEFAULT NULL COMMENT 'IPv4 or IPv6 address',
    user_agent TEXT DEFAULT NULL COMMENT 'Browser user agent string',
    referer VARCHAR(255) DEFAULT NULL COMMENT 'HTTP referer',
    data LONGTEXT DEFAULT NULL COMMENT 'Additional JSON data about the event',
    status VARCHAR(50) DEFAULT 'completed' COMMENT 'Event status: completed, failed, pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_audit_logs_type (type),
    INDEX idx_audit_logs_action (action),
    INDEX idx_audit_logs_user_id (user_id),
    INDEX idx_audit_logs_created_at (created_at),
    INDEX idx_audit_logs_ip (ip_address),
    INDEX idx_audit_logs_type_created (type, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
COMMENT='Comprehensive audit log for security and compliance tracking';

-- ====================================================================
-- INSERT DEFAULT ROLES
-- ====================================================================

INSERT IGNORE INTO roles (id, name, description, level, is_system) VALUES
(1, 'admin', 'Administrator with full system access', 3, TRUE),
(2, 'moderator', 'Moderator with content management permissions', 2, TRUE),
(3, 'member', 'Regular member with limited permissions', 1, TRUE),
(4, 'guest', 'Guest user with read-only access', 0, TRUE);

-- ====================================================================
-- INSERT DEFAULT PERMISSIONS
-- ====================================================================

-- Booking Permissions
INSERT IGNORE INTO permissions (name, description, category, is_system) VALUES
('booking.view', 'View available bookings', 'booking', TRUE),
('booking.create', 'Create new booking', 'booking', TRUE),
('booking.view_own', 'View own bookings', 'booking', TRUE),
('booking.edit_own', 'Edit own bookings', 'booking', TRUE),
('booking.edit', 'Edit any booking', 'booking', TRUE),
('booking.delete_own', 'Delete own bookings', 'booking', TRUE),
('booking.delete', 'Delete any booking', 'booking', TRUE),
('booking.approve', 'Approve bookings', 'booking', TRUE),
('booking.view_all', 'View all bookings', 'booking', TRUE);

-- User Permissions
INSERT IGNORE INTO permissions (name, description, category, is_system) VALUES
('user.view_profile', 'View user profiles', 'user', TRUE),
('user.edit_own_profile', 'Edit own profile', 'user', TRUE),
('user.edit_profile', 'Edit any profile', 'user', TRUE),
('user.view_list', 'View user list', 'user', TRUE),
('user.change_password', 'Change own password', 'user', TRUE);

-- Admin Permissions
INSERT IGNORE INTO permissions (name, description, category, is_system) VALUES
('admin.user_management', 'Manage users', 'admin', TRUE),
('admin.role_management', 'Manage roles and permissions', 'admin', TRUE),
('admin.audit_logs', 'View audit logs', 'admin', TRUE),
('admin.system_settings', 'Modify system settings', 'admin', TRUE),
('admin.email_management', 'Send system emails', 'admin', TRUE),
('admin.database', 'Database administration', 'admin', TRUE);

-- Events Permissions
INSERT IGNORE INTO permissions (name, description, category, is_system) VALUES
('event.view', 'View events', 'event', TRUE),
('event.create', 'Create events', 'event', TRUE),
('event.edit_own', 'Edit own events', 'event', TRUE),
('event.edit', 'Edit any event', 'event', TRUE),
('event.delete_own', 'Delete own events', 'event', TRUE),
('event.delete', 'Delete any event', 'event', TRUE);

-- ====================================================================
-- ASSIGN PERMISSIONS TO ROLES
-- ====================================================================

-- Admin - Has all permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id) 
SELECT 1, id FROM permissions WHERE is_system = TRUE;

-- Moderator - Content management
INSERT IGNORE INTO role_permissions (role_id, permission_id) 
SELECT 2, id FROM permissions 
WHERE category IN ('booking', 'event', 'user') AND is_system = TRUE;

INSERT IGNORE INTO role_permissions (role_id, permission_id) 
SELECT 2, id FROM permissions 
WHERE name IN ('admin.audit_logs') AND is_system = TRUE;

-- Member - Basic permissions
INSERT IGNORE INTO role_permissions (role_id, permission_id) 
SELECT 3, id FROM permissions 
WHERE name IN (
    'booking.view', 'booking.create', 'booking.view_own', 'booking.edit_own', 'booking.delete_own',
    'event.view', 'event.create', 'event.edit_own', 'event.delete_own',
    'user.view_profile', 'user.edit_own_profile', 'user.change_password'
) AND is_system = TRUE;

-- Guest - Read-only
INSERT IGNORE INTO role_permissions (role_id, permission_id) 
SELECT 4, id FROM permissions 
WHERE name IN ('booking.view', 'event.view', 'user.view_profile') AND is_system = TRUE;

-- ====================================================================
-- VERIFY TABLES CREATED
-- ====================================================================

-- Show table structures
SHOW TABLES LIKE 'roles';
SHOW TABLES LIKE 'permissions';
SHOW TABLES LIKE 'role_permissions';
SHOW TABLES LIKE 'user_permissions';
SHOW TABLES LIKE 'audit_logs';

-- Verify users table updates
DESCRIBE users;
