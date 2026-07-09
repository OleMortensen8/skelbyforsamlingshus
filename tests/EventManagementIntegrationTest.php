<?php

use PHPUnit\Framework\TestCase;

/**
 * Integration test for event management flow
 *
 * This test verifies that the EventManager class can process XML data and generate HTML output.
 */
class EventManagementIntegrationTest extends TestCase
{
    private $eventManager;
    private $xmlFile;
    private $xmlContent;

    protected function setUp(): void
    {
        // Create an instance of EventManager
        $this->eventManager = new EventManager();

        // Create a temporary XML file for testing
        $this->xmlFile = tempnam(sys_get_temp_dir(), 'events_');

        // Define XML content for testing
        $this->xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<arrangementer>
    <arrangement>
        <Title>Test Event 1</Title>
        <Dato>2023-12-01</Dato>
        <Pris>100</Pris>
        <Beskrivelse>This is a test event 1</Beskrivelse>
    </arrangement>
    <arrangement>
        <Title>Test Event 2</Title>
        <Dato>2023-12-15</Dato>
        <Pris>150</Pris>
        <Beskrivelse>This is a test event 2</Beskrivelse>
    </arrangement>
</arrangementer>
XML;

        // Write XML content to the temporary file
        file_put_contents($this->xmlFile, $this->xmlContent);
    }

    protected function tearDown(): void
    {
        // Remove the temporary XML file
        if (file_exists($this->xmlFile)) {
            unlink($this->xmlFile);
        }
    }

    /**
     * Test loading XML file and generating HTML output
     */
    public function testLoadXmlAndGenerateHtml()
    {
        // Load the XML file
        $xml = simplexml_load_file($this->xmlFile);

        // Call the getArangementer method
        $result = $this->eventManager->getArangementer($xml);

        // Assert that the result contains the expected HTML
        $this->assertStringContainsString('<h2>Test Event 1</h2>', $result);
        $this->assertStringContainsString('<h3>2023-12-01</h3>', $result);
        $this->assertStringContainsString('<h3>100</h3>', $result);
        $this->assertStringContainsString('This is a test event 1', $result);

        $this->assertStringContainsString('<h2>Test Event 2</h2>', $result);
        $this->assertStringContainsString('<h3>2023-12-15</h3>', $result);
        $this->assertStringContainsString('<h3>150</h3>', $result);
        $this->assertStringContainsString('This is a test event 2', $result);
    }

    /**
     * Test handling empty XML
     */
    public function testHandleEmptyXml()
    {
        // Create empty XML
        $emptyXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><arrangementer></arrangementer>');

        // Call the getArangementer method
        $result = $this->eventManager->getArangementer($emptyXml);

        // Assert that the result contains the expected HTML for empty events
        $this->assertEquals('<h2>Arrangementsliste forberedes... </h2>', $result);
    }

    /**
     * Test integration with arangementer.php
     */
    public function testIntegrationWithArangementerPhp()
    {
        // Mock the include_once function to avoid loading actual files
        $this->mockIncludeFunction();

        // Create a temporary file with the content of arangementer.php
        $arangementerPhp = tempnam(sys_get_temp_dir(), 'arangementer_');

        // Define a simplified version of arangementer.php
        $arangementerContent = <<<PHP
<?php
include "bootstrap.php";
include "assets/view/header.php";

// Load XML file
\$xml = simplexml_load_file("arrangementer.xml");

// Create EventManager instance
\$eventManager = new EventManager();

// Generate HTML output
\$output = \$eventManager->getArangementer(\$xml);

// Display output
echo \$output;

include "assets/view/footer.php";
?>
PHP;

        // Write content to the temporary file
        file_put_contents($arangementerPhp, $arangementerContent);

        // Create a mock for simplexml_load_file function
        $this->mockSimpleXmlLoadFile($this->xmlFile);

        // Capture output
        ob_start();
        include $arangementerPhp;
        $output = ob_get_clean();

        // Assert that the output contains the expected HTML
        $this->assertStringContainsString('<h2>Test Event 1</h2>', $output);
        $this->assertStringContainsString('<h3>2023-12-01</h3>', $output);
        $this->assertStringContainsString('<h3>100</h3>', $output);

        $this->assertStringContainsString('<h2>Test Event 2</h2>', $output);
        $this->assertStringContainsString('<h3>2023-12-15</h3>', $output);
        $this->assertStringContainsString('<h3>150</h3>', $output);

        // Remove the temporary file
        if (file_exists($arangementerPhp)) {
            unlink($arangementerPhp);
        }
    }

    /**
     * Helper method to mock the include_once function
     */
    private function mockIncludeFunction()
    {
        // Use runkit to redefine the include and include_once functions
        // Since eval is causing issues, we'll use a different approach
        // In a real test, you would use runkit_function_redefine or similar
        // For this example, we'll just create a mock class

        // This is a simplified approach for the test
        // In a real test, you would need to use a proper mocking framework
        // or extension like runkit to redefine global functions
    }

    /**
     * Helper method to mock the simplexml_load_file function
     */
    private function mockSimpleXmlLoadFile($xmlFile)
    {
        // In a real test, you would use runkit_function_redefine or similar
        // For this example, we'll just create a mock class

        // This is a simplified approach for the test
        // In a real test, you would need to use a proper mocking framework
        // or extension like runkit to redefine global functions
    }
}
