<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require 'C:/xampp/htdocs/lavish-new/admin/adminhub/email/PHPMailer/src/Exception.php';
require 'C:/xampp/htdocs/lavish-new/admin/adminhub/email/PHPMailer/src/PHPMailer.php';
require 'C:/xampp/htdocs/lavish-new/admin/adminhub/email/PHPMailer/src/SMTP.php';

// Database connection
$conn = new PDO("mysql:host=localhost;dbname=lavishb2_lavish", "root", "");

// Query recipients from email_list table
$query = $conn->query("SELECT name, surname, email FROM email_list");
$recipients = $query->fetchAll(PDO::FETCH_ASSOC);

// Function to log failed emails
function logFailedEmail($name, $surname, $email, $reason, $conn) {
    $stmt = $conn->prepare("INSERT INTO failed_emails (name, surname, email, failure_reason) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $surname, $email, $reason]);
}

$link = 'https://lavishbeautysalon.co.za/appointment';
$mail = new PHPMailer(true);

foreach ($recipients as $recipient) {
    $name = $recipient['name'];
    $surname = $recipient['surname'] ?? ''; // Check if surname is available
    $email = $recipient['email'];

    try {
        // SMTP settings
        $mail->isSMTP();
        $mail->Host = '';
        $mail->SMTPAuth = true;
        $mail->Username = '';
        $mail->Password = '';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = ;

        // Email headers and recipient
        $mail->setFrom('', 'Lavish Beauty Salon');
        $mail->addAddress($email, $name . ($surname ? ' ' . $surname : ''));
        $mail->Subject = 'ATTENTION LAVISH BEAUTIES!!!';

        // Email body with embedded images and buttons
        $mailContent = "
    <p>Hi {$name}" . ($surname ? " {$surname}" : "") . ",</p>
    <div style='text-align: center;'>
        <img src='cid:image1' style='max-width:100%; height:auto;'>
    </div>
    <p style='text-align: center;'>
        <a href='{$link}' style='display:inline-block;padding:10px 20px;background-color:#007bff;color:#ffffff;text-decoration:none;border-radius:5px;'>Book Now</a>
    </p>
    <div style='text-align: center;'>
        <img src='cid:image2' style='max-width:100%; height:auto;'>
    </div>
    <p style='text-align: center;'>
        <a href='{$link}' style='display:inline-block;padding:10px 20px;background-color:#007bff;color:#ffffff;text-decoration:none;border-radius:5px;'>Book Now</a>
    </p>
    <p>Hope to see you soon,<br>Lavish Beauty Salon</p>
";

        $mail->isHTML(true);
        $mail->Body = $mailContent;

        // Attach images and set CID for inline display
        $mail->addEmbeddedImage('lavish/1.png', 'image1');
        $mail->addEmbeddedImage('lavish/2.png', 'image2');

        // Send email
        $mail->send();
        echo "Email sent to {$name} {$surname} ({$email}) successfully.<br>";

    } catch (Exception $e) {
        // Log failed email
        logFailedEmail($name, $surname, $email, $mail->ErrorInfo, $conn);
        echo "Failed to send email to {$name} {$surname} ({$email}): {$mail->ErrorInfo}<br>";
    }

    // Clear addresses and attachments for next loop
    $mail->clearAddresses();
    $mail->clearAttachments();
}
?>
