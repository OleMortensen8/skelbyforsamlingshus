<?php

use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    private $customer;
    private $dbhMock;

    protected function setUp(): void
    {
        // Create a mock for the PDO class
        $this->dbhMock = $this->createMock(PDO::class);

        // Create a mock for the Database class
        $databaseMock = $this->getMockBuilder(Database::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Configure the mock to return the mock PDO object
        $databaseMock->method('__get')
            ->with('dbh')
            ->willReturn($this->dbhMock);

        // Create a partial mock for Customer class
        $this->customer = $this->getMockBuilder(Customer::class)
            ->setConstructorArgs([null, null])
            ->onlyMethods(['createOrFetchCustomer'])
            ->getMock();

        // Set the mock PDO object on the Customer instance
        $reflection = new ReflectionClass($this->customer);
        $property = $reflection->getProperty('dbh');
        $property->setAccessible(true);
        $property->setValue($this->customer, $this->dbhMock);
    }

    public function testGetCustomerId()
    {
        // Set the customerId property
        $reflection = new ReflectionClass($this->customer);
        $property = $reflection->getProperty('customerId');
        $property->setAccessible(true);
        $property->setValue($this->customer, 123);

        // Test the method
        $this->assertEquals(123, $this->customer->getCustomerId());
    }

    public function testGetCustomerIdFromBooking()
    {
        // Create a mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetchColumn')->willReturn(456);

        // Configure the mock PDO to return the mock statement
        $this->dbhMock->method('prepare')->willReturn($stmtMock);

        // Call the method
        $this->customer->getCustomerIdFromBooking(789);

        // Verify the customerId was set
        $reflection = new ReflectionClass($this->customer);
        $property = $reflection->getProperty('customerId');
        $property->setAccessible(true);
        $this->assertEquals(456, $property->getValue($this->customer));
    }

    public function testHasBookingsWithBookings()
    {
        // Create a mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetchColumn')->willReturn(5); // 5 bookings

        // Configure the mock PDO to return the mock statement
        $this->dbhMock->method('prepare')->willReturn($stmtMock);

        // Test the method
        $this->assertTrue($this->customer->hasBookings(123));
    }

    public function testHasBookingsWithoutBookings()
    {
        // Create a mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->method('execute')->willReturn(true);
        $stmtMock->method('fetchColumn')->willReturn(0); // No bookings

        // Configure the mock PDO to return the mock statement
        $this->dbhMock->method('prepare')->willReturn($stmtMock);

        // Test the method
        $this->assertFalse($this->customer->hasBookings(123));
    }

    public function testDeleteCustomer()
    {
        // Create a mock statement
        $stmtMock = $this->createMock(PDOStatement::class);
        $stmtMock->expects($this->once())->method('execute')->with([123])->willReturn(true);

        // Configure the mock PDO to return the mock statement
        $this->dbhMock->expects($this->once())->method('prepare')->with('DELETE FROM Customers WHERE customer_id = ?')->willReturn($stmtMock);

        // Test the method
        $this->customer->deleteCustomer(123);
    }
}