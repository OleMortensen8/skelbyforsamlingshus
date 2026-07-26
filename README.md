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
   mysql -u your_db_username -p your_db_name < assets/sql/create_users_table.sql
   mysql -u your_db_username -p your_db_name < docker/initdb/01_customers_bookings.sql
   ```

## Docker

`docker-compose.yml` / `Dockerfile` are dev-oriented: the `db` service uses
hardcoded, low-security default credentials (`skelby`/`skelby_dev_pass`,
root password `secret`) meant only for local development. **Do not reuse
`docker-compose.yml` as-is in production** — a production deployment should
use its own compose file (or non-Docker deployment) with real secrets
supplied via environment variables / a secrets manager, not the defaults
baked into this file.

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

The project uses GitHub Actions for continuous integration. The workflow is defined in
`.github/workflows/ci.yml` and runs on every push to `main` and on pull requests.

### CI Workflow

1. Starts a MySQL 8.0 service container for testing
2. Sets up PHP 8.3 with the extensions the app needs (pdo_mysql, curl, mbstring, xml)
3. Validates `composer.json`
4. Installs dependencies
5. Lints every PHP file with `php -l`
6. Loads the database schema (`assets/sql/create_users_table.sql` + `docker/initdb/01_customers_bookings.sql`)
7. Runs the full test suite via `composer test` (all PHPUnit suites configured in `phpunit.xml`)

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
