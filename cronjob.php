<?php
date_default_timezone_set('Europe/Copenhagen');

require_once __DIR__ . '/vendor/autoload.php';

if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

$host = getenv('HOST');
$db = getenv('DATABASE');
$user = getenv('USERNAME');
$pass = getenv('PASSWORD');
$charset = 'utf8mb4';

if (!$host || !$db || !$user) {
    throw new Exception('Database configuration missing in environment variables.');
}

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    exit;
}
error_log("Cron job started at " . date('Y-m-d H:i:s'));

try {

    $xmlFilePath = __DIR__ . '/arrangementer.xml';
    $xmlOriginal = simplexml_load_file($xmlFilePath);
    if (!$xmlOriginal) {
        throw new Exception("Failed to load XML file: $xmlFilePath");
    }
    error_log("XML file successfully loaded: $xmlFilePath");


    $currentDate = new DateTime();
    $xmlNew = new SimpleXMLElement("<?xml version=\"1.0\"?><arrangementer>\n</arrangementer>");


    foreach ($xmlOriginal->arrangement as $event) {
        $title = (string)$event->title;
        $booking_date_str = (string)$event->date;


        error_log("Processing event: Title='$title', Date='$booking_date_str'");


        $bookingDate = DateTime::createFromFormat('Y-m-d', $booking_date_str);
        if (!$bookingDate) {
            error_log(
                "INVALID DATE FORMAT: Skipping event '$title' due to invalid date format '$booking_date_str'"
            );
            continue;
        }


        if ($bookingDate < $currentDate) {
            error_log("Outdated event detected: Title='$title', Date='$booking_date_str'");


            $deleteBookingStmt = $pdo->prepare("
                DELETE FROM skelby_forsamlingshus_dk_db.Bookings
                WHERE booking_date = :booking_date
                AND customer_id = (SELECT customer_id FROM skelby_forsamlingshus_dk_db.Customers WHERE name = :name LIMIT 1)"
            );
            $deleteBookingStmt->execute([
                'booking_date' => $bookingDate->format('Y-m-d'),
                'name' => $title,
            ]);
            if ($deleteBookingStmt->rowCount() > 0) {
                error_log("Deleted outdated booking: Title='$title', Date='$booking_date_str'");
            } else {
                error_log("No booking to delete for Title='$title', Date='$booking_date_str'");
            }


            $checkCustomerStmt = $pdo->prepare("
                SELECT COUNT(*) FROM skelby_forsamlingshus_dk_db.Bookings
                WHERE customer_id = (SELECT customer_id FROM skelby_forsamlingshus_dk_db.Customers WHERE name = :name LIMIT 1)"
            );
            $checkCustomerStmt->execute(['name' => $title]);
            $remainingBookings = $checkCustomerStmt->fetchColumn();

            if ($remainingBookings == 0) {
                $deleteCustomerStmt = $pdo->prepare("DELETE FROM skelby_forsamlingshus_dk_db.Customers WHERE name = :name");
                $deleteCustomerStmt->execute(['name' => $title]);
                if ($deleteCustomerStmt->rowCount() > 0) {
                    error_log("Deleted customer with no remaining bookings: '$title'");
                }
            }


            continue;
        }


        error_log("Adding/updating valid event: Title='$title', Date='$booking_date_str'");


        $customerStmt = $pdo->prepare("SELECT customer_id FROM skelby_forsamlingshus_dk_db.Customers WHERE name = :name");
        $customerStmt->execute(['name' => $title]);
        $customerId = $customerStmt->fetchColumn();

        if (!$customerId) {

            try {
                $insertCustomerStmt = $pdo->prepare("INSERT INTO skelby_forsamlingshus_dk_db.Customers (name) VALUES (:name)");
                $insertCustomerStmt->execute(['name' => $title]);
                $customerId = $pdo->lastInsertId();
                error_log("New customer added: Title='$title'");
            } catch (Exception $e) {
                error_log("Failed to insert customer: '$title'. Error: " . $e->getMessage());
                continue;
            }
        }


        $bookingStmt = $pdo->prepare("
            SELECT COUNT(*) FROM skelby_forsamlingshus_dk_db.Bookings
            WHERE customer_id = :customer_id
            AND booking_date = :booking_date"
        );
        $bookingStmt->execute([
            'customer_id' => $customerId,
            'booking_date' => $bookingDate->format('Y-m-d'),
        ]);

        if ($bookingStmt->fetchColumn() == 0) {

            try {
                $insertBookingStmt = $pdo->prepare("
                    INSERT INTO skelby_forsamlingshus_dk_db.Bookings (customer_id, booking_date, approved)
                    VALUES (:customer_id, :booking_date, 1)"
                );
                $insertBookingStmt->execute([
                    'customer_id' => $customerId,
                    'booking_date' => $bookingDate->format('Y-m-d'),
                ]);
                error_log("New booking added: Title='$title', Date='$booking_date_str'");
            } catch (Exception $e) {
                error_log("Failed to insert booking for Title='$title'. Error: " . $e->getMessage());
            }
        } else {
            error_log("Booking already exists: Title='$title', Date='$booking_date_str'");
        }


        $newEvent = $xmlNew->addChild('arrangement');
        $newEvent->addChild('title', $title);
        $newEvent->addChild('date', $booking_date_str);
        $newEvent->addChild('time', (string)$event->time);
        $newEvent->addChild('location', (string)$event->location);
        $newEvent->addChild('description', (string)$event->description);
    }


    $newXMLPath = __DIR__ . '/arrangementer.xml';
    if ($xmlNew->asXML($newXMLPath)) {
        error_log("New XML file successfully saved: $newXMLPath");
    } else {
        throw new Exception("Failed to save new XML file: $newXMLPath");
    }

    error_log("Cron job completed successfully at " . date('Y-m-d H:i:s'));
} catch (Exception $e) {
    error_log("Cron job encountered an error: " . $e->getMessage());
}