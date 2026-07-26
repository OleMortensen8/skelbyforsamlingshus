<?php

namespace App;

use PDO;
use Exception;
use DateTime;
use DateInterval;

class Booking extends Database
{
    private $customer = null;

    public function __construct(Customer $customer = null)
    {
        parent::__construct();
        $this->customer = $customer;
    }

    public function index(): array
    {
        $stmt = $this->dbh->query('SELECT * FROM Bookings');
        return $stmt->fetchAll();
    }

    public function createBooking(array $date, bool $approved = false): string
    {
        try {
            $this->dbh->beginTransaction();

            $values = [];
            $startDate = new DateTime($date[0]);
            $days = (int)$date[1];

            for ($i = 0; $i <= $days; $i++) {
                $currentDate = (clone $startDate)->add(new DateInterval("P{$i}D"));
                $values[] = [
                    'booking_date' => $currentDate->format('Y-m-d'),
                    'approved' => (int)$approved,
                    'customer_id' => $this->customer->getCustomerId()
                ];
            }

            // Perform insertion (letting DB handle AUTO_INCREMENT for booking_id)
            $sql = "INSERT INTO Bookings (booking_date, approved, customer_id) VALUES (:booking_date, :approved, :customer_id)";
            $stmt = $this->dbh->prepare($sql);

            $insertedIds = [];
            foreach ($values as $val) {
                $stmt->execute($val);
                $insertedIds[] = $this->dbh->lastInsertId();
            }

            $this->dbh->commit();

            return json_encode([
                'status' => 'success',
                'message' => 'Booking blev Tilføjet Successfuldt.',
                'bookingIds' => $insertedIds
            ]);
        } catch (Exception $e) {
            if ($this->dbh->inTransaction()) {
                $this->dbh->rollBack();
            }
            return json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function deleteBooking(array $bookingIds)
    {
        try {
            $idPlaceholders = rtrim(str_repeat('?,', count($bookingIds)), ',');
            $sql = "DELETE FROM Bookings WHERE booking_id IN ($idPlaceholders)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($bookingIds);
        } catch (Exception $th) {
            echo $th->getMessage();
        }
    }

    // Fetch the customer_id and booking_date for a set of booking IDs, so
    // callers (e.g. the approve/reject email flow) can look up the customer
    // and dates *before* the rows are updated/deleted.
    public function getBookingsByIds(array $bookingIds): array
    {
        try {
            $idPlaceholders = rtrim(str_repeat('?,', count($bookingIds)), ',');
            $sql = "SELECT booking_id, booking_date, customer_id FROM Bookings WHERE booking_id IN ($idPlaceholders)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($bookingIds);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            return [];
        }
    }

    public function approveBooking(array $bookingIds)
    {
        try {
            $idPlaceholders = rtrim(str_repeat('?,', count($bookingIds)), ',');
            $sql = "UPDATE Bookings SET approved = 1 WHERE booking_id IN ($idPlaceholders)";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute($bookingIds);
        } catch (Exception $th) {
            echo $th->getMessage();
        }
    }

    // Other methods related to a booking can be added here.
}
