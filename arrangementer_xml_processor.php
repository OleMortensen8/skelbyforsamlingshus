<?php
date_default_timezone_set('Europe/Copenhagen'); // Set timezone

// Output HTML header when running manually in the browser
echo "<!DOCTYPE html><html lang='en'><head><meta charset='UTF-8'><title>Cronjob Execution</title></head><body>";
echo "<h1>Cronjob Execution Log</h1>";
echo "<p>Starting the cron job at " . date('Y-m-d H:i:s') . "</p>";

$logMessages = []; // To hold browser output logs

// Database Configuration
$host = 'mysql19.unoeuro.com';
$db = 'skelby_forsamlingshus_dk_db';
$user = 'skelby_forsamlingshus_dk';
$pass = 'tba56pkxy3';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
    $logMessages[] = "Connected to database: $db";
} catch (PDOException $e) {
    $logMessages[] = "DB Connection failed: " . $e->getMessage();
    echo "<p style='color:red;'>DB Connection failed: " . $e->getMessage() . "</p>";
    exit;
}

// Load the XML file
$xmlFilePath = __DIR__ . '/arrangementer.xml';
$xmlOriginal = simplexml_load_file($xmlFilePath);

if (!$xmlOriginal) {
    $logMessages[] = "Failed to load XML file: $xmlFilePath";
    echo "<p style='color:red;'>Failed to load XML file: $xmlFilePath</p>";
    exit;
}

$logMessages[] = "XML file successfully loaded: $xmlFilePath";
echo "<p>XML file successfully loaded at path: $xmlFilePath</p>";

// Prepare a new XML structure
$currentDate = new DateTime();
$xmlNew = new SimpleXMLElement("<?xml version=\"1.0\"?><arrangementer>\n</arrangementer>");

$totalEventsProcessed = 0;
$eventsDeleted = 0;
$eventsAdded = 0;
$customersAdded = 0;

// Process each event in the XML
foreach ($xmlOriginal->arrangement as $event) {
    try {
        $title = (string)$event->title; // Event Title (customer name)
        $booking_date_str = (string)$event->date; // Booking Date (event date)

        $logMessages[] = "Processing event: Title='$title', Date='$booking_date_str'";
        $totalEventsProcessed++;

        // Validate and parse event date
        $bookingDate = DateTime::createFromFormat('Y-m-d', $booking_date_str);
        if (!$bookingDate) {
            $logMessages[] = "INVALID DATE: '$title' has an invalid date format: $booking_date_str";
            echo "<p style='color:orange;'>INVALID DATE: Skipping event '$title' (date: $booking_date_str)</p>";
            continue;
        }

        // Handle outdated events
        if ($bookingDate < $currentDate) {
            $deleteStmt = $pdo->prepare("
                DELETE FROM bookings 
                WHERE booking_date = :booking_date 
                AND customer_id = (SELECT customer_id FROM customers WHERE name = :name LIMIT 1)"
            );
            $deleteStmt->execute([
                'booking_date' => $bookingDate->format('Y-m-d'),
                'name' => $title
            ]);

            if ($deleteStmt->rowCount() > 0) {
                $logMessages[] = "Outdated booking deleted: '$title' on $booking_date_str";
                $eventsDeleted++;
            }

            // Check if customer has other bookings
            $checkCustomerStmt = $pdo->prepare("
                SELECT COUNT(*) 
                FROM bookings 
                WHERE customer_id = (SELECT customer_id FROM customers WHERE name = :name LIMIT 1)"
            );
            $checkCustomerStmt->execute(['name' => $title]);

            if ($checkCustomerStmt->fetchColumn() == 0) {
                $deleteCustomerStmt = $pdo->prepare("DELETE FROM customers WHERE name = :name");
                $deleteCustomerStmt->execute(['name' => $title]);
                $logMessages[] = "Customer deleted as they had no other bookings: $title";
            }

            continue;
        }

        // Ensure customer exists
        $customerStmt = $pdo->prepare("SELECT customer_id FROM customers WHERE name = :name");
        $customerStmt->execute(['name' => $title]);
        $customerId = $customerStmt->fetchColumn();

        if (!$customerId) {
            $insertCustomerStmt = $pdo->prepare("INSERT INTO customers (name) VALUES (:name)");
            $insertCustomerStmt->execute(['name' => $title]);
            $customerId = $pdo->lastInsertId();
            $customersAdded++;
            $logMessages[] = "New customer added: $title";
        }

        // Insert booking if it does not exist
        $bookingStmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings 
            WHERE customer_id = :customer_id AND booking_date = :booking_date
        ");
        $bookingStmt->execute([
            'customer_id' => $customerId,
            'booking_date' => $bookingDate->format('Y-m-d'),
        ]);

        if ($bookingStmt->fetchColumn() == 0) {
            $insertBookingStmt = $pdo->prepare("
                INSERT INTO bookings (customer_id, booking_date, approved) 
                VALUES (:customer_id, :booking_date, 1)"
            );
            $insertBookingStmt->execute([
                'customer_id' => $customerId,
                'booking_date' => $bookingDate->format('Y-m-d'),
            ]);
            $eventsAdded++;
            $logMessages[] = "New booking added: Title='$title', Date='$booking_date_str'";
        }
    } catch (Exception $e) {
        $logMessages[] = "Error while processing event '$title': " . $e->getMessage();
        echo "<p style='color:red;'>Error processing event '$title': " . $e->getMessage() . "</p>";
        continue;
    }
}

// Save the new XML file
$newXMLPath = __DIR__ . '/arrangementer.xml';
if ($xmlNew->asXML($newXMLPath)) {
    $logMessages[] = "New XML file successfully saved: $newXMLPath";
    echo "<p>New XML file successfully saved: $newXMLPath</p>";
} else {
    $logMessages[] = "Failed to save new XML file: $newXMLPath";
    echo "<p style='color:red;'>Failed to save new XML file: $newXMLPath</p>";
}

echo "<h2>Summary</h2>";
echo "<ul>";
echo "<li>Total events processed: $totalEventsProcessed</li>";
echo "<li>Events deleted (outdated): $eventsDeleted</li>";
echo "<li>Events added: $eventsAdded</li>";
echo "<li>Customers added: $customersAdded</li>";
echo "</ul>";

echo "<p>Cron job finished at " . date('Y-m-d H:i:s') . "</p>";
echo "</body></html>";
?>