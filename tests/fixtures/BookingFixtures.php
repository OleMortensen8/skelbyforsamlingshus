<?php

/**
 * Booking Fixtures
 *
 * This class provides test data for Booking tests.
 */
class BookingFixtures
{
    /**
     * Get valid booking data for testing
     *
     * @return array Array of booking data
     */
    public static function getValidBookingData(): array
    {
        return [
            ['2023-12-01', 0, 'Test Booking 1'],
            ['2023-12-02', 1, 'Test Booking 2'],
            ['2023-12-03', 0, 'Test Booking 3']
        ];
    }

    /**
     * Get a single valid booking for testing
     *
     * @return array Single booking data
     */
    public static function getSingleBooking(): array
    {
        return ['2023-12-01', 0, 'Test Single Booking'];
    }

    /**
     * Get valid booking dates for testing
     *
     * @return array Array of booking dates
     */
    public static function getValidBookingDates(): array
    {
        return ['2023-12-01', '2023-12-02', '2023-12-03'];
    }

    /**
     * Get invalid booking data for testing
     *
     * @return array Array of invalid booking data
     */
    public static function getInvalidBookingData(): array
    {
        return [
            ['invalid-date', 0, 'Invalid Date Booking'],
            ['2023-12-01', 'invalid-type', 'Invalid Type Booking'],
            ['2023-12-01', 0, ''] // Empty name
        ];
    }

    /**
     * Get booking data with past dates for testing
     *
     * @return array Array of booking data with past dates
     */
    public static function getPastBookingData(): array
    {
        return [
            ['2020-01-01', 0, 'Past Booking 1'],
            ['2020-01-02', 1, 'Past Booking 2']
        ];
    }
}