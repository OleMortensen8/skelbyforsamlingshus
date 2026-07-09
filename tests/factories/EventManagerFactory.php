<?php

/**
 * EventManager Factory
 *
 * This class provides methods to generate test data for EventManager tests.
 */
class EventManagerFactory
{
    /**
     * Default event data
     *
     * @var array
     */
    private $defaults = [
        'title' => 'Test Event',
        'description' => 'This is a test event description',
        'date' => '2023-12-15',
        'time' => '19:00',
        'location' => 'Skelby Forsamlinghus',
        'image' => 'default-event.jpg'
    ];

    /**
     * Create an event with custom attributes
     *
     * @param array $attributes Custom attributes to override defaults
     * @return array The created event data
     */
    public function create(array $attributes = []): array
    {
        return array_merge($this->defaults, $attributes);
    }

    /**
     * Create multiple events with custom attributes
     *
     * @param int $count Number of events to create
     * @param array $attributes Custom attributes to override defaults
     * @return array Array of event data
     */
    public function createMany(int $count, array $attributes = []): array
    {
        $events = [];
        for ($i = 0; $i < $count; $i++) {
            // Create variations for each event
            $customAttributes = $attributes;
            if (empty($attributes['title'] ?? null)) {
                $customAttributes['title'] = "Test Event " . ($i + 1);
            }
            if (empty($attributes['date'] ?? null)) {
                // Create sequential dates
                $date = new DateTime($this->defaults['date']);
                $date->modify("+$i days");
                $customAttributes['date'] = $date->format('Y-m-d');
            }

            $events[] = $this->create($customAttributes);
        }

        return $events;
    }

    /**
     * Create a past event
     *
     * @param int $daysInPast Number of days in the past
     * @return array The event data with a past date
     */
    public function createPast(int $daysInPast = 30): array
    {
        $date = new DateTime();
        $date->modify("-$daysInPast days");

        return $this->create([
            'date' => $date->format('Y-m-d'),
            'title' => 'Past Event'
        ]);
    }

    /**
     * Create a future event
     *
     * @param int $daysInFuture Number of days in the future
     * @return array The event data with a future date
     */
    public function createFuture(int $daysInFuture = 30): array
    {
        $date = new DateTime();
        $date->modify("+$daysInFuture days");

        return $this->create([
            'date' => $date->format('Y-m-d'),
            'title' => 'Future Event'
        ]);
    }

    /**
     * Create an invalid event
     *
     * @param string $invalidField The field to make invalid
     * @return array The invalid event data
     */
    public function createInvalid(string $invalidField): array
    {
        $event = $this->defaults;

        switch ($invalidField) {
            case 'title':
                $event['title'] = '';
                break;
            case 'date':
                $event['date'] = 'invalid-date';
                break;
            case 'time':
                $event['time'] = 'invalid-time';
                break;
        }

        return $event;
    }
}