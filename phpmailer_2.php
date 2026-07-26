<?php

use App\Mailer;

try {
    if (empty($email)) {
        // No customer email address was provided; nothing to send.
        return;
    }

    $mail2 = Mailer::create();
    $mail2->addAddress($email, htmlspecialchars($name ?? 'Guest'));
    Mailer::addDevBcc($mail2);

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
