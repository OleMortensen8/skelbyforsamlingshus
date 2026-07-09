<?php
namespace App;

/**
 * User Class
 *
 * Handles user authentication, password hashing, and role-based access control.
 */
class User extends Database
{
    private $id;
    private $username;
    private $email;
    private $role;
    private $isLoggedIn = false;

    /**
     * Constructor
     *
     * @param int|null $id User ID (optional)
     */
    public function __construct($id = null)
    {
        parent::__construct();

        // Include secure session configuration
        require_once __DIR__ . '/../config/session_config.php';

        // Check if user is logged in
        if (isset($_SESSION['user_id'])) {
            $this->id = $_SESSION['user_id'];
            $this->username = $_SESSION['username'];
            $this->email = $_SESSION['email'];
            $this->role = $_SESSION['role'];
            $this->isLoggedIn = true;
        } elseif ($id !== null) {
            // Load user by ID
            $this->loadUser($id);
        }
    }

    /**
     * Load user by ID
     *
     * @param int $id User ID
     * @return bool True if user was loaded, false otherwise
     */
    private function loadUser($id)
    {
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM users WHERE id = :id");
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->id = $user['id'];
                $this->username = $user['username'];
                $this->email = $user['email'];
                $this->role = $user['role'];
                return true;
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error loading user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Authenticate user
     *
     * @param string $username Username
     * @param string $password Password
     * @return bool True if authentication was successful, false otherwise
     */
    public function login($username, $password)
    {
        try {
            $stmt = $this->dbh->prepare("SELECT * FROM users WHERE username = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {
                // Verify password
                if (password_verify($password, $user['password'])) {
                    // Set user properties
                    $this->id = $user['id'];
                    $this->username = $user['username'];
                    $this->email = $user['email'];
                    $this->role = $user['role'];
                    $this->isLoggedIn = true;

                    // Store user data in session
                    $_SESSION['user_id'] = $this->id;
                    $_SESSION['username'] = $this->username;
                    $_SESSION['email'] = $this->email;
                    $_SESSION['role'] = $this->role;

                    // Regenerate session ID to prevent session fixation
                    session_regenerate_id(true);

                    // Update last login timestamp
                    $this->updateLastLogin();

                    return true;
                }
            }

            return false;
        } catch (PDOException $e) {
            error_log("Error during login: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Update user's last login timestamp
     */
    private function updateLastLogin()
    {
        try {
            $stmt = $this->dbh->prepare("UPDATE users SET last_login = NOW() WHERE id = :id");
            $stmt->bindParam(':id', $this->id, PDO::PARAM_INT);
            $stmt->execute();
        } catch (PDOException $e) {
            error_log("Error updating last login: " . $e->getMessage());
        }
    }

    /**
     * Log out user
     */
    public function logout()
    {
        // Unset all session variables
        $_SESSION = array();

        // Destroy the session
        session_destroy();

        // Reset user properties
        $this->id = null;
        $this->username = null;
        $this->email = null;
        $this->role = null;
        $this->isLoggedIn = false;
    }

    /**
     * Create a new user
     *
     * @param string $username Username
     * @param string $password Password
     * @param string $email Email
     * @param string $role Role (default: 'user')
     * @return int|bool User ID if successful, false otherwise
     */
    public function createUser($username, $password, $email, $role = 'user')
    {
        try {
            // Check if username or email already exists
            $stmt = $this->dbh->prepare("SELECT * FROM users WHERE username = :username OR email = :email");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->fetch(PDO::FETCH_ASSOC)) {
                return false; // Username or email already exists
            }

            // Hash password
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

            // Insert new user
            $stmt = $this->dbh->prepare("INSERT INTO users (username, password, email, role, created_at) VALUES (:username, :password, :email, :role, NOW())");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
            $stmt->bindParam(':email', $email, PDO::PARAM_STR);
            $stmt->bindParam(':role', $role, PDO::PARAM_STR);
            $stmt->execute();

            return $this->dbh->lastInsertId();
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user is logged in
     *
     * @return bool True if user is logged in, false otherwise
     */
    public function isLoggedIn()
    {
        return $this->isLoggedIn;
    }

    /**
     * Check if user has a specific role
     *
     * @param string|array $roles Role or array of roles
     * @return bool True if user has the role, false otherwise
     */
    public function hasRole($roles)
    {
        if (!$this->isLoggedIn) {
            return false;
        }

        if (is_array($roles)) {
            return in_array($this->role, $roles);
        }

        return $this->role === $roles;
    }

    /**
     * Get user ID
     *
     * @return int|null User ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get username
     *
     * @return string|null Username
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Get email
     *
     * @return string|null Email
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get role
     *
     * @return string|null Role
     */
    public function getRole()
    {
        return $this->role;
    }
}