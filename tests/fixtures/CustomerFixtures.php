<?php

/**
 * Customer Fixtures
 *
 * This class provides test data for Customer tests.
 */
class CustomerFixtures
{
    /**
     * Get valid customer data for testing
     *
     * @return array Array of customer data
     */
    public static function getValidCustomerData(): array
    {
        return [
            [
                'customer_id' => 1,
                'name' => 'John Doe',
                'email' => 'john.doe@example.com',
                'phone' => '12345678',
                'address' => '123 Main St',
                'city' => 'Anytown',
                'zip' => '12345'
            ],
            [
                'customer_id' => 2,
                'name' => 'Jane Smith',
                'email' => 'jane.smith@example.com',
                'phone' => '87654321',
                'address' => '456 Oak Ave',
                'city' => 'Othertown',
                'zip' => '54321'
            ],
            [
                'customer_id' => 3,
                'name' => 'Bob Johnson',
                'email' => 'bob.johnson@example.com',
                'phone' => '55555555',
                'address' => '789 Pine Rd',
                'city' => 'Somewhere',
                'zip' => '67890'
            ]
        ];
    }

    /**
     * Get a single valid customer for testing
     *
     * @return array Single customer data
     */
    public static function getSingleCustomer(): array
    {
        return [
            'customer_id' => 1,
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'phone' => '12345678',
            'address' => '123 Main St',
            'city' => 'Anytown',
            'zip' => '12345'
        ];
    }

    /**
     * Get invalid customer data for testing
     *
     * @return array Array of invalid customer data
     */
    public static function getInvalidCustomerData(): array
    {
        return [
            [
                'customer_id' => 'invalid-id', // Invalid ID
                'name' => 'Invalid ID Customer',
                'email' => 'invalid.id@example.com',
                'phone' => '12345678',
                'address' => '123 Main St',
                'city' => 'Anytown',
                'zip' => '12345'
            ],
            [
                'customer_id' => 4,
                'name' => '', // Empty name
                'email' => 'empty.name@example.com',
                'phone' => '12345678',
                'address' => '123 Main St',
                'city' => 'Anytown',
                'zip' => '12345'
            ],
            [
                'customer_id' => 5,
                'name' => 'Invalid Email Customer',
                'email' => 'invalid-email', // Invalid email
                'phone' => '12345678',
                'address' => '123 Main St',
                'city' => 'Anytown',
                'zip' => '12345'
            ]
        ];
    }

    /**
     * Get customer IDs for testing
     *
     * @return array Array of customer IDs
     */
    public static function getCustomerIds(): array
    {
        return [1, 2, 3, 4, 5];
    }

    /**
     * Get booking IDs associated with customers for testing
     *
     * @return array Array of booking IDs
     */
    public static function getBookingIds(): array
    {
        return [101, 102, 103, 104, 105];
    }
}