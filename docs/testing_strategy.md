# SkelbyForsamlinghus Testing Strategy

This document outlines the testing strategy for the SkelbyForsamlinghus project, including the types of tests, testing
tools, and best practices.

## Testing Objectives

The main objectives of our testing strategy are:

1. Ensure the application functions correctly according to requirements
2. Identify and fix bugs early in the development process
3. Prevent regression issues when making changes
4. Ensure the application is secure and performs well
5. Provide documentation of expected behavior through tests

## Types of Tests

### Unit Tests

Unit tests focus on testing individual components (classes, methods, functions) in isolation.

- **Location**: `tests/` directory (files named with the suffix "Test.php")
- **Tool**: PHPUnit
- **Coverage Target**: 80% code coverage for all classes
- **Examples**: `BookingTest.php`, `CustomerTest.php`, `DatabaseTest.php`

### Integration Tests

Integration tests verify that different components work together correctly.

- **Location**: `tests/` directory (files named with the suffix "IntegrationTest.php")
- **Tool**: PHPUnit
- **Coverage Target**: All critical integration points between components
- **Examples**: `UserAuthenticationIntegrationTest.php`, `BookingIntegrationTest.php`

### Functional Tests

Functional tests ensure that the application functions correctly from a user's perspective, testing entire workflows.

- **Location**: `tests/` directory (files named with the suffix "WorkflowFunctionalTest.php")
- **Tool**: PHPUnit
- **Coverage Target**: All critical user workflows
- **Examples**: `BookingWorkflowFunctionalTest.php`, `UserRegistrationLoginWorkflowFunctionalTest.php`

### Browser Tests

Browser tests verify the frontend functionality of the application in a real browser environment.

- **Location**: `tests/browser/` directory
- **Tools**: PHPUnit with WebDriver, Codeception
- **Coverage Target**: All key frontend pages and interactions
- **Examples**: `HomepageBrowserTest.php`, `ArrangementsCest.php`, `LoginCest.php`

### API Tests

API tests verify that the application's API endpoints function correctly.

- **Location**: `tests/api/` directory
- **Tool**: PHPUnit
- **Coverage Target**: All API endpoints
- **Examples**: `BookingApiTest.php`, `UserApiTest.php`

### Performance Tests

Performance tests ensure the application performs well under load.

- **Location**: `tests/performance/` directory
- **Tool**: JMeter or similar
- **Coverage Target**: Critical high-traffic pages and endpoints
- **Examples**: `HomepageLoadTest.jmx`, `BookingProcessLoadTest.jmx`

## Test Environment

### Local Development

- Each developer should run unit and integration tests locally before committing code
- Browser tests can be run locally with Selenium WebDriver and ChromeDriver/GeckoDriver

### Continuous Integration

- All tests are run automatically on the CI/CD pipeline
- Tests are run on each pull request and before deployment
- Test coverage reports are generated and reviewed

## Test Data Management

### Test Fixtures

- Test fixtures provide consistent test data for tests
- Located in `tests/fixtures/` directory
- Examples: `BookingFixtures.php`, `CustomerFixtures.php`

### Test Factories

- Test factories generate test data dynamically
- Located in `tests/factories/` directory
- Examples: `BookingFactory.php`, `UserFactory.php`

## Best Practices

1. **Test Independence**: Each test should be independent and not rely on the state from other tests
2. **Arrange-Act-Assert**: Follow the AAA pattern in tests (Arrange the test, Act on the system, Assert the results)
3. **Descriptive Test Names**: Use descriptive test method names that explain what is being tested
4. **Test Edge Cases**: Include tests for edge cases and error conditions
5. **Keep Tests Fast**: Tests should run quickly to encourage frequent testing
6. **Test Real Code**: Avoid excessive mocking that tests implementation rather than behavior
7. **Maintain Tests**: Update tests when requirements change

## Test Execution

### Running Tests

```bash
# Run all tests
vendor/bin/phpunit

# Run a specific test file
vendor/bin/phpunit tests/BookingTest.php

# Run browser tests
vendor/bin/phpunit tests/browser

# Run with coverage report
vendor/bin/phpunit --coverage-html tests/log/report
```

### Test Reports

- HTML coverage reports are generated in `tests/log/report/`
- CI/CD pipeline provides test results and coverage reports

## Responsibilities

- **Developers**: Write and maintain unit, integration, and functional tests
- **QA Team**: Focus on browser tests, API tests, and performance tests
- **Team Lead**: Ensure test coverage meets targets and review test reports

## Continuous Improvement

The testing strategy should be reviewed and updated regularly to:

1. Identify gaps in test coverage
2. Improve test performance
3. Adopt new testing tools and techniques
4. Address recurring issues found in production