# Test Factories

This directory contains test factories for the SkelbyForsamlinghus project. Factories are used to generate test data
dynamically, allowing for more flexible and maintainable tests.

## Usage

Factories can be loaded in test files using the `require_once` statement:

```php
require_once __DIR__ . '/../factories/BookingFactory.php';
```

Then, you can use the factories in your tests:

```php
$bookingFactory = new BookingFactory();
$booking = $bookingFactory->create(['name' => 'Custom Booking Name']);
```

## Difference Between Fixtures and Factories

- **Fixtures** provide static, predefined test data that doesn't change.
- **Factories** generate dynamic test data that can be customized for specific test scenarios.

Use fixtures when you need consistent, unchanging test data. Use factories when you need to generate variations of test
data or when you need to create complex object graphs.