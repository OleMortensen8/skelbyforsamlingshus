<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

try {
    $mail2 = new PHPMailer(true);
    $mail2->isSMTP();
    $mail2->Host = getenv('MAIL_HOST') ?: 'websmtp.simply.com';
    $mail2->SMTPAuth = true;
    $mail2->Username = getenv('MAIL_USERNAME') ?: 'ue334094@skelby-forsamlingshus.dk';
    $mail2->Password = getenv('MAIL_PASSWORD') ?: '***REMOVED-LEAKED-SMTP-PASSWORD***';
    $mail2->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail2->Port = (int)(getenv('MAIL_PORT') ?: '587');
    $mail2->CharSet = 'UTF-8';
    $mail2->isHTML(true);
    $mail2->setFrom(getenv('MAIL_FROM') ?: 'ue334094@skelby-forsamlingshus.dk', 'Forsamlingshuset');
    $mail2->addReplyTo(getenv('MAIL_REPLY_TO') ?: 'kasserer@skelby-forsamlingshus.dk', 'Kasserer');

    if (!empty($email)) {
        $mail2->addAddress(htmlspecialchars($email), htmlspecialchars($name ?? 'Guest'));
    }
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

    if (!$mail2->send()) {
        error_log('PHPMailer 2 Error: ' . $mail2->ErrorInfo);
    }
} catch (Exception $e) {
    error_log('PHPMailer 2 Exception: ' . $e->getMessage());
}
