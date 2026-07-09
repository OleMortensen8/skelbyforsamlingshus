<?php

class RentalCest
{
    public function _before(AcceptanceTester $I)
    {
        // This method will be executed before each test
    }

    public function _after(AcceptanceTester $I)
    {
        // This method will be executed after each test
    }

    // Test rental page loads correctly
    public function testRentalPageLoads(AcceptanceTester $I)
    {
        $I->amOnPage('/udlejning.php');
        $I->see('Udlejning');
        $I->seeElement('header');
        $I->seeElement('footer');
    }

    // Test rental information is displayed
    public function testRentalInformationDisplayed(AcceptanceTester $I)
    {
        $I->amOnPage('/udlejning.php');
        $I->see('Priser');
        $I->see('Faciliteter');
        $I->see('Booking');
    }

    // Test rental calendar is displayed
    public function testRentalCalendarDisplayed(AcceptanceTester $I)
    {
        $I->amOnPage('/udlejning.php');
        $I->seeElement('.calendar');
        $I->seeElement('.calendar-month');
    }

    // Test booking form is displayed
    public function testBookingFormDisplayed(AcceptanceTester $I)
    {
        $I->amOnPage('/udlejning.php');
        $I->seeElement('form');
        $I->seeElement('input[name="name"]');
        $I->seeElement('input[name="email"]');
        $I->seeElement('input[name="phone"]');
        $I->seeElement('input[name="date"]');
        $I->seeElement('textarea[name="message"]');
        $I->seeElement('input[type="submit"]');
    }

    // Test booking form validation
    public function testBookingFormValidation(AcceptanceTester $I)
    {
        $I->amOnPage('/udlejning.php');
        $I->click('input[type="submit"]');
        // Check for validation error messages
        $I->see('Udfyld venligst alle felter');
    }

    // Test navigation from rental page
    public function testNavigationFromRentalPage(AcceptanceTester $I)
    {
        $I->amOnPage('/udlejning.php');
        $I->click('Hjem');
        $I->seeInCurrentUrl('/index.php');
        $I->see('Skelby Forsamlinghus');
    }
}