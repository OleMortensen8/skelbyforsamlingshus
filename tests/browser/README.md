# Browser Testing

This directory contains browser tests for the SkelbyForsamlinghus project. Browser tests are used to test the frontend
functionality of the application in a real browser environment.

## Requirements

To run browser tests, you need to have the following installed:

- PHP 8.0.12 or higher
- Composer
- PHPUnit 10
- Selenium WebDriver
- ChromeDriver or GeckoDriver (for Firefox)

## Installation

1. Install the required dependencies:

```bash
composer require --dev php-webdriver/webdriver
```

2. Download and install ChromeDriver or GeckoDriver:

For ChromeDriver:

```bash
# For Linux
wget https://chromedriver.storage.googleapis.com/94.0.4606.61/chromedriver_linux64.zip
unzip chromedriver_linux64.zip
sudo mv chromedriver /usr/local/bin/
```

For GeckoDriver (Firefox):

```bash
# For Linux
wget https://github.com/mozilla/geckodriver/releases/download/v0.30.0/geckodriver-v0.30.0-linux64.tar.gz
tar -xvzf geckodriver-v0.30.0-linux64.tar.gz
sudo mv geckodriver /usr/local/bin/
```

## Running Browser Tests

To run browser tests, use the following command:

```bash
vendor/bin/phpunit tests/browser
```

## Writing Browser Tests

Browser tests should extend the `BrowserTestCase` class and use the WebDriver API to interact with the browser. Here's
an example:

```php
<?php
use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;

class ExampleBrowserTest extends TestCase {
    protected $driver;

    protected function setUp(): void {
        // Start Chrome
        $host = 'http://localhost:4444/wd/hub';
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create($host, $capabilities);
    }

    protected function tearDown(): void {
        // Close the browser
        $this->driver->quit();
    }

    public function test_homepage_loads() {
        // Navigate to the homepage
        $this->driver->get('http://localhost:8080');
        
        // Assert that the title contains "Skelby Forsamlinghus"
        $this->assertStringContainsString('Skelby Forsamlinghus', $this->driver->getTitle());
        
        // Assert that the navigation menu is present
        $this->assertTrue($this->driver->findElement(WebDriverBy::id('nav-menu'))->isDisplayed());
    }
}
```