<?php

require_once __DIR__ . '/BrowserTestCase.php';

/**
 * Browser test for the homepage
 */
class HomepageBrowserTest extends BrowserTestCase
{
    /**
     * Test that the homepage loads correctly
     */
    public function test_homepage_loads()
    {
        // Navigate to the homepage
        $this->navigateTo('/');

        // Assert that the title contains "Skelby Forsamlinghus"
        $this->assertStringContainsString('Skelby Forsamlinghus', $this->driver->getTitle());

        // Assert that the header and footer are present
        $this->assertTrue($this->driver->findElement(WebDriverBy::tagName('header'))->isDisplayed());
        $this->assertTrue($this->driver->findElement(WebDriverBy::tagName('footer'))->isDisplayed());
    }

    /**
     * Test that the navigation menu is present and contains the expected links
     */
    public function test_navigation_menu()
    {
        // Navigate to the homepage
        $this->navigateTo('/');

        // Assert that the navigation menu is present
        $navMenu = $this->driver->findElement(WebDriverBy::tagName('nav'));
        $this->assertTrue($navMenu->isDisplayed());

        // Assert that the navigation menu contains the expected links
        $links = $navMenu->findElements(WebDriverBy::tagName('a'));
        $linkTexts = [];
        foreach ($links as $link) {
            $linkTexts[] = $link->getText();
        }

        $this->assertContains('Hjem', $linkTexts);
        $this->assertContains('Udlejning', $linkTexts);
        $this->assertContains('Arrangementer', $linkTexts);
        $this->assertContains('Galleri', $linkTexts);
        $this->assertContains('Bestyrelse', $linkTexts);
        $this->assertContains('Kontakt', $linkTexts);
    }

    /**
     * Test that clicking on a navigation link works correctly
     */
    public function test_navigation_links()
    {
        // Navigate to the homepage
        $this->navigateTo('/');

        // Click on the Kontakt link
        $this->click('nav a[href*="kontakt.php"]');

        // Assert that we are now on the kontakt page
        $this->assertStringContainsString('kontakt.php', $this->driver->getCurrentURL());

        // Assert that the kontakt page title is displayed
        $this->assertStringContainsString('Kontakt', $this->driver->getTitle());
    }

    /**
     * Test that the main content section is displayed
     */
    public function test_main_content()
    {
        // Navigate to the homepage
        $this->navigateTo('/');

        // Assert that the main content section is displayed
        $mainContent = $this->driver->findElement(WebDriverBy::tagName('main'));
        $this->assertTrue($mainContent->isDisplayed());

        // Assert that the main content contains some expected text
        $this->assertStringContainsString('Velkommen', $mainContent->getText());
    }

    /**
     * Test that the slideshow is present and functioning
     */
    public function test_slideshow()
    {
        // Navigate to the homepage
        $this->navigateTo('/');

        // Assert that the slideshow container is present
        $this->assertTrue($this->driver->findElement(WebDriverBy::className('slideshow'))->isDisplayed());

        // Assert that there are slideshow images
        $slideshowImages = $this->driver->findElements(WebDriverBy::cssSelector('.slideshow img'));
        $this->assertGreaterThan(0, count($slideshowImages));
    }
}