<?php

class MembershipCest
{
    public function _before(AcceptanceTester $I)
    {
        // This method will be executed before each test
    }

    public function _after(AcceptanceTester $I)
    {
        // This method will be executed after each test
    }

    // Test become member page loads correctly
    public function testBecomeMemberPageLoads(AcceptanceTester $I)
    {
        $I->amOnPage('/blivMedlem.php');
        $I->see('Bliv Medlem');
        $I->seeElement('header');
        $I->seeElement('footer');
    }

    // Test member registration form is displayed
    public function testMemberRegistrationFormDisplayed(AcceptanceTester $I)
    {
        $I->amOnPage('/blivMedlem.php');
        $I->seeElement('form');
        $I->seeElement('input[name="name"]');
        $I->seeElement('input[name="email"]');
        $I->seeElement('input[name="phone"]');
        $I->seeElement('input[type="submit"]');
    }

    // Test member page loads correctly
    public function testMemberPageLoads(AcceptanceTester $I)
    {
        $I->amOnPage('/medlem.php');
        $I->see('Medlem');
        $I->seeElement('header');
        $I->seeElement('footer');
    }

    // Test member login form is displayed
    public function testMemberLoginFormDisplayed(AcceptanceTester $I)
    {
        $I->amOnPage('/medlem.php');
        $I->seeElement('form');
        $I->seeElement('input[name="email"]');
        $I->seeElement('input[name="password"]');
        $I->seeElement('input[type="submit"]');
    }

    // Test member registration form validation
    public function testMemberRegistrationFormValidation(AcceptanceTester $I)
    {
        $I->amOnPage('/blivMedlem.php');
        $I->click('input[type="submit"]');
        // Check for validation error messages
        $I->see('Udfyld venligst alle felter');
    }

    // Test navigation from member pages
    public function testNavigationFromMemberPages(AcceptanceTester $I)
    {
        $I->amOnPage('/blivMedlem.php');
        $I->click('Hjem');
        $I->seeInCurrentUrl('/index.php');

        $I->amOnPage('/medlem.php');
        $I->click('Hjem');
        $I->seeInCurrentUrl('/index.php');
    }
}