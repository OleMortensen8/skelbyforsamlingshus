# Development Environment Setup

## Overview
SkelbyForsamlingshus is a PHP-based booking system for community halls. The project uses:
- **PHP 8.3** with Apache
- **MySQL 8.0** database
- **Docker** for containerization
- **PHPUnit** for testing
- **Composer** for PHP dependency management

## VS Code Setup

### Recommended Extensions
The project includes a `.vscode/extensions.json` with recommended extensions for PHP development:
- **PHP Intelephense** - PHP language support and IntelliSense
- **PHP Debug** - Xdebug debugging support
- **PHP DocBlocker** - Documentation comment generation
- **GitLens** - Git integration and blame
- **Docker** - Docker file support
- **GitHub Copilot** - AI coding assistance
- And more...

To install all recommended extensions, VS Code will prompt you when opening the workspace. You can also manually install them from the Extensions panel.

### Launch Configurations
The project includes three debug configurations in `.vscode/launch.json`:
1. **Listen for Xdebug** - Connect to a running PHP server with Xdebug enabled
2. **Launch currently open script** - Debug an individual PHP script
3. **Launch Built-in web server** - Start PHP's built-in server with debugging

## Getting Started

### Option 1: Using Docker (Recommended)
```bash
cd /home/olevsm/Projekter/SkelbyForsamlingshus
docker-compose up -d
```

This will start:
- **Web server**: http://localhost:8080
- **Database**: MySQL on localhost:3306
  - Root password: `secret`
  - Database: `myapp`

### Option 2: Local PHP Setup
1. **Install PHP dependencies:**
   ```bash
   php composer.phar install
   ```

2. **Configure environment:**
   - Copy `.env.example` to `.env` (already done)
   - Update `.env` with your database credentials

3. **Set up database:**
   ```bash
   mysql -u root -p your_database < assets/sql/schema.sql
   ```

4. **Start PHP server:**
   ```bash
   php -S localhost:8000
   ```

## Testing

### Run All Tests
```bash
php composer.phar test
```

### Generate Coverage Report
```bash
./generate-coverage.sh
```

### Run Specific Test Suite
```bash
php composer.phar test tests/BookingTest.php
```

## Project Structure

```
├── assets/
│   ├── class/          # PHP classes with PSR-4 autoloading
│   ├── config/         # Configuration files
│   ├── css/            # Stylesheets
│   ├── js/             # JavaScript files
│   ├── sql/            # Database schemas and migrations
│   └── view/           # View templates
├── tests/              # PHPUnit test files
├── docs/               # Documentation
├── config/             # Security and application config
└── vendor/             # Composer dependencies
```

## Database Setup

The database schema is located at `assets/sql/` and includes tables for:
- Users and authentication
- Bookings and availability
- Events and arrangements
- Members and roles

See `.env` for database credentials.

## Debugging

### Xdebug Configuration
Xdebug is configured for port 9003 (VS Code default). To debug:

1. Start a debug configuration from the Run menu
2. Set breakpoints in your PHP code
3. Trigger the code path in your browser
4. VS Code will pause at breakpoints

### Common Issues
- **Port 9003 already in use**: Change the port in `.vscode/launch.json` and `docker-compose.yml`
- **Xdebug not connecting**: Verify `XDEBUG_MODE=debug` in environment variables

## Performance Testing

Run performance tests:
```bash
./run_performance_tests.sh
```

## Additional Resources

- [PHP PSR Standards](https://www.php-fig.org/psr/)
- [PHPUnit Documentation](https://phpunit.de/)
- [Xdebug Documentation](https://xdebug.org/)
- [Docker Documentation](https://docs.docker.com/)

## Troubleshooting

For issues with:
- **Composer**: See `composer.json` for dependencies and scripts
- **Database**: Check `.env` credentials and MySQL service status
- **Tests**: Check `phpunit.xml` for configuration
- **Security**: Review `config/security.php` and `docs/PHASE2_SECURITY.md`
