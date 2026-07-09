# SkelbyForsamlinghus

This is the repository for the SkelbyForsamlinghus website.

## Development Setup

### Prerequisites

- PHP 8.0.12 or higher
- MySQL 5.7 or higher
- Composer

### Installation

1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/SkelbyForsamlinghus.git
   cd SkelbyForsamlinghus
   ```

2. Install dependencies:
   ```bash
   composer install
   ```

3. Create a `.env` file:
   ```bash
   cp .env.example .env
   ```

4. Update the `.env` file with your database credentials:
   ```
   HOST="your_db_host"
   DATABASE="your_db_name"
   USERNAME="your_db_username"
   PASSWORD="your_db_password"
   ENVIRONMENT="development"
   ```

5. Set up the database:
   ```bash
   mysql -u your_db_username -p your_db_name < assets/sql/schema.sql
   ```

## Testing

### Running Tests

The project uses PHPUnit for testing. To run the tests, you can use the Composer script:

```bash
composer test
```

Or run PHPUnit directly:

```bash
vendor/bin/phpunit
```

You can also run specific test suites:

```bash
# Run unit tests
vendor/bin/phpunit --testsuite "Unit Tests"

# Run integration tests
vendor/bin/phpunit --testsuite "Integration Tests"

# Run functional tests
vendor/bin/phpunit --testsuite "Functional Tests"
```

### Code Coverage

#### Installing Xdebug

To generate code coverage reports, you need to have Xdebug installed. The project includes a script to help you install
Xdebug:

```bash
./install-xdebug.sh
```

This script will:

- Detect your operating system (Linux, macOS, or Windows)
- Install the necessary dependencies
- Install Xdebug via PECL
- Configure Xdebug for code coverage

For Windows users, the script will provide manual installation instructions.

#### Verifying Xdebug Installation

After installing Xdebug, you can verify that it's working correctly by running:

```bash
./test-xdebug.php
```

Or using the Composer script:

```bash
composer test-xdebug
```

This script will:

- Check if Xdebug is installed and enabled
- Display the Xdebug version
- Verify that coverage mode is enabled
- Test if PHPUnit can generate code coverage reports
- Provide guidance if any issues are detected

#### Generating Coverage Reports

Once Xdebug is installed, you can generate a code coverage report using the provided script:

```bash
./generate-coverage.sh
```

This script will generate HTML, Clover XML, and text reports, and open the HTML report in your browser. You can also use
the `--no-browser` option to prevent opening the browser.

Alternatively, you can use the Composer script:

```bash
composer coverage
```

You can also generate coverage reports directly with PHPUnit:

```bash
vendor/bin/phpunit --coverage-html tests/log/report
```

Then open `tests/log/report/index.html` in your browser to view the report.

## CI/CD Pipeline

The project uses GitHub Actions for continuous integration and deployment. The workflow is defined in
`.github/workflows/ci.yml`.

### CI Workflow

The CI workflow runs on every push to the `main` branch and on pull requests. It performs the following steps:

1. Sets up a MySQL database for testing
2. Sets up PHP 8.0 with necessary extensions
3. Validates composer.json and composer.lock
4. Installs dependencies
5. Creates a .env file for testing
6. Sets up the database schema
7. Runs unit tests
8. Runs integration tests
9. Generates a code coverage report
10. Uploads the coverage report to Codecov

### Setting Up CI/CD

To set up the CI/CD pipeline:

1. Fork or clone the repository to your GitHub account
2. Go to the repository settings on GitHub
3. Navigate to "Secrets and variables" > "Actions"
4. Add the following secrets:
    - `DB_HOST`: Your database host
    - `DB_NAME`: Your database name
    - `DB_USER`: Your database username
    - `DB_PASSWORD`: Your database password

5. (Optional) Set up Codecov:
    - Create an account on [Codecov](https://codecov.io/)
    - Add your repository to Codecov
    - Add the `CODECOV_TOKEN` secret to your GitHub repository

## Contributing

1. Fork the repository
2. Create a new branch: `git checkout -b feature/your-feature-name`
3. Make your changes
4. Run the tests: `vendor/bin/phpunit`
5. Commit your changes: `git commit -m 'Add some feature'`
6. Push to the branch: `git push origin feature/your-feature-name`
7. Submit a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
