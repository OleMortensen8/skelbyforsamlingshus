<?php

/**
 * BookableCell Fixtures
 *
 * This class provides test data for BookableCell tests.
 */
class BookableCellFixtures
{
    /**
     * Get valid dates for testing
     *
     * @return array Array of valid dates
     */
    public static function getValidDates(): array
    {
        return [
            date('Y-m-d', strtotime('+1 day')),
            date('Y-m-d', strtotime('+2 days')),
            date('Y-m-d', strtotime('+3 days')),
            date('Y-m-d', strtotime('+4 days')),
            date('Y-m-d', strtotime('+5 days'))
        ];
    }

    /**
     * Get past dates for testing
     *
     * @return array Array of past dates
     */
    public static function getPastDates(): array
    {
        return [
            date('Y-m-d', strtotime('-1 day')),
            date('Y-m-d', strtotime('-2 days')),
            date('Y-m-d', strtotime('-3 days')),
            date('Y-m-d', strtotime('-4 days')),
            date('Y-m-d', strtotime('-5 days'))
        ];
    }

    /**
     * Get invalid dates for testing
     *
     * @return array Array of invalid dates
     */
    public static function getInvalidDates(): array
    {
        return [
            'invalid-date',
            '2023/12/01',
            '01-12-2023',
            '2023-13-01',
            '2023-01-32'
        ];
    }

    /**
     * Get valid booking form data for testing
     *
     * @return array Array of booking form data
     */
    public static function getValidBookingFormData(): array
    {
        return [
            [
                'startdate' => date('Y-m-d', strtotime('+1 day')),
                'enddate' => '2',
                'navnet' => 'Test Person',
                'mail' => 'test@example.com',
                'telefon' => '12345678',
                'adresse' => 'Test Address 123',
                'postnr' => '1234',
                'by' => 'Test City',
                'sal' => 'begge sale',
                'csrf_token' => 'test_token'
            ],
            [
                'startdate' => date('Y-m-d', strtotime('+2 days')),
                'enddate' => '0',
                'navnet' => 'Another Person',
                'mail' => 'another@example.com',
                'telefon' => '87654321',
                'adresse' => 'Another Address 456',
                'postnr' => '5678',
                'by' => 'Another City',
                'sal' => 'lillesal',
                'csrf_token' => 'test_token'
            ]
        ];
    }

    /**
     * Get invalid booking form data for testing
     *
     * @return array Array of invalid booking form data
     */
    public static function getInvalidBookingFormData(): array
    {
        return [
            [
                'startdate' => 'invalid-date',
                'enddate' => '2',
                'navnet' => 'Test Person',
                'mail' => 'test@example.com',
                'telefon' => '12345678',
                'adresse' => 'Test Address 123',
                'postnr' => '1234',
                'by' => 'Test City',
                'sal' => 'begge sale',
                'csrf_token' => 'test_token'
            ],
            [
                'startdate' => date('Y-m-d', strtotime('+1 day')),
                'enddate' => '2',
                'navnet' => '', // Empty name
                'mail' => 'test@example.com',
                'telefon' => '12345678',
                'adresse' => 'Test Address 123',
                'postnr' => '1234',
                'by' => 'Test City',
                'sal' => 'begge sale',
                'csrf_token' => 'test_token'
            ],
            [
                'startdate' => date('Y-m-d', strtotime('+1 day')),
                'enddate' => '2',
                'navnet' => 'Test Person',
                'mail' => 'invalid-email', // Invalid email
                'telefon' => '12345678',
                'adresse' => 'Test Address 123',
                'postnr' => '1234',
                'by' => 'Test City',
                'sal' => 'begge sale',
                'csrf_token' => 'test_token'
            ]
        ];
    }

    /**
     * Get booking IDs for testing
     *
     * @return array Array of booking IDs
     */
    public static function getBookingIds(): array
    {
        return [1, 2, 3, 4, 5];
    }

    /**
     * Get invalid booking IDs for testing
     *
     * @return array Array of invalid booking IDs
     */
    public static function getInvalidBookingIds(): array
    {
        return ['invalid', 'a1', '1a', '', null];
    }
}