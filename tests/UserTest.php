<?php

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private $user;
    private $dbhMock;

    protected function setUp(): void
    {
        // Create a mock for the PDO class
        $this->dbhMock = $this->createMock(PDO::class);

        // Create a partial mock for User class to avoid loading session_config.php
        $this->user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct'])
            ->getMock();

        // Set the mock PDO object on the User instance
        $reflection = new ReflectionClass($this->user);
        $property = $reflection->getProperty('dbh');
        $property->setAccessible(true);
        $property->setValue($this->user, $this->dbhMock);
    }

    public function testIsLoggedIn()
    {
        // Set the isLoggedIn property
        $reflection = new ReflectionClass($this->user);
        $property = $reflection->getProperty('isLoggedIn');
        $property->setAccessible(true);
        $property->setValue($this->user, true);

        // Test the method
        $this->assertTrue($this->user->isLoggedIn());
    }

    public function testHasRoleWithSingleRole()
    {
        // Set the isLoggedIn and role properties
        $reflection = new ReflectionClass($this->user);
        $isLoggedInProperty = $reflection->getProperty('isLoggedIn');
        $isLoggedInProperty->setAccessible(true);
        $isLoggedInProperty->setValue($this->user, true);

        $roleProperty = $reflection->getProperty('role');
        $roleProperty->setAccessible(true);
        $roleProperty->setValue($this->user, 'admin');

        // Test the method
        $this->assertTrue($this->user->hasRole('admin'));
        $this->assertFalse($this->user->hasRole('user'));
    }

    public function testHasRoleWithArrayOfRoles()
    {
        // Set the isLoggedIn and role properties
        $reflection = new ReflectionClass($this->user);
        $isLoggedInProperty = $reflection->getProperty('isLoggedIn');
        $isLoggedInProperty->setAccessible(true);
        $isLoggedInProperty->setValue($this->user, true);

        $roleProperty = $reflection->getProperty('role');
        $roleProperty->setAccessible(true);
        $roleProperty->setValue($this->user, 'admin');

        // Test the method
        $this->assertTrue($this->user->hasRole(['admin', 'user']));
        $this->assertFalse($this->user->hasRole(['user', 'editor']));
    }

    public function testHasRoleWhenNotLoggedIn()
    {
        // Set the isLoggedIn property to false
        $reflection = new ReflectionClass($this->user);
        $property = $reflection->getProperty('isLoggedIn');
        $property->setAccessible(true);
        $property->setValue($this->user, false);

        // Test the method
        $this->assertFalse($this->user->hasRole('admin'));
    }

    public function testGetId()
    {
        // Set the id property
        $reflection = new ReflectionClass($this->user);
        $property = $reflection->getProperty('id');
        $property->setAccessible(true);
        $property->setValue($this->user, 123);

        // Test the method
        $this->assertEquals(123, $this->user->getId());
    }

    public function testGetUsername()
    {
        // Set the username property
        $reflection = new ReflectionClass($this->user);
        $property = $reflection->getProperty('username');
        $property->setAccessible(true);
        $property->setValue($this->user, 'testuser');

        // Test the method
        $this->assertEquals('testuser', $this->user->getUsername());
    }

    public function testGetEmail()
    {
        // Set the email property
        $reflection = new ReflectionClass($this->user);
        $property = $reflection->getProperty('email');
        $property->setAccessible(true);
        $property->setValue($this->user, 'test@example.com');

        // Test the method
        $this->assertEquals('test@example.com', $this->user->getEmail());
    }

    public function testGetRole()
    {
        // Set the role property
        $reflection = new ReflectionClass($this->user);
        $property = $reflection->getProperty('role');
        $property->setAccessible(true);
        $property->setValue($this->user, 'admin');

        // Test the method
        $this->assertEquals('admin', $this->user->getRole());
    }

    /**
     * @runInSeparateProcess
     */
    public function testLogout()
    {
        // Mock session functions
        $this->mockSessionFunctions();

        // Set properties
        $reflection = new ReflectionClass($this->user);

        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($this->user, 123);

        $usernameProperty = $reflection->getProperty('username');
        $usernameProperty->setAccessible(true);
        $usernameProperty->setValue($this->user, 'testuser');

        $emailProperty = $reflection->getProperty('email');
        $emailProperty->setAccessible(true);
        $emailProperty->setValue($this->user, 'test@example.com');

        $roleProperty = $reflection->getProperty('role');
        $roleProperty->setAccessible(true);
        $roleProperty->setValue($this->user, 'admin');

        $isLoggedInProperty = $reflection->getProperty('isLoggedIn');
        $isLoggedInProperty->setAccessible(true);
        $isLoggedInProperty->setValue($this->user, true);

        // Call the method
        $this->user->logout();

        // Verify properties were reset
        $this->assertNull($idProperty->getValue($this->user));
        $this->assertNull($usernameProperty->getValue($this->user));
        $this->assertNull($emailProperty->getValue($this->user));
        $this->assertNull($roleProperty->getValue($this->user));
        $this->assertFalse($isLoggedInProperty->getValue($this->user));
    }

    public function testCreateUserSuccess()
    {
        // Create a mock statement for checking if user exists
        $checkStmtMock = $this->createMock(PDOStatement::class);
        $checkStmtMock->method('execute')->willReturn(true);
        $checkStmtMock->method('fetch')->willReturn(false); // User doesn't exist

        // Create a mock statement for inserting user
        $insertStmtMock = $this->createMock(PDOStatement::class);
        $insertStmtMock->method('execute')->willReturn(true);

        // Configure the mock PDO to return the mock statements
        $this->dbhMock->method('prepare')
            ->will($this->returnCallback(function ($sql) use ($checkStmtMock, $insertStmtMock) {
                if (strpos($sql, 'SELECT') !== false) {
                    return $checkStmtMock;
                } else {
                    return $insertStmtMock;
                }
            }));

        // Configure the mock PDO to return a last insert ID
        $this->dbhMock->method('lastInsertId')->willReturn('123');

        // Test the method
        $result = $this->user->createUser('testuser', 'password', 'test@example.com', 'user');
        $this->assertEquals('123', $result);
    }

    public function testCreateUserFailureUserExists()
    {
        // Create a mock statement for checking if user exists
        $checkStmtMock = $this->createMock(PDOStatement::class);
        $checkStmtMock->method('execute')->willReturn(true);
        $checkStmtMock->method('fetch')->willReturn(['id' => 123]); // User exists

        // Configure the mock PDO to return the mock statement
        $this->dbhMock->method('prepare')->willReturn($checkStmtMock);

        // Test the method
        $result = $this->user->createUser('testuser', 'password', 'test@example.com', 'user');
        $this->assertFalse($result);
    }

    /**
     * Helper method to mock session functions
     */
    private function mockSessionFunctions()
    {
        // Define session functions
        eval('namespace {
            $_SESSION = [];
            
            function session_destroy() {
                $_SESSION = [];
                return true;
            }
        }');
    }
}