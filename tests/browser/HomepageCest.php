<?php

class HomepageCest
{
    public function _before(AcceptanceTester $I)
    {
        // This method will be executed before each test
    }

    public function _after(AcceptanceTester $I)
    {
        // This method will be executed after each test
    }

    // Test homepage loads correctly
    public function testHomepageLoads(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->see('Skelby Forsamlinghus');
        $I->seeElement('header');
        $I->seeElement('footer');
    }

    // Test navigation menu
    public function testNavigationMenu(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->seeElement('nav');
        $I->seeLink('Hjem');
        $I->seeLink('Udlejning');
        $I->seeLink('Arrangementer');
        $I->seeLink('Galleri');
        $I->seeLink('Bestyrelse');
        $I->seeLink('Kontakt');
    }

    // Test clicking on a navigation link
    public function testNavigationLinks(AcceptanceTester $I)
    {
        $I->amOnPage('/');
        $I->click('Kontakt');
        $I->seeInCurrentUrl('/kontakt.php');
        $I->see('Kontakt');
    }

    // Test gallery page loads correctly
    public function testGalleryPage(AcceptanceTester $I)
    {
        $I->amOnPage('/gallery.php');
        $I->see('Galleri');
        $I->seeElement('.gallery-container');
    }

    // Test contact form functionality
    public function testContactForm(AcceptanceTester $I)
    {
        $I->amOnPage('/kontakt.php');
        $I->see('Kontakt');
        $I->seeElement('form');
        $I->fillField('name', 'Test User');
        $I->fillField('email', 'test@example.com');
        $I->fillField('message', 'This is a test message');
        // Note: We're not actually submitting the form to avoid sending emails during tests
        // $I->click('Submit');
        // $I->see('Thank you for your message');
    }
}