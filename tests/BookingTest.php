<?php
use PHPUnit\Framework\TestCase;

class BookingTest extends TestCase {
    protected $booking;

    protected function setUp(): void {
        $this->booking = new Booking(); 
    }

    public function test_construct() {
        // Test the construct method
        $this->assertInstanceOf(PDO::class, $this->booking->dbh);
    }

    public function test_index() {
        // Test the index method
        $this->assertIsArray($this->booking->index());
    }

    public function test_add() {
        // Test the add method
        $test_booking_date = array(
            array('2021-10-10', 0, 'Test Name')
        );
        $response = $this->booking->add($test_booking_date);
        $this->assertEquals("success", $response["status"]);
    }

    public function test_prove() {
        // Test the prove method
        $test_dates = array('2021-10-10');
        $this->booking->prove($test_dates); 
        // Assert successful execution
    }

    public function test_delete() {
        // Test the delete method
        $test_dates = array('2021-10-10');
        $this->booking->delete($test_dates); 
        // Assert successful execution
    }

    public function test_addAjax() {
        // Test the addAjax method
        $test_booking_date = array(
            array('2021-10-10', 0, 'Test Name 2')
        );
        $response = $this->booking->addAjax($test_booking_date);
        $this->assertEquals("success", $response["status"]);
    }
}