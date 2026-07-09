<?php
namespace App;

use PDO;
use Exception;

class Customer extends Database {
    private $name;
    private $email;
    private $customerId;

    public function __construct($name = null, $email = null) {
        parent::__construct();
        if (!$name == null || !$email == null){
            $this->name = $name;
            $this->email = $email;
            $this->createOrFetchCustomer();
        }
    }

    private function createOrFetchCustomer() {
        // Check if the customer already exists in the database.
        $stmt = $this->dbh->prepare('SELECT customer_id FROM Customers WHERE email = ?');
        $stmt->execute([$this->email]);
        $existingCustomerId = $stmt->fetchColumn();

        if ($existingCustomerId) {
            // If customer exists, set the customerId.
            $this->customerId = $existingCustomerId;
        } else {
            // If customer doesn't exist, create them and set the customerId.
            $stmt = $this->dbh->prepare('INSERT INTO Customers (name, email) VALUES (?, ?)');
            $stmt->execute([$this->name, $this->email]);
            $this->customerId = $this->dbh->lastInsertId();
        }
    }
    public function getCustomerId() {
        return $this->customerId;
    }
    public function getCustomerIdFromBooking($bookingId) {
        $sql = 'SELECT customer_id FROM Bookings WHERE booking_id = ?';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$bookingId]);
        $this->customerId = $stmt->fetchColumn();
    }
    public function hasBookings($customerId) {
        $sql = 'SELECT COUNT(*) FROM Bookings WHERE customer_id = ?';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$customerId]);
        $return = $stmt->fetchColumn() < 1 ? false : true;
        return $return;
    }
    public function deleteCustomer($customerId) {
        $sql = 'DELETE FROM Customers WHERE customer_id = ?';
        $stmt = $this->dbh->prepare($sql);
        $stmt->execute([$customerId]);
    }    
}
