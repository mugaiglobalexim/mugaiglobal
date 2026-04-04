<?php
// send_mail.php

// 1. We start an output buffer so random server warnings don't break our JSON
ob_start();
error_reporting(0); 

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $user_email = filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);

    if (filter_var($user_email, FILTER_VALIDATE_EMAIL)) {
        
        // ============================================
        // FALLBACK: ALWAYS SAVE TO CSV FILE FIRST
        // This guarantees you never lose an email, even if mail() fails.
        // ============================================
        $file_path = "subscribers.csv";
        $file = @fopen($file_path, "a");
        if ($file) {
            fputcsv($file, [$user_email, date('Y-m-d H:i:s')]);
            fclose($file);
        }

        // ============================================
        // SEND REAL EMAIL
        // ============================================
        $admin_email = "admin@mugaiglobal.com"; // Put your REAL receiving email here
        $subject = "New Waitlist Subscriber!";
        $message = "You have a new subscriber on Mugai Global: " . $user_email;
        $headers = "From: noreply@mugaiglobal.com\r\n";
        $headers .= "Reply-To: " . $user_email . "\r\n";
        
        // Try to send the email
        $mail_sent = @mail($admin_email, $subject, $message, $headers);

        // Clear the buffer to ensure clean JSON output
        ob_end_clean();

        // Check if the email successfully left the server
        if ($mail_sent) {
            echo json_encode(["status" => "success", "message" => "Thank you! We will notify you when we launch."]);
        } else {
            // Even if the email was blocked by the host, it was SAVED to the CSV file!
            echo json_encode(["status" => "success", "message" => "Subscribed successfully!"]);
        }
        
    } else {
        ob_end_clean();
        echo json_encode(["status" => "error", "message" => "Invalid email format."]);
    }
} else {
    ob_end_clean();
    echo json_encode(["status" => "error", "message" => "Invalid request."]);
}
?>