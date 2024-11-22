<?php
// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "lavishb2_lavish";

try {
    // Establish a connection using PDO
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    echo "Connected successfully.<br>";

    // Function to fetch unique emails, names, and count of bookings from `new_bookings` table
    function fetchEmailsFromTable($pdo) {
        $stmt = $pdo->prepare("
            SELECT email, name, COUNT(*) AS booking_count 
            FROM new_bookings 
            WHERE email IS NOT NULL 
            GROUP BY email, name
        ");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch emails from `new_bookings` table
    $emailsFromBookings = fetchEmailsFromTable($pdo);

    // Create `email_list` table if it doesn't already exist
    $createTableQuery = "
        CREATE TABLE IF NOT EXISTS email_list (
            id INT(11) PRIMARY KEY AUTO_INCREMENT,
            email VARCHAR(255) UNIQUE NOT NULL,
            name VARCHAR(255),
            booking_count INT(11) DEFAULT 0
        )
    ";
    $pdo->exec($createTableQuery);

    // Prepare statement to insert or update data in `email_list`
    $insertOrUpdateEmail = $pdo->prepare("
        INSERT INTO email_list (email, name, booking_count) 
        VALUES (:email, :name, :booking_count) 
        ON DUPLICATE KEY UPDATE 
        booking_count = booking_count + :booking_count
    ");

    // Insert or update each email in the `email_list` table
    foreach ($emailsFromBookings as $row) {
        $insertOrUpdateEmail->execute([
            ':email' => $row['email'],
            ':name' => $row['name'],
            ':booking_count' => $row['booking_count']
        ]);
    }

    echo "Emails, names, and booking counts have been successfully transferred to the email_list table.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
