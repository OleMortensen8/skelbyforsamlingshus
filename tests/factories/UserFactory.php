<?php

/**
 * User Factory
 *
 * This class provides methods to generate test data for User tests.
 */
class UserFactory
{
    /**
     * Default user data
     *
     * @var array
     */
    private $defaults = [
        'username' => 'testuser',
        'password' => 'password123',
        'email' => 'test@example.com',
        'role' => 'user'
    ];

    /**
     * Create a user with custom attributes
     *
     * @param array $attributes Custom attributes to override defaults
     * @return array The created user data
     */
    public function create(array $attributes = []): array
    {
        return array_merge($this->defaults, $attributes);
    }

    /**
     * Create multiple users with custom attributes
     *
     * @param int $count Number of users to create
     * @param array $attributes Custom attributes to override defaults
     * @return array Array of user data
     */
    public function createMany(int $count, array $attributes = []): array
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            // Create variations for each user
            $customAttributes = $attributes;
            if (empty($attributes['username'] ?? null)) {
                $customAttributes['username'] = "testuser" . ($i + 1);
            }
            if (empty($attributes['email'] ?? null)) {
                $customAttributes['email'] = "test{$i}@example.com";
            }

            $users[] = $this->create($customAttributes);
        }

        return $users;
    }

    /**
     * Create an admin user
     *
     * @param array $attributes Custom attributes to override defaults
     * @return array The admin user data
     */
    public function createAdmin(array $attributes = []): array
    {
        return $this->create(array_merge([
            'role' => 'admin'
        ], $attributes));
    }

    /**
     * Create an invalid user
     *
     * @param string $invalidField The field to make invalid
     * @return array The invalid user data
     */
    public function createInvalid(string $invalidField): array
    {
        $user = $this->defaults;

        switch ($invalidField) {
            case 'username':
                $user['username'] = '';
                break;
            case 'password':
                $user['password'] = '123'; // Too short
                break;
            case 'email':
                $user['email'] = 'invalid-email';
                break;
            case 'role':
                $user['role'] = 'invalid-role';
                break;
        }

        return $user;
    }
}