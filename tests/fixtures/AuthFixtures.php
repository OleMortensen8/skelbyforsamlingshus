<?php

/**
 * Auth Fixtures
 *
 * This class provides test data for Auth tests.
 */
class AuthFixtures
{
    /**
     * Get valid user credentials for testing
     *
     * @return array Array of user credentials
     */
    public static function getValidUserCredentials(): array
    {
        return [
            [
                'username' => 'admin',
                'password' => 'admin123',
                'role' => 'admin'
            ],
            [
                'username' => 'user',
                'password' => 'user123',
                'role' => 'user'
            ],
            [
                'username' => 'editor',
                'password' => 'editor123',
                'role' => 'editor'
            ]
        ];
    }

    /**
     * Get a single valid user credential for testing
     *
     * @return array Single user credential
     */
    public static function getSingleUserCredential(): array
    {
        return [
            'username' => 'admin',
            'password' => 'admin123',
            'role' => 'admin'
        ];
    }

    /**
     * Get invalid user credentials for testing
     *
     * @return array Array of invalid user credentials
     */
    public static function getInvalidUserCredentials(): array
    {
        return [
            [
                'username' => 'admin',
                'password' => 'wrongpassword',
                'role' => 'admin'
            ],
            [
                'username' => 'nonexistent',
                'password' => 'password123',
                'role' => 'user'
            ],
            [
                'username' => '',
                'password' => 'password123',
                'role' => 'user'
            ],
            [
                'username' => 'user',
                'password' => '',
                'role' => 'user'
            ]
        ];
    }

    /**
     * Get user roles for testing
     *
     * @return array Array of user roles
     */
    public static function getUserRoles(): array
    {
        return ['admin', 'user', 'editor', 'guest'];
    }
}