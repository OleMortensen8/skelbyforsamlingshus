<?php

use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    private $auth;
    private $userMock;

    protected function setUp(): void
    {
        // Create a mock for the User class
        $this->userMock = $this->createMock(User::class);

        // Create a partial mock for Auth class to avoid loading session_config.php
        $this->auth = $this->getMockBuilder(Auth::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['__construct'])
            ->getMock();

        // Set the mock User object on the Auth instance
        $reflection = new ReflectionClass($this->auth);
        $property = $reflection->getProperty('user');
        $property->setAccessible(true);
        $property->setValue($this->auth, $this->userMock);
    }

    public function testIsLoggedIn()
    {
        // Configure the mock User to return true for isLoggedIn
        $this->userMock->method('isLoggedIn')->willReturn(true);

        // Test the method
        $this->assertTrue($this->auth->isLoggedIn());
    }

    public function testHasRole()
    {
        // Configure the mock User to return true for hasRole
        $this->userMock->method('hasRole')->with('admin')->willReturn(true);

        // Test the method
        $this->assertTrue($this->auth->hasRole('admin'));
    }

    public function testGetUser()
    {
        // Test the method
        $this->assertSame($this->userMock, $this->auth->getUser());
    }

    /**
     * @runInSeparateProcess
     */
    public function testRequireLoginWhenLoggedIn()
    {
        // Configure the mock User to return true for isLoggedIn
        $this->userMock->method('isLoggedIn')->willReturn(true);

        // Test the method
        $result = $this->auth->requireLogin();

        // Verify that the method returns the user
        $this->assertSame($this->userMock, $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRequireLoginWhenNotLoggedIn()
    {
        // Configure the mock User to return false for isLoggedIn
        $this->userMock->method('isLoggedIn')->willReturn(false);

        // Mock the header function
        $this->mockHeaderFunction();

        // Expect an exit
        $this->expectException(ExitException::class);

        // Test the method
        $this->auth->requireLogin();
    }

    /**
     * @runInSeparateProcess
     */
    public function testRequireRoleWhenLoggedInWithRole()
    {
        // Configure the mock User to return true for isLoggedIn and hasRole
        $this->userMock->method('isLoggedIn')->willReturn(true);
        $this->userMock->method('hasRole')->with('admin')->willReturn(true);

        // Test the method
        $result = $this->auth->requireRole('admin');

        // Verify that the method returns the user
        $this->assertSame($this->userMock, $result);
    }

    /**
     * @runInSeparateProcess
     */
    public function testRequireRoleWhenLoggedInWithoutRole()
    {
        // Configure the mock User to return true for isLoggedIn but false for hasRole
        $this->userMock->method('isLoggedIn')->willReturn(true);
        $this->userMock->method('hasRole')->with('admin')->willReturn(false);

        // Mock the include function
        $this->mockIncludeFunction();

        // Expect an exit
        $this->expectException(ExitException::class);

        // Test the method
        $this->auth->requireRole('admin');
    }

    /**
     * @runInSeparateProcess
     */
    public function testRequireRoleWhenNotLoggedIn()
    {
        // Configure the mock User to return false for isLoggedIn
        $this->userMock->method('isLoggedIn')->willReturn(false);

        // Mock the header function
        $this->mockHeaderFunction();

        // Expect an exit
        $this->expectException(ExitException::class);

        // Test the method
        $this->auth->requireRole('admin');
    }

    /**
     * Helper method to mock the header function
     */
    private function mockHeaderFunction()
    {
        // Define a header function that throws an ExitException
        eval('namespace {
            function header($header) {
                // Do nothing
            }
            
            class ExitException extends \Exception {}
            
            function exit() {
                throw new ExitException("Exit called");
            }
        }');
    }

    /**
     * Helper method to mock the include function
     */
    private function mockIncludeFunction()
    {
        // Define an include function that does nothing
        eval('namespace {
            function include($file) {
                // Do nothing
            }
        }');
    }
}