-- Create users table
CREATE TABLE IF NOT EXISTS users
(
    id         INT AUTO_INCREMENT PRIMARY KEY,
    username   VARCHAR(50)            NOT NULL UNIQUE,
    password   VARCHAR(255)           NOT NULL,
    email      VARCHAR(100)           NOT NULL UNIQUE,
    role       ENUM ('admin', 'user') NOT NULL DEFAULT 'user',
    created_at DATETIME               NOT NULL,
    last_login DATETIME               NULL
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

-- Create default admin user (password: admin123)
-- Note: In production, change this password immediately after installation
INSERT INTO users (username, password, email, role, created_at)
VALUES ('admin', '$2y$12$5ZKuRJKBGJu7CWn5NOKnZOXlrqxBmO.7n.Cl9GrOHWUUUbcwO/Nv.', 'admin@example.com', 'admin', NOW());