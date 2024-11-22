<?php
// Database connection details
$host = "localhost";           // Database server
$dbname = "lavishb2_lbooking"; // Name of the database
$username = "root";            // Database username
$password = "";                // Database password

try {
    // Establish a connection using PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected successfully.<br>";

    // Function to fetch unique emails, names, and count of bookings from a specified table
    function fetchEmailsFromTable($pdo, $table) {
        $stmt = $pdo->prepare("
            SELECT email, name, surname, COUNT(*) AS booking_count 
            FROM $table 
            WHERE email IS NOT NULL 
            GROUP BY email, name, surname
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch emails from both `bookings` and `packages` tables
    $emailsFromBookings = fetchEmailsFromTable($pdo, 'bookings');
    $emailsFromPackages = fetchEmailsFromTable($pdo, 'packages');

    // Combine results from both tables
    $allEmails = array_merge($emailsFromBookings, $emailsFromPackages);

    // Create `email_list` table if it doesn't already exist
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS email_list (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255),
            surname VARCHAR(255),
            booking_count INT(11) DEFAULT 0
        )
    ";
    $pdo->exec($createTableQuery);

    // Prepare statement to insert or update data in `email_list`
    $insertOrUpdateEmail = $pdo->prepare("
        INSERT INTO email_list (email, name, surname, booking_count) 
        VALUES (:email, :name, :surname, :booking_count) 
        ON DUPLICATE KEY UPDATE 
        booking_count = booking_count + :booking_count
    ");

    // Insert or update each email in the `email_list` table
    foreach ($allEmails as $row) {
        $insertOrUpdateEmail->execute([
            ':email' => $row['email'],
            ':name' => $row['name'],
            ':surname' => $row['surname'],
            ':booking_count' => $row['booking_count']
        ]);
    }

    echo "Emails, names, and booking counts have been successfully transferred to the email_list table.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
