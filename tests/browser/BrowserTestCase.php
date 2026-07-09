<?php
/**
 * Base test case for browser tests
 *
 * This class provides common functionality for browser tests.
 */

use PHPUnit\Framework\TestCase;
use Facebook\WebDriver\Remote\RemoteWebDriver;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverExpectedCondition;

class BrowserTestCase extends TestCase
{
    /**
     * WebDriver instance
     *
     * @var RemoteWebDriver
     */
    protected $driver;

    /**
     * Base URL for the application
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost:8080';

    /**
     * Set up the test environment
     */
    protected function setUp(): void
    {
        // Check if Selenium is running
        if (!$this->isSeleniumRunning()) {
            $this->markTestSkipped('Selenium server is not running. Skipping browser tests.');
            return;
        }

        // Start Chrome
        $host = 'http://localhost:4444/wd/hub';
        $capabilities = DesiredCapabilities::chrome();
        $this->driver = RemoteWebDriver::create($host, $capabilities, 60000, 60000);

        // Set window size
        $this->driver->manage()->window()->maximize();

        // Set implicit wait
        $this->driver->manage()->timeouts()->implicitlyWait(10);
    }

    /**
     * Tear down the test environment
     */
    protected function tearDown(): void
    {
        // Close the browser if driver exists
        if ($this->driver) {
            $this->driver->quit();
        }
    }

    /**
     * Check if Selenium server is running
     *
     * @return bool True if Selenium is running, false otherwise
     */
    protected function isSeleniumRunning(): bool
    {
        $ch = curl_init('http://localhost:4444/wd/hub/status');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($ch, CURLOPT_TIMEOUT, 2);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200 && $response !== false;
    }

    /**
     * Navigate to a page
     *
     * @param string $path Path to navigate to
     */
    protected function navigateTo(string $path): void
    {
        $this->driver->get($this->baseUrl . $path);
    }

    /**
     * Wait for an element to be visible
     *
     * @param string $selector CSS selector for the element
     * @param int $timeout Timeout in seconds
     * @return \Facebook\WebDriver\WebDriverElement The element
     */
    protected function waitForElement(string $selector, int $timeout = 10)
    {
        return $this->driver->wait($timeout)->until(
            WebDriverExpectedCondition::visibilityOfElementLocated(WebDriverBy::cssSelector($selector))
        );
    }

    /**
     * Fill a form field
     *
     * @param string $selector CSS selector for the field
     * @param string $value Value to fill
     */
    protected function fillField(string $selector, string $value): void
    {
        $element = $this->driver->findElement(WebDriverBy::cssSelector($selector));
        $element->clear();
        $element->sendKeys($value);
    }

    /**
     * Click an element
     *
     * @param string $selector CSS selector for the element
     */
    protected function click(string $selector): void
    {
        $this->driver->findElement(WebDriverBy::cssSelector($selector))->click();
    }

    /**
     * Take a screenshot
     *
     * @param string $name Name of the screenshot
     */
    protected function takeScreenshot(string $name): void
    {
        $screenshotDir = __DIR__ . '/screenshots';
        if (!is_dir($screenshotDir)) {
            mkdir($screenshotDir, 0777, true);
        }

        $this->driver->takeScreenshot($screenshotDir . '/' . $name . '.png');
    }
}