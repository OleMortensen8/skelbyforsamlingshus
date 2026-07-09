<?php

/**
 * Database Fixtures
 *
 * This class provides test data for Database tests.
 */
class DatabaseFixtures
{
    /**
     * Get valid database configuration for testing
     *
     * @return array Array of valid database configuration
     */
    public static function getValidDatabaseConfig(): array
    {
        return [
            'HOST' => 'localhost',
            'DATABASE' => 'test_db',
            'USERNAME' => 'test_user',
            'PASSWORD' => 'test_password',
            'ENVIRONMENT' => 'development'
        ];
    }

    /**
     * Get invalid database configuration for testing
     *
     * @return array Array of invalid database configurations
     */
    public static function getInvalidDatabaseConfigs(): array
    {
        return [
            [
                'HOST' => 'invalid_host',
                'DATABASE' => 'test_db',
                'USERNAME' => 'test_user',
                'PASSWORD' => 'test_password',
                'ENVIRONMENT' => 'development'
            ],
            [
                'HOST' => 'localhost',
                'DATABASE' => 'nonexistent_db',
                'USERNAME' => 'test_user',
                'PASSWORD' => 'test_password',
                'ENVIRONMENT' => 'development'
            ],
            [
                'HOST' => 'localhost',
                'DATABASE' => 'test_db',
                'USERNAME' => 'invalid_user',
                'PASSWORD' => 'test_password',
                'ENVIRONMENT' => 'development'
            ],
            [
                'HOST' => 'localhost',
                'DATABASE' => 'test_db',
                'USERNAME' => 'test_user',
                'PASSWORD' => 'wrong_password',
                'ENVIRONMENT' => 'development'
            ]
        ];
    }

    /**
     * Get mock PDO object for testing
     *
     * @return PDO Mock PDO object
     */
    public static function getMockPdo(): \PDO
    {
        return new \PDO('sqlite::memory:');
    }

    /**
     * Get sample table schema for testing
     *
     * @return array Array of table schemas
     */
    public static function getSampleTableSchemas(): array
    {
        return [
            'bookings' => [
                'CREATE TABLE bookings (
                    id INTEGER PRIMARY KEY,
                    booking_date DATE NOT NULL,
                    customer_id INTEGER NOT NULL,
                    approved BOOLEAN DEFAULT 0,
                    name VARCHAR(255) NOT NULL
                )'
            ],
            'customers' => [
                'CREATE TABLE customers (
                    id INTEGER PRIMARY KEY,
                    name VARCHAR(255) NOT NULL,
                    email VARCHAR(255),
                    phone VARCHAR(20) NOT NULL,
                    address VARCHAR(255) NOT NULL,
                    city VARCHAR(100) NOT NULL,
                    zip VARCHAR(10) NOT NULL
                )'
            ],
            'users' => [
                'CREATE TABLE users (
                    id INTEGER PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    email VARCHAR(255) NOT NULL UNIQUE,
                    role VARCHAR(20) NOT NULL DEFAULT "user",
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )'
            ]
        ];
    }

    /**
     * Get sample data for testing
     *
     * @return array Array of sample data for tables
     */
    public static function getSampleData(): array
    {
        return [
            'bookings' => [
                [
                    'id' => 1,
                    'booking_date' => '2023-12-01',
                    'customer_id' => 1,
                    'approved' => 1,
                    'name' => 'Test Booking 1'
                ],
                [
                    'id' => 2,
                    'booking_date' => '2023-12-02',
                    'customer_id' => 2,
                    'approved' => 0,
                    'name' => 'Test Booking 2'
                ]
            ],
            'customers' => [
                [
                    'id' => 1,
                    'name' => 'John Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '12345678',
                    'address' => '123 Main St',
                    'city' => 'Anytown',
                    'zip' => '12345'
                ],
                [
                    'id' => 2,
                    'name' => 'Jane Smith',
                    'email' => 'jane.smith@example.com',
                    'phone' => '87654321',
                    'address' => '456 Oak Ave',
                    'city' => 'Othertown',
                    'zip' => '54321'
                ]
            ],
            'users' => [
                [
                    'id' => 1,
                    'username' => 'admin',
                    'password' => password_hash('admin123', PASSWORD_DEFAULT),
                    'email' => 'admin@example.com',
                    'role' => 'admin',
                    'created_at' => '2023-01-01 00:00:00'
                ],
                [
                    'id' => 2,
                    'username' => 'user',
                    'password' => password_hash('user123', PASSWORD_DEFAULT),
                    'email' => 'user@example.com',
                    'role' => 'user',
                    'created_at' => '2023-01-02 00:00:00'
                ]
            ]
        ];
    }
}