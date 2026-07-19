
    $logMessages[] = "DB Connection failed: " . $e->getMessage();
    echo "<p style='color:red;'>DB Connection failed: " . $e->getMessage() . "</p>";
    exit;
}
