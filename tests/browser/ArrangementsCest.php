<?php

class ArrangementsCest
{
    public function _before(AcceptanceTester $I)
    {
        // This method will be executed before each test
    }

    public function _after(AcceptanceTester $I)
    {
        // This method will be executed after each test
    }

    // Test arrangements page loads correctly
    public function testArrangementsPageLoads(AcceptanceTester $I)
    {
        $I->amOnPage('/arangementer.php');
        $I->see('Arrangementer');
        $I->seeElement('header');
        $I->seeElement('footer');
    }

    // Test arrangements list is displayed
    public function testArrangementsListDisplayed(AcceptanceTester $I)
    {
        $I->amOnPage('/arangementer.php');
        $I->seeElement('.arrangement-list');
        // Check for common elements that should be in the arrangements list
        $I->seeElement('.arrangement-item');
    }

    // Test arrangement details are displayed
    public function testArrangementDetailsDisplayed(AcceptanceTester $I)
    {
        $I->amOnPage('/arangementer.php');
        // Assuming there's at least one arrangement item with details
        $I->seeElement('.arrangement-item');
        $I->seeElement('.arrangement-title');
        $I->seeElement('.arrangement-date');
        $I->seeElement('.arrangement-description');
    }

    // Test navigation from arrangements page
    public function testNavigationFromArrangementsPage(AcceptanceTester $I)
    {
        $I->amOnPage('/arangementer.php');
        $I->click('Hjem');
        $I->seeInCurrentUrl('/index.php');
        $I->see('Skelby Forsamlinghus');
    }
}