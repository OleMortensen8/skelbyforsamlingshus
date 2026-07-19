<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    $mailHost = getenv('MAIL_HOST');
    $mailUsername = getenv('MAIL_USERNAME');
    $mailPassword = getenv('MAIL_PASSWORD');
    $mailFrom = getenv('MAIL_FROM');

    if (!$mailHost || !$mailUsername || !$mailPassword || !$mailFrom) {
        throw new \RuntimeException('Missing required mail configuration environment variable(s) (MAIL_HOST/MAIL_USERNAME/MAIL_PASSWORD/MAIL_FROM); booking confirmation email not sent.');
    }

    if (empty($email)) {
        // No customer email address was provided; nothing to send.
        return;
    }

    $mail2 = new PHPMailer(true);
    $mail2->isSMTP();
    $mail2->Host = $mailHost;
    $mail2->SMTPAuth = true;
    $mail2->Username = $mailUsername;
    $mail2->Password = $mailPassword;
    $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail2->Port = (int)(getenv('MAIL_PORT') ?: 587);
    $mail2->CharSet = 'UTF-8';
    $mail2->isHTML(true);
    $mail2->setFrom($mailFrom, 'Forsamlingshuset');
    $mail2->addReplyTo(getenv('MAIL_REPLY_TO') ?: 'kasserer@skelby-forsamlingshus.dk', 'Kasserer');
    $mail2->addAddress($email, htmlspecialchars($name ?? 'Guest'));

    $bccDev = getenv('MAIL_BCC_DEV') ?: '';
    if ($bccDev !== '') {
        $mail2->addBCC($bccDev);
    }
    $mail2->Subject = "Oplysninger for henvendelse til udlejning";
    $mail2->Body = 'Efterspurgt BookingDate: ' . htmlspecialchars($pendingDay[0] ?? '') . ' og ' . htmlspecialchars($pendingDay[1] ?? '') . ' dage frem<br/>' .
        'Kære ' . htmlspecialchars($name ?? '') . '<br/><br/>' .
        'Vi takker for din Henvendelse.<br/>' .
        'Vi kontakter dig inden for 5 dage omkring betalingen af huset og andre aftaler I forbindelse med overtagelesen.<br/>' .
        'M.v.h. fra Skelby forsamlingshus på Falster';

    $mail2->send();
} catch (\Throwable $e) {
    error_log('PHPMailer 2 Exception: ' . $e->getMessage());
}
