# Test Fixtures

This directory contains test fixtures for the SkelbyForsamlinghus project. Fixtures are sample data used in tests to
ensure consistent test results.

## Usage

Fixtures can be loaded in test files using the `require_once` statement:

```php
require_once __DIR__ . '/../fixtures/BookingFixtures.php';
```

Then, you can use the fixtures in your tests:

```php
$bookingData = BookingFixtures::getValidBookingData();
```