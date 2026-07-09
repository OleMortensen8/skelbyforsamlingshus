# SkelbyForsamlinghus Development Guidelines

This document provides guidelines for development on the SkelbyForsamlinghus project.

## Build/Configuration Instructions

### Environment Setup

1. **PHP Version**: The project requires PHP 8.0.12 (as specified in the Dockerfile).

2. **Environment Variables**: Copy `.env.example` to `.env` and configure the following variables:
   ```
   HOST="your_db_host"
   DATABASE="your_db_name"
   USERNAME="your_db_username"
   PASSWORD="your_db_password"
   ```

3. **Dependencies**: Install dependencies using Composer:
   ```bash
   composer install
   ```

### Docker Setup

The project includes a basic Dockerfile that uses PHP 8.0.12 with Apache. To build and run the Docker container:

```bash
docker build -t skelby-forsamlinghus .
docker run -p 8080:80 skelby-forsamlinghus
```

## Testing Information

### Test Configuration

1. **PHPUnit**: The project uses PHPUnit 10 for testing, configured in `phpunit.xml`.

2. **Test Directory**: All tests are located in the `./tests` directory.

3. **Running Tests**: Execute tests using the PHPUnit binary:
   ```bash
   vendor/bin/phpunit
   ```

   To run a specific test file:
   ```bash
   vendor/bin/phpunit tests/YourTestFile.php
   ```

### Creating New Tests

1. **Naming Convention**: Test files should be named after the class they test with the suffix "Test" (e.g., `BookingTest.php` for the `Booking` class).

2. **Test Structure**: Tests should extend `PHPUnit\Framework\TestCase` and follow this structure:
   ```php
   <?php
   use PHPUnit\Framework\TestCase;

   class YourClassTest extends TestCase {
       protected $yourObject;

       protected function setUp(): void {
           $this->yourObject = new YourClass();
       }

       public function test_methodName() {
           // Test code here
           $this->assertSomething(...);
       }
   }
   ```

3. **Test Example**: Here's an example test for the Database class:
   ```php
   <?php
   use PHPUnit\Framework\TestCase;

   class DatabaseTest extends TestCase {
       protected $database;

       protected function setUp(): void {
           $this->database = new Database();
       }

       public function test_construct() {
           // Test that the database connection is established
           $reflection = new ReflectionClass($this->database);
           $property = $reflection->getProperty('dbh');
           $property->setAccessible(true);
           $dbh = $property->getValue($this->database);
           
           $this->assertInstanceOf(PDO::class, $dbh);
       }
   }
   ```

## Code Style and Development Practices

### Project Structure

- **Class Files**: Located in `assets/class/`
- **Configuration Files**: Located in `assets/config/`
- **Views**: Located in `assets/view/`
- **CSS/JS**: Located in `assets/css/` and `assets/js/`
- **Images**: Located in `assets/img/`

### Autoloading

The project uses Composer's classmap autoloading for classes in the `assets/class` directory. Additionally, there's a custom autoloader in `assets/class/autoload.php`.

### Database Access

- Database connections are established through the `Database` class.
- Classes that need database access should extend the `Database` class.
- Use environment variables for database configuration.

### Security Practices

As documented in `docs/security_improvements.md`, the project implements:

1. **Input Validation and Sanitization**:
   - Use type-specific validation functions for different types of data.
   - Sanitize all user inputs.

2. **CSRF Protection**:
   - Use CSRF tokens for all form submissions and AJAX requests.

3. **Secure Sensitive Data**:
   - Use environment variables for sensitive information.
   - Avoid hardcoding credentials.

4. **Recommended Security Improvements**:
   - Implement proper authentication and authorization.
   - Use prepared statements for all database queries.
   - Configure secure session settings.
   - Implement proper error handling.
   - Ensure HTTPS usage.
   - Implement Content Security Policy and security headers.
   - Conduct regular security audits.

### Error Handling

- Use try-catch blocks for error handling.
- Log errors instead of displaying them to users in production.
- Provide user-friendly error messages.

### XML Processing

The project uses SimpleXML for XML processing, as seen in `bootstrap.php` with the `arrangementer.xml` file.