<?php
use PHPUnit\Framework\TestCase;

class CalendarTest extends TestCase {

  protected $calendar;

  protected function setUp(): void {
    $this->calendar = new Calendar();
  }

  public function testConstructor() {
    $this->assertEquals(htmlentities($_SERVER['PHP_SELF']), $this->calendar->naviHref);
  }

  public function testAttachObserver() {
    $observer = $this->getMockBuilder('Observer')->getMock();
    $this->calendar->attachObserver('testType', $observer);
    $this->assertArrayHasKey('testType', $this->calendar->observers);
  }

  public function testNotifyObserver() {
    $observer = $this->getMockBuilder('Observer')->getMock();
    $observer->expects($this->once())
             ->method('update')
             ->with($this->equalTo($this->calendar));
    $this->calendar->attachObserver('testType', $observer);
    $this->calendar->notifyObserver('testType');
  }

  public function testGetCurrentDate() {
    $date = date('Y-m-d');
    $this->calendar->currentDate = $date;
    $this->assertEquals($date, $this->calendar->getCurrentDate());
  }

  public function testSetSundayFirst() {
    $this->calendar->setSundayFirst(false);
    $this->assertFalse($this->calendar->sundayFirst);
  }

  public function testShow() {
    $content = $this->calendar->show();
    $this->assertStringContainsString('<div id="calendar">', $content);
  }

}