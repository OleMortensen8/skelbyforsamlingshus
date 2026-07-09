<?php

/**
 * Booking Factory
 *
 * This class provides methods to generate test data for Booking tests.
 */
class BookingFactory
{
    /**
     * Default booking data
     *
     * @var array
     */
    private $defaults = [
        'date' => '2023-12-01',
        'type' => 0,
        'name' => 'Test Booking'
    ];

    /**
     * Create a booking with custom attributes
     *
     * @param array $attributes Custom attributes to override defaults
     * @return array The created booking data
     */
    public function create(array $attributes = []): array
    {
        $booking = array_merge($this->defaults, $attributes);
        return [$booking['date'], $booking['type'], $booking['name']];
    }

    /**
     * Create multiple bookings with custom attributes
     *
     * @param int $count Number of bookings to create
     * @param array $attributes Custom attributes to override defaults
     * @return array Array of booking data
     */
    public function createMany(int $count, array $attributes = []): array
    {
        $bookings = [];
        for ($i = 0; $i < $count; $i++) {
            // Create variations for each booking
            $customAttributes = $attributes;
            if (empty($attributes['name'] ?? null)) {
                $customAttributes['name'] = "Test Booking " . ($i + 1);
            }
            if (empty($attributes['date'] ?? null)) {
                // Create sequential dates
                $date = new DateTime($this->defaults['date']);
                $date->modify("+$i days");
                $customAttributes['date'] = $date->format('Y-m-d');
            }

            $bookings[] = $this->create($customAttributes);
        }

        return $bookings;
    }

    /**
     * Create an invalid booking
     *
     * @param string $invalidField The field to make invalid
     * @return array The invalid booking data
     */
    public function createInvalid(string $invalidField): array
    {
        $booking = $this->defaults;

        switch ($invalidField) {
            case 'date':
                $booking['date'] = 'invalid-date';
                break;
            case 'type':
                $booking['type'] = 'invalid-type';
                break;
            case 'name':
                $booking['name'] = '';
                break;
        }

        return [$booking['date'], $booking['type'], $booking['name']];
    }

    /**
     * Create a booking with a past date
     *
     * @param int $daysInPast Number of days in the past
     * @return array The booking data with a past date
     */
    public function createPast(int $daysInPast = 365): array
    {
        $date = new DateTime();
        $date->modify("-$daysInPast days");

        return $this->create([
            'date' => $date->format('Y-m-d')
        ]);
    }
}