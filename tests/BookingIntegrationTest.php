<?php

use PHPUnit\Framework\TestCase;

/**
 * Integration test for booking flow
 *
 * This test verifies that the BookableCell class can create bookings through the Booking class and Customer class.
 */
class BookingIntegrationTest extends TestCase
{
    private $dbMock;
    private $pdoStatementMock;
    private $bookingMock;
    private $customerMock;
    private $bookableCell;

    protected function setUp(): void
    {
        // Create a mock for PDO
        $this->dbMock = $this->createMock(PDO::class);

        // Create a mock for PDOStatement
        $this->pdoStatementMock = $this->createMock(PDOStatement::class);

        // Configure the PDO mock to return the PDOStatement mock when prepare is called
        $this->dbMock->method('prepare')->willReturn($this->pdoStatementMock);

        // Create a mock for Customer class
        $this->customerMock = $this->getMockBuilder(Customer::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create a mock for Booking class
        $this->bookingMock = $this->getMockBuilder(Booking::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Create a partial mock for BookableCell class
        $this->bookableCell = $this->getMockBuilder(BookableCell::class)
            ->setConstructorArgs([$this->bookingMock])
            ->onlyMethods(['createCustomer'])
            ->getMock();

        // Configure the BookableCell mock to return the Customer mock when createCustomer is called
        $this->bookableCell->method('createCustomer')->willReturn($this->customerMock);

        // Mock the session functions
        $this->mockSessionFunctions();
    }

    /**
     * Test adding a booking
     */
    public function testAddBooking()
    {
        // Configure the Customer mock to return a customer ID
        $this->customerMock->method('getCustomerId')->willReturn(123);

        // Configure the Booking mock to return a success response
        $this->bookingMock->method('createBooking')->willReturn(json_encode([
            'status' => 'success',
            'message' => 'Booking blev Tilføjet Successfuldt.',
            'bookingIds' => [456]
        ]));

        // Call the addBooking method
        $dateArray = ['2023-12-01', 1]; // Date and number of days
        $name = 'Test User';
        $result = $this->bookableCell->addBooking($dateArray, true, $name);

        // Assert that the result is null (method doesn't return anything)
        $this->assertNull($result);
    }

    /**
     * Test checking if a date is pending
     */
    public function testIsDatePending()
    {
        // Configure the Booking mock to return bookings
        $this->bookingMock->method('index')->willReturn([
            ['booking_id' => 1, 'booking_date' => '2023-12-01', 'approved' => 0],
            ['booking_id' => 2, 'booking_date' => '2023-12-02', 'approved' => 1]
        ]);

        // Use reflection to access the private method
        $reflection = new ReflectionClass($this->bookableCell);
        $method = $reflection->getMethod('isDatePending');
        $method->setAccessible(true);

        // Call the isDatePending method for a pending date
        $result = $method->invoke($this->bookableCell, '2023-12-01');

        // Assert that the date is pending
        $this->assertTrue($result);

        // Call the isDatePending method for a non-pending date
        $result = $method->invoke($this->bookableCell, '2023-12-03');

        // Assert that the date is not pending
        $this->assertFalse($result);
    }

    /**
     * Test checking if a date is booked
     */
    public function testIsDateBooked()
    {
        // Configure the Booking mock to return bookings
        $this->bookingMock->method('index')->willReturn([
            ['booking_id' => 1, 'booking_date' => '2023-12-01', 'approved' => 0],
            ['booking_id' => 2, 'booking_date' => '2023-12-02', 'approved' => 1]
        ]);

        // Use reflection to access the private method
        $reflection = new ReflectionClass($this->bookableCell);
        $method = $reflection->getMethod('isDateBooked');
        $method->setAccessible(true);

        // Call the isDateBooked method for a booked date
        $result = $method->invoke($this->bookableCell, '2023-12-02');

        // Assert that the date is booked
        $this->assertTrue($result);

        // Call the isDateBooked method for a non-booked date
        $result = $method->invoke($this->bookableCell, '2023-12-03');

        // Assert that the date is not booked
        $this->assertFalse($result);
    }

    /**
     * Test the booking form generation
     */
    public function testBookingForm()
    {
        // Call the bookingForm method
        ob_start();
        $this->bookableCell->bookingForm();
        $output = ob_get_clean();

        // Assert that the output contains the form elements
        $this->assertStringContainsString('<form name="form1" id="form1"', $output);
        $this->assertStringContainsString('input type="text" name="navnet"', $output);
        $this->assertStringContainsString('input type="text" name="adresse"', $output);
        $this->assertStringContainsString('input type="text" name="telefon"', $output);
        $this->assertStringContainsString('input type="text" name="mail"', $output);
        $this->assertStringContainsString('input type="hidden" name="sdate"', $output);
        $this->assertStringContainsString('input type="hidden" name="enddate"', $output);
        $this->assertStringContainsString('input type="submit" id="sub"', $output);
    }

    /**
     * Test the open cell rendering
     */
    public function testOpenCell()
    {
        // Use reflection to access the private method
        $reflection = new ReflectionClass($this->bookableCell);
        $method = $reflection->getMethod('openCell');
        $method->setAccessible(true);

        // Call the openCell method
        $date = '2023-12-01';
        $result = $method->invoke($this->bookableCell, $date);

        // Assert that the result contains the expected HTML
        $this->assertStringContainsString('<div class="open"', $result);
        $this->assertStringContainsString('value="' . $date . '"', $result);
        $this->assertStringContainsString($date, $result);
    }

    /**
     * Test the pending cell rendering
     */
    public function testPendingCell()
    {
        // Use reflection to access the private method
        $reflection = new ReflectionClass($this->bookableCell);
        $method = $reflection->getMethod('pendingCell');
        $method->setAccessible(true);

        // Call the pendingCell method
        $date = '2023-12-01';
        $result = $method->invoke($this->bookableCell, $date);

        // Assert that the result contains the expected HTML
        $this->assertStringContainsString('<div class="pending"', $result);
        $this->assertStringContainsString($date, $result);
    }

    /**
     * Test the booked cell rendering
     */
    public function testBookedCell()
    {
        // Use reflection to access the private method
        $reflection = new ReflectionClass($this->bookableCell);
        $method = $reflection->getMethod('bookedCell');
        $method->setAccessible(true);

        // Call the bookedCell method
        $date = '2023-12-01';
        $result = $method->invoke($this->bookableCell, $date);

        // Assert that the result contains the expected HTML
        $this->assertStringContainsString('<div class="booked"', $result);
        $this->assertStringContainsString($date, $result);
    }

    /**
     * Helper method to mock session functions
     */
    private function mockSessionFunctions()
    {
        // Define session functions
        eval('namespace {
            $_SESSION = [];

            function session_regenerate_id($delete_old_session = false) {
                return true;
            }

            function session_destroy() {
                $_SESSION = [];
                return true;
            }
        }');
    }
}
