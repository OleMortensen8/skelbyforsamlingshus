<?php

use PHPUnit\Framework\TestCase;

/**
 * Integration test for user authentication flow
 *
 * This test verifies that the User class can authenticate a user with the Database class.
 */
class UserAuthenticationIntegrationTest extends TestCase
{
    private $dbMock;
    private $pdoStatementMock;

    protected function setUp(): void
    {
        // Create a mock for PDO
        $this->dbMock = $this->createMock(PDO::class);

        // Create a mock for PDOStatement
        $this->pdoStatementMock = $this->createMock(PDOStatement::class);

        // Configure the PDO mock to return the PDOStatement mock when prepare is called
        $this->dbMock->method('prepare')->willReturn($this->pdoStatementMock);

        // Mock the session functions
        $this->mockSessionFunctions();
    }

    /**
     * Test successful user authentication
     */
    public function testSuccessfulAuthentication()
    {
        // Configure the PDOStatement mock to return a user when fetch is called
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoStatementMock->method('fetch')->willReturn([
            'id' => 1,
            'username' => 'testuser',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        // Create a partial mock for User class to avoid loading session_config.php
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct', 'updateLastLogin'])
            ->getMock();

        // Set the mock PDO object on the User instance
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('dbh');
        $property->setAccessible(true);
        $property->setValue($user, $this->dbMock);

        // Call the login method
        $result = $user->login('testuser', 'password123');

        // Assert that the login was successful
        $this->assertTrue($result);

        // Assert that the user properties were set correctly
        $this->assertEquals(1, $user->getId());
        $this->assertEquals('testuser', $user->getUsername());
        $this->assertEquals('test@example.com', $user->getEmail());
        $this->assertEquals('admin', $user->getRole());

        // Assert that the user is logged in
        $this->assertTrue($user->isLoggedIn());

        // Assert that the session variables were set
        $this->assertEquals(1, $_SESSION['user_id']);
        $this->assertEquals('testuser', $_SESSION['username']);
        $this->assertEquals('test@example.com', $_SESSION['email']);
        $this->assertEquals('admin', $_SESSION['role']);
    }

    /**
     * Test failed user authentication with incorrect password
     */
    public function testFailedAuthenticationWithIncorrectPassword()
    {
        // Configure the PDOStatement mock to return a user when fetch is called
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoStatementMock->method('fetch')->willReturn([
            'id' => 1,
            'username' => 'testuser',
            'password' => password_hash('password123', PASSWORD_BCRYPT),
            'email' => 'test@example.com',
            'role' => 'admin'
        ]);

        // Create a partial mock for User class to avoid loading session_config.php
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct', 'updateLastLogin'])
            ->getMock();

        // Set the mock PDO object on the User instance
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('dbh');
        $property->setAccessible(true);
        $property->setValue($user, $this->dbMock);

        // Call the login method with incorrect password
        $result = $user->login('testuser', 'wrongpassword');

        // Assert that the login failed
        $this->assertFalse($result);

        // Assert that the user is not logged in
        $this->assertFalse($user->isLoggedIn());
    }

    /**
     * Test failed user authentication with non-existent user
     */
    public function testFailedAuthenticationWithNonExistentUser()
    {
        // Configure the PDOStatement mock to return false when fetch is called
        $this->pdoStatementMock->method('execute')->willReturn(true);
        $this->pdoStatementMock->method('fetch')->willReturn(false);

        // Create a partial mock for User class to avoid loading session_config.php
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct', 'updateLastLogin'])
            ->getMock();

        // Set the mock PDO object on the User instance
        $reflection = new ReflectionClass($user);
        $property = $reflection->getProperty('dbh');
        $property->setAccessible(true);
        $property->setValue($user, $this->dbMock);

        // Call the login method with non-existent user
        $result = $user->login('nonexistentuser', 'password123');

        // Assert that the login failed
        $this->assertFalse($result);

        // Assert that the user is not logged in
        $this->assertFalse($user->isLoggedIn());
    }

    /**
     * Test user logout
     */
    public function testLogout()
    {
        // Create a partial mock for User class to avoid loading session_config.php
        $user = $this->getMockBuilder(User::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct'])
            ->getMock();

        // Set the user properties
        $reflection = new ReflectionClass($user);

        $idProperty = $reflection->getProperty('id');
        $idProperty->setAccessible(true);
        $idProperty->setValue($user, 1);

        $usernameProperty = $reflection->getProperty('username');
        $usernameProperty->setAccessible(true);
        $usernameProperty->setValue($user, 'testuser');

        $emailProperty = $reflection->getProperty('email');
        $emailProperty->setAccessible(true);
        $emailProperty->setValue($user, 'test@example.com');

        $roleProperty = $reflection->getProperty('role');
        $roleProperty->setAccessible(true);
        $roleProperty->setValue($user, 'admin');

        $isLoggedInProperty = $reflection->getProperty('isLoggedIn');
        $isLoggedInProperty->setAccessible(true);
        $isLoggedInProperty->setValue($user, true);

        // Set session variables
        $_SESSION['user_id'] = 1;
        $_SESSION['username'] = 'testuser';
        $_SESSION['email'] = 'test@example.com';
        $_SESSION['role'] = 'admin';

        // Call the logout method
        $user->logout();

        // Assert that the user properties were reset
        $this->assertNull($idProperty->getValue($user));
        $this->assertNull($usernameProperty->getValue($user));
        $this->assertNull($emailProperty->getValue($user));
        $this->assertNull($roleProperty->getValue($user));
        $this->assertFalse($isLoggedInProperty->getValue($user));

        // Assert that the session variables were unset
        $this->assertEmpty($_SESSION);
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