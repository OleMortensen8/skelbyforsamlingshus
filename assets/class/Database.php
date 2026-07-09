<?php 
namespace App;

use PDO;
use PDOException;
use Exception;

class Database
{
    protected ?PDO $dbh;

    public function __construct(array $config = [])
    {
        $host = $config['host'] ?? getenv('HOST');
        $db = $config['database'] ?? getenv('DATABASE');
        $user = $config['username'] ?? getenv('USERNAME');
        $pass = $config['password'] ?? getenv('PASSWORD');

        $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4', $host, $db);
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->dbh = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            error_log("Database connection error: " . $e->getMessage());
            $this->dbh = null;

            $message = (getenv('ENVIRONMENT') === 'development') 
                ? "Database connection failed: " . $e->getMessage()
                : "Database connection failed. Please try again later.";
            
            throw new Exception($message);
        }
    }
}
