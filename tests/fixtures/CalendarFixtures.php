<?php

/**
 * Calendar Fixtures
 *
 * This class provides test data for Calendar tests.
 */
class CalendarFixtures
{
    /**
     * Get valid months for testing
     *
     * @return array Array of valid months
     */
    public static function getValidMonths(): array
    {
        return ['01', '02', '03', '04', '05', '06', '07', '08', '09', '10', '11', '12'];
    }

    /**
     * Get invalid months for testing
     *
     * @return array Array of invalid months
     */
    public static function getInvalidMonths(): array
    {
        return ['00', '13', 'Jan', 'invalid', ''];
    }

    /**
     * Get valid years for testing
     *
     * @return array Array of valid years
     */
    public static function getValidYears(): array
    {
        return [
            date('Y', strtotime('-1 year')),
            date('Y'),
            date('Y', strtotime('+1 year')),
            date('Y', strtotime('+2 years')),
            date('Y', strtotime('+3 years'))
        ];
    }

    /**
     * Get invalid years for testing
     *
     * @return array Array of invalid years
     */
    public static function getInvalidYears(): array
    {
        return ['invalid', '20XX', '', '0'];
    }

    /**
     * Get valid month-year combinations for testing
     *
     * @return array Array of valid month-year combinations
     */
    public static function getValidMonthYearCombinations(): array
    {
        return [
            ['month' => '01', 'year' => date('Y')],
            ['month' => '06', 'year' => date('Y')],
            ['month' => '12', 'year' => date('Y')],
            ['month' => '01', 'year' => date('Y', strtotime('+1 year'))],
            ['month' => '12', 'year' => date('Y', strtotime('+1 year'))]
        ];
    }

    /**
     * Get days in month data for testing
     *
     * @return array Array of month, year, and expected days in month
     */
    public static function getDaysInMonthData(): array
    {
        return [
            ['month' => '01', 'year' => '2023', 'days' => 31],
            ['month' => '02', 'year' => '2023', 'days' => 28],
            ['month' => '02', 'year' => '2024', 'days' => 29], // Leap year
            ['month' => '04', 'year' => '2023', 'days' => 30],
            ['month' => '12', 'year' => '2023', 'days' => 31]
        ];
    }

    /**
     * Get navigation data for testing
     *
     * @return array Array of current month, year, and expected next/previous month, year
     */
    public static function getNavigationData(): array
    {
        return [
            [
                'currentMonth' => '01',
                'currentYear' => '2023',
                'nextMonth' => '02',
                'nextYear' => '2023',
                'prevMonth' => '12',
                'prevYear' => '2022'
            ],
            [
                'currentMonth' => '12',
                'currentYear' => '2023',
                'nextMonth' => '01',
                'nextYear' => '2024',
                'prevMonth' => '11',
                'prevYear' => '2023'
            ]
        ];
    }

    /**
     * Get day labels for testing
     *
     * @return array Array of day labels
     */
    public static function getDayLabels(): array
    {
        return ["Man", "Tir", "Ons", "Tor", "Fre", "Lør", "Søn"];
    }

    /**
     * Get month labels for testing
     *
     * @return array Array of month labels
     */
    public static function getMonthLabels(): array
    {
        return ["Jan", "Feb", "Mar", "Apr", "Maj", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dec"];
    }
}