-- Local Docker development schema for tables not yet covered by assets/sql migrations.
-- Table names are capitalized to match the exact casing used in the PHP queries
-- (MySQL on Linux treats table names as case-sensitive).

CREATE TABLE IF NOT EXISTS Customers
(
    customer_id INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(255) NOT NULL,
    email       VARCHAR(255) NOT NULL UNIQUE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS Bookings
(
    booking_id   INT AUTO_INCREMENT PRIMARY KEY,
    booking_date DATE NOT NULL,
    approved     TINYINT(1) NOT NULL DEFAULT 0,
    customer_id  INT NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES Customers (customer_id) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
