<?php

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    /**
     * @runInSeparateProcess
     */
    public function testConstructorSuccess()
    {
        // Mock environment variables
        putenv('HOST=localhost');
        putenv('DATABASE=test_db');
        putenv('USERNAME=test_user');
        putenv('PASSWORD=test_pass');

        // Mock PDO to avoid actual database connection
        $pdoMock = $this->createMock(PDO::class);

        // Create a mock builder for PDO
        $pdoMockBuilder = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor();

        // Create the mock
        $pdoMock = $pdoMockBuilder->getMock();

        // Replace the PDO class with our mock
        $this->registerMockPdoInNamespace($pdoMock);

        // Create the Database instance
        $database = new Database();

        // Verify that dbh is set
        $reflection = new ReflectionClass($database);
        $property = $reflection->getProperty('dbh');
        $property->setAccessible(true);
        $this->assertInstanceOf(PDO::class, $property->getValue($database));
    }

    /**
     * @runInSeparateProcess
     */
    public function testConstructorFailureDevelopment()
    {
        // Mock environment variables
        putenv('HOST=localhost');
        putenv('DATABASE=test_db');
        putenv('USERNAME=test_user');
        putenv('PASSWORD=test_pass');
        putenv('ENVIRONMENT=development');

        // Create a mock for PDOException
        $pdoException = new PDOException('Test PDO Exception');

        // Create a mock builder for PDO that throws an exception
        $pdoMockBuilder = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor();

        // Configure the mock to throw an exception when constructed
        $pdoMockBuilder->setMockClassName('PDO');

        // Replace the PDO class with our mock that throws an exception
        $this->registerMockPdoThatThrowsException($pdoException);

        // Expect an exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database connection failed: Test PDO Exception');

        // Create the Database instance
        $database = new Database();
    }

    /**
     * @runInSeparateProcess
     */
    public function testConstructorFailureProduction()
    {
        // Mock environment variables
        putenv('HOST=localhost');
        putenv('DATABASE=test_db');
        putenv('USERNAME=test_user');
        putenv('PASSWORD=test_pass');
        putenv('ENVIRONMENT=production');

        // Create a mock for PDOException
        $pdoException = new PDOException('Test PDO Exception');

        // Create a mock builder for PDO that throws an exception
        $pdoMockBuilder = $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor();

        // Configure the mock to throw an exception when constructed
        $pdoMockBuilder->setMockClassName('PDO');

        // Replace the PDO class with our mock that throws an exception
        $this->registerMockPdoThatThrowsException($pdoException);

        // Expect an exception
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database connection failed. Please try again later or contact support.');

        // Create the Database instance
        $database = new Database();
    }

    /**
     * Helper method to register a mock PDO in the global namespace
     */
    private function registerMockPdoInNamespace($pdoMock)
    {
        // Define a class_exists function that returns false for PDO
        eval('namespace {
            function class_exists($class) {
                return $class !== "PDO" && \class_exists($class);
            }
            
            class PDO extends \stdClass {
                public function __construct() {
                    return new \stdClass();
                }
            }
        }');
    }

    /**
     * Helper method to register a mock PDO that throws an exception
     */
    private function registerMockPdoThatThrowsException($pdoException)
    {
        // Define a PDO class that throws an exception when constructed
        eval('namespace {
            function class_exists($class) {
                return $class !== "PDO" && \class_exists($class);
            }
            
            class PDO extends \stdClass {
                public function __construct() {
                    throw new \PDOException("' . $pdoException->getMessage() . '");
                }
            }
        }');
    }
}