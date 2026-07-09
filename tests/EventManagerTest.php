<?php

use PHPUnit\Framework\TestCase;

class EventManagerTest extends TestCase
{
    public function testGetArangementerWithChildren()
    {
        // Arrange
        $mockChild1 = $this->createStub(SimpleXMLElement::class);
        $mockChild1->Title = "Test Event 1";
        $mockChild1->Dato = "2023-11-01";
        $mockChild1->Pris = "100";

        $mockChild2 = $this->createStub(SimpleXMLElement::class);
        $mockChild2->Title = "Test Event 2";
        $mockChild2->Dato = "2023-11-15";
        $mockChild2->Pris = "150";

        $mockParent = $this->createStub(SimpleXMLElement::class);
        $mockParent->method('children')->willReturn([$mockChild1, $mockChild2]);

        $eventManager = new EventManager();

        // Act
        $result = $eventManager->getArangementer($mockParent);

        // Assert
        $this->assertStringContainsString("<h2>Test Event 1</h2>", $result);
        $this->assertStringContainsString("<h3>2023-11-01</h3>", $result);
        $this->assertStringContainsString("<h3>100</h3>", $result);

        $this->assertStringContainsString("<h2>Test Event 2</h2>", $result);
        $this->assertStringContainsString("<h3>2023-11-15</h3>", $result);
        $this->assertStringContainsString("<h3>150</h3>", $result);
    }

    public function testGetArangementerWithoutChildren()
    {
        // Arrange
        $mockParent = $this->createStub(SimpleXMLElement::class);
        $mockParent->method('children')->willReturn([]);

        $eventManager = new EventManager();

        // Act
        $result = $eventManager->getArangementer($mockParent);

        // Assert
        $this->assertEquals("<h2>Arrangementsliste forberedes... </h2>", $result);
    }
}