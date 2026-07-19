<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer;
    $mail->isSMTP();
    $mail->Host = getenv('MAIL_HOST') ?: 'websmtp.simply.com';
    $mail->SMTPAuth = true;
    $mail->Username = getenv('MAIL_USERNAME') ?: 'ue334094@skelby-forsamlingshus.dk';
    $mail->Password = getenv('MAIL_PASSWORD') ?: '***REMOVED-LEAKED-SMTP-PASSWORD***';
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = (int)(getenv('MAIL_PORT') ?: '587');
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->setFrom(getenv('MAIL_FROM') ?: 'ue334094@skelby-forsamlingshus.dk', getenv('MAIL_FROM_NAME') ?: 'Skelby Forsamlinghus');
    $mail->addReplyTo(getenv('MAIL_REPLY_TO') ?: 'kasserer@skelby-forsamlingshus.dk', 'Kasserer');
    $mail->addAddress(getenv('ADMIN_EMAIL'), 'Administrator');

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

    if (!$mail->send()) {
        error_log('PHPMailer Error: ' . $mail->ErrorInfo);
    }
} catch (\Throwable $e) {
    error_log('PHPMailer Exception: ' . $e->getMessage());
}
