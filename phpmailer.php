<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    $mailHost = getenv('MAIL_HOST');
    $mailUsername = getenv('MAIL_USERNAME');
    $mailPassword = getenv('MAIL_PASSWORD');
    $mailFrom = getenv('MAIL_FROM');
    $adminEmail = getenv('ADMIN_EMAIL');

    if (!$mailHost || !$mailUsername || !$mailPassword || !$mailFrom || !$adminEmail) {
        throw new \RuntimeException('Missing required mail configuration environment variable(s) (MAIL_HOST/MAIL_USERNAME/MAIL_PASSWORD/MAIL_FROM/ADMIN_EMAIL); booking notification email not sent.');
    }

    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = $mailHost;
    $mail->SMTPAuth = true;
    $mail->Username = $mailUsername;
    $mail->Password = $mailPassword;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)(getenv('MAIL_PORT') ?: 587);
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->setFrom($mailFrom, getenv('MAIL_FROM_NAME') ?: 'Skelby Forsamlinghus');
    $mail->addReplyTo(getenv('MAIL_REPLY_TO') ?: 'kasserer@skelby-forsamlingshus.dk', 'Kasserer');
    $mail->addAddress($adminEmail, 'Administrator');

    $bccSecretary = getenv('MAIL_BCC_SECRETARY') ?: 'mette@fiskebaek.com';
    if ($bccSecretary !== '') {
        $mail->addBCC($bccSecretary, 'Sekrætær');
    }
    $mail->Subject = "Oplysninger for henvendelse til udlejning";

    $domain = getenv('APP_DOMAIN') ?: 'skelby-forsamlingshus.dk';
    $mail->Body = 'Efterspurgt Booking Dato: ' . htmlspecialchars($pendingDay[0] ?? '');
    $mail->Body .= '<br/>Bookerns Navn: ' . htmlspecialchars($name ?? '');
    $mail->Body .= '<br/>Bookerns Adresse: ' . htmlspecialchars($adresse ?? '') . ', ';
    $mail->Body .= htmlspecialchars($postalCode ?? '') . ' ' . htmlspecialchars($town ?? '');
    $mail->Body .= '<br/>Bookerns Telefon: ' . htmlspecialchars($tel ?? '');
    $mail->Body .= '<br/>Bookerns Mail: ' . htmlspecialchars($email ?? '');
    $mail->Body .= '<br/>Bookerns rumbooking: ' . htmlspecialchars($sal ?? '');

    $ids = implode(',', array_map('intval', $bookingIds ?? []));
    $mail->Body .= '<br/><a href="https://' . htmlspecialchars($domain) . '/udlejning?book&ids=' . htmlspecialchars($ids) . '">Godkend Booking</a>';
    $mail->Body .= '<br/><a href="https://' . htmlspecialchars($domain) . '/udlejning?delete&ids=' . htmlspecialchars($ids) . '">Slet/Annullere Booking</a>';

    $mail->send();
} catch (\Throwable $e) {
    error_log('PHPMailer Exception: ' . $e->getMessage());
}
