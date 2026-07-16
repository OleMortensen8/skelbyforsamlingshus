<?php
require_once 'bootstrap.php';

use App\CapCaptcha;

$name = htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8');
$lastname = htmlspecialchars($_POST['lastname'] ?? '', ENT_QUOTES, 'UTF-8');
$email = filter_var($_POST['mail'] ?? '', FILTER_SANITIZE_EMAIL);
$tel = htmlspecialchars($_POST['tlf'] ?? '', ENT_QUOTES, 'UTF-8');
$adresse = htmlspecialchars($_POST['adresse'] ?? '', ENT_QUOTES, 'UTF-8');
$postalCode = htmlspecialchars($_POST['post'] ?? '', ENT_QUOTES, 'UTF-8');
$town = htmlspecialchars($_POST['town'] ?? '', ENT_QUOTES, 'UTF-8');

$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required.';
}

if (empty($lastname)) {
    $errors[] = 'Last name is required.';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email is required.';
}

if (empty($tel)) {
    $errors[] = 'Telephone number is required.';
}

if (empty($adresse)) {
    $errors[] = 'Address is required.';
}

if (empty($postalCode)) {
    $errors[] = 'Postal code is required.';
}

if (empty($town)) {
    $errors[] = 'Town is required.';
}

$capCaptcha = new CapCaptcha();
$capResult = $capCaptcha->verify($_POST['cap-token'] ?? '');
if (!$capResult['success']) {
    $errors[] = $capResult['message'];
}

if (!empty($errors)) {
    foreach ($errors as $error) {
        echo '<p>' . htmlspecialchars($error) . '</p>';
    }
    exit();
}

$fullName = $name . ' ' . $lastname;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer;
$mail->isSMTP();
$mail->Host = $_ENV['MAIL_HOST'];
$mail->SMTPAuth = true;
$mail->Username = $_ENV['MAIL_USERNAME'];
$mail->Password = $_ENV['MAIL_PASSWORD'];
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = (int)$_ENV['MAIL_PORT'];
$mail->CharSet = 'UTF-8';
$mail->isHTML(true);
$mail->setFrom($_ENV['MAIL_FROM'], $_ENV['MAIL_FROM_NAME']);
$mail->addAddress($_ENV['ADMIN_EMAIL'], 'Bestyrelsesformand');
$mail->addBCC('mette@fiskebaek.com');
$mail->addBCC('olevsmortensen@dbc5radio.dk');
$mail->Subject = "Oplysninger til Medlemsskab";
$mail->Body = '';
$mail->Body .= '<br/>Nye medlems Navn: ' . $fullName;
$mail->Body .= '<br/>Nye medlems Adresse: ' . $adresse . ', ';
$mail->Body .= $postalCode . ', ' . $town;
$mail->Body .= '<br/>Nye medlems Telefon: ' . $tel;
$mail->Body .= '<br/>Nye medlems Mail: ' . $email;

if (!$mail->send()) {
    error_log('Mail Error: ' . $mail->ErrorInfo);
    header("Location: blivMedlem.php?status=error");
    exit();
}

header("Location: blivMedlem.php?status=success");
exit();
