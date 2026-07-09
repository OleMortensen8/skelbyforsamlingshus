<?php
use PHPUnit\Framework\TestCase;

class BookableCellTest extends TestCase
{
    private $bookableCell;

    protected function setUp(): void
    {
        $booking = $this->getMockBuilder(Booking::class)
            ->disableOriginalConstructor()
            ->getMock();
        //create an instance of BookableCell
        $this->bookableCell = new BookableCell($booking);
    }

    // Test for checking openCell() method
    public function testOpenCell()
    {
        $date = date('Y-m-d');
        $this->assertStringContainsString($date, $this->bookableCell->openCell($date));
    }

    // Test for checking pendingCell() method
    public function testPendingCell()
    {
        $date = date('Y-m-d');
        $this->assertStringContainsString($date, $this->bookableCell->pendingCell($date));
    }

    // Test for checking isDatePending() method
    public function testIsDatePending()
    {
        $date = date('Y-m-d');
        $this->assertFalse($this->bookableCell->isDatePending($date));
    }

    // Test for checking isDateBooked() method
    public function testIsDateBooked()
    {
        $date = date('Y-m-d');
        $this->assertFalse($this->bookableCell->isDateBooked($date));
    }
    
    // Test for checking addBooking() method
    public function testAddBooking()
    {
        $dateArray = [date('Y-m-d'), '1'];
        $name = 'John Doe';
        $this->assertNull($this->bookableCell->addBooking($dateArray, true, $name));
    }
}