<?php

class LoginCest
{
    public function _before(AcceptanceTester $I)
    {
        // This method will be executed before each test
    }

    public function _after(AcceptanceTester $I)
    {
        // This method will be executed after each test
    }

    // Test login page loads correctly
    public function testLoginPageLoads(AcceptanceTester $I)
    {
        $I->amOnPage('/login.php');
        $I->see('Login');
        $I->seeElement('header');
        $I->seeElement('footer');
    }

    // Test login form is displayed
    public function testLoginFormDisplayed(AcceptanceTester $I)
    {
        $I->amOnPage('/login.php');
        $I->seeElement('form');
        $I->seeElement('input[name="username"]');
        $I->seeElement('input[name="password"]');
        $I->seeElement('input[type="submit"]');
    }

    // Test login form validation
    public function testLoginFormValidation(AcceptanceTester $I)
    {
        $I->amOnPage('/login.php');
        $I->click('input[type="submit"]');
        // Check for validation error messages
        $I->see('Udfyld venligst alle felter');
    }

    // Test login with invalid credentials
    public function testLoginWithInvalidCredentials(AcceptanceTester $I)
    {
        $I->amOnPage('/login.php');
        $I->fillField('username', 'invalid_user');
        $I->fillField('password', 'invalid_password');
        $I->click('input[type="submit"]');
        // Check for error message
        $I->see('Forkert brugernavn eller adgangskode');
    }

    // Test navigation from login page
    public function testNavigationFromLoginPage(AcceptanceTester $I)
    {
        $I->amOnPage('/login.php');
        $I->click('Hjem');
        $I->seeInCurrentUrl('/index.php');
        $I->see('Skelby Forsamlinghus');
    }

    // Test logout functionality
    public function testLogout(AcceptanceTester $I)
    {
        // Note: This test assumes the user is already logged in
        // In a real test, you would need to log in first
        $I->amOnPage('/logout.php');
        $I->seeInCurrentUrl('/index.php');
        // Check that user is logged out by seeing the login link
        $I->see('Login');
    }
}