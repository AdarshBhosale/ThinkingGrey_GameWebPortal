<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['signup_btn'])) {
    // Sanitize and validate input
    $name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Validate inputs
    $errors = [];
    if (empty($name))
        $errors[] = "Name is required";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Invalid email format";
    if (strlen($password) < 8)
        $errors[] = "Password must be at least 8 characters";
    if ($password !== $confirm_password)
        $errors[] = "Passwords do not match";

    if (!empty($errors)) {
        foreach ($errors as $error) {
            echo "<div class='error'>$error</div>";
        }
        exit;
    }

    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = SMTP::DEBUG_OFF; // Change to DEBUG_SERVER for debugging
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'adarshb9763ab@gmail.com';
        $mail->Password = 'dgea fwdq irel pzwg';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port = 465;

        // Recipients
        $mail->setFrom('adarshb9763ab@gmail.com', 'Thinking Grey');
        $mail->addAddress($email, $name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Welcome to Thinking Grey!';

        // Build email body
        $mail->Body = sprintf('
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&family=Lato:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    <style>
        body { margin: 0; padding: 0; font-family: "Roboto", sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 30px auto; padding: 40px; background: #fff; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
        .header { text-align: center; padding-bottom: 30px; border-bottom: 2px solid #f0f0f0; margin-bottom: 30px; }
        h1 { color: #2c3e50; font-size: 28px; margin-bottom: 15px; }
        h2 { color: #3498db; font-size: 22px; }
        .credentials { background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 25px 0; }
        .footer { text-align: center; padding-top: 25px; border-top: 2px solid #f0f0f0; color: #95a5a6; font-size: 14px; }
        .footer a { color: #3498db; text-decoration: none; }
        ul { padding-left: 20px; }
        li { margin-bottom: 8px; }
        .button { display: inline-block; padding: 12px 30px; background: #3498db; color: white; text-decoration: none; border-radius: 25px; margin-top: 15px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome, %s!</h1>
            <h2>Thank you for joining Thinking Grey</h2>
        </div>
        
        <div class="content">
            <p>We are excited to have you as part of our community! 🎉</p>
            
            <div class="credentials">
                <strong style="color: #2c3e50; display: block; margin-bottom: 8px;">
                    <i class="fas fa-key"></i> Your Account Details:
                </strong>
                <p style="font-family: monospace; font-size: 16px; color: #e74c3c;">
                    <i class="fas fa-envelope"></i> Email: %s<br>
                    <i class="fas fa-lock"></i> Password: %s
                </p>
            </div>
            
            <a href="mailto:support@thinkinggrey.com" class="button">
                <i class="fas fa-envelope"></i> Contact Support
            </a>
        </div>
        
        <div class="footer">
            <p>© %s Thinking Grey. All rights reserved.</p>
            <p style="font-size: 12px; margin-top: 15px; color: #bdc3c7;">
                This is an automated message. Please do not reply directly to this email.
            </p>
        </div>
    </div>
</body>
</html>',
            htmlspecialchars($name),
            htmlspecialchars($email),
            htmlspecialchars($password),
            date("Y")
        );

        $mail->send();
        header('Location: shop.html');
        exit();
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {
    http_response_code(403);
    echo "Access Forbidden";
}
?>