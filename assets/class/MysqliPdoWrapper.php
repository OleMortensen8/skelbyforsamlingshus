<?php
namespace App;
/**
 * MysqliPdoWrapper - A compatibility layer to use mysqli with PDO-style methods
 * This class provides a subset of PDO functionality using mysqli
 */
class MysqliPdoWrapper {
    private $mysqli;
    private $stmt;
    
    /**
     * Constructor
     * @param mysqli $mysqli The mysqli connection
     */
    public function __construct(mysqli $mysqli) {
        $this->mysqli = $mysqli;
    }
    
    /**
     * Prepare a statement for execution
     * @param string $query The SQL query to prepare
     * @return MysqliPdoStatement A statement object
     */
    public function prepare($query) {
        $stmt = $this->mysqli->prepare($query);
        if (!$stmt) {
            throw new Exception("Failed to prepare statement: " . $this->mysqli->error);
        }
        return new MysqliPdoStatement($stmt);
    }
    
    /**
     * Execute a simple query
     * @param string $query The SQL query to execute
     * @return MysqliPdoStatement|false A statement object or false on failure
     */
    public function query($query) {
        $result = $this->mysqli->query($query);
        if (!$result) {
            return false;
        }
        return new MysqliPdoStatement($result, $query, $this->mysqli);
    }
    
    /**
     * Begin a transaction
     * @return bool True on success or false on failure
     */
    public function beginTransaction() {
        return $this->mysqli->begin_transaction();
    }
    
    /**
     * Commit a transaction
     * @return bool True on success or false on failure
     */
    public function commit() {
        return $this->mysqli->commit();
    }
    
    /**
     * Roll back a transaction
     * @return bool True on success or false on failure
     */
    public function rollBack() {
        return $this->mysqli->rollback();
    }
    
    /**
     * Get the ID of the last inserted row
     * @return string The ID of the last inserted row
     */
    public function lastInsertId() {
        return $this->mysqli->insert_id;
    }
}

/**
 * MysqliPdoStatement - A compatibility layer for mysqli_stmt to mimic PDO statements
 */
class MysqliPdoStatement {
    private $stmt;
    private $result;
    private $query;
    private $mysqli;
    private $boundParams = [];
    
    /**
     * Constructor
     * @param mysqli_stmt|mysqli_result $stmt The mysqli statement or result
     * @param string $query Optional SQL query for direct query execution
     * @param mysqli $mysqli Optional mysqli connection for direct query execution
     */
    public function __construct($stmt, $query = null, $mysqli = null) {
        $this->stmt = $stmt;
        $this->query = $query;
        $this->mysqli = $mysqli;
    }
    
    /**
     * Bind a parameter to the specified variable name
     * @param mixed $param Parameter identifier
     * @param mixed &$variable Name of the PHP variable to bind
     * @param int $type Optional data type
     * @return bool True on success or false on failure
     */
    public function bindParam($param, &$variable, $type = null) {
        $this->boundParams[$param] = &$variable;
        return true;
    }
    
    /**
     * Bind a value to a parameter
     * @param mixed $param Parameter identifier
     * @param mixed $value The value to bind
     * @param int $type Optional data type
     * @return bool True on success or false on failure
     */
    public function bindValue($param, $value, $type = null) {
        $this->boundParams[$param] = $value;
        return true;
    }
    
    /**
     * Execute the prepared statement
     * @param array $params Optional array of parameter values
     * @return bool True on success or false on failure
     */
    public function execute($params = null) {
        if ($params !== null) {
            // Use the provided params instead of bound params
            $this->boundParams = $params;
        }
        
        if (!empty($this->boundParams)) {
            // Convert numeric array to references for bind_param
            $types = '';
            $values = [];
            
            foreach ($this->boundParams as $param => $value) {
                // Determine the type
                if (is_int($value)) {
                    $types .= 'i';
                } elseif (is_float($value)) {
                    $types .= 'd';
                } elseif (is_string($value)) {
                    $types .= 's';
                } else {
                    $types .= 'b'; // blob
                }
                
                $values[] = &$this->boundParams[$param];
            }
            
            // Bind parameters
            $bindParams = array_merge([$types], $values);
            call_user_func_array([$this->stmt, 'bind_param'], $bindParams);
        }
        
        // Execute the statement
        $result = $this->stmt->execute();
        if ($result) {
            $this->result = $this->stmt->get_result();
        }
        return $result;
    }
    
    /**
     * Fetch the next row from a result set as an associative array
     * @return array|false The fetched row or false if there are no more rows
     */
    public function fetch($fetch_style = null) {
        if ($this->result instanceof mysqli_result) {
            return $this->result->fetch_assoc();
        }
        return false;
    }
    
    /**
     * Fetch all rows from a result set
     * @return array An array containing all of the result set rows
     */
    public function fetchAll($fetch_style = null) {
        $rows = [];
        if ($this->result instanceof mysqli_result) {
            while ($row = $this->result->fetch_assoc()) {
                $rows[] = $row;
            }
        } elseif ($this->query && $this->mysqli) {
            // For direct query execution
            $result = $this->mysqli->query($this->query);
            if ($result) {
                while ($row = $result->fetch_assoc()) {
                    $rows[] = $row;
                }
                $result->free();
            }
        }
        return $rows;
    }
    
    /**
     * Fetch a single column from the next row of a result set
     * @return mixed The value of a single column or false if there are no more rows
     */
    public function fetchColumn($column_number = 0) {
        if ($this->result instanceof mysqli_result) {
            $row = $this->result->fetch_array(MYSQLI_NUM);
            if ($row && isset($row[$column_number])) {
                return $row[$column_number];
            }
        }
        return false;
    }
    
    /**
     * Close the cursor, enabling the statement to be executed again
     * @return bool True on success or false on failure
     */
    public function closeCursor() {
        if ($this->result instanceof mysqli_result) {
            $this->result->free();
        }
        return true;
    }
    
    /**
     * Get the number of rows affected by the last SQL statement
     * @return int The number of rows
     */
    public function rowCount() {
        if ($this->stmt instanceof mysqli_stmt) {
            return $this->stmt->affected_rows;
        }
        return 0;
    }
}