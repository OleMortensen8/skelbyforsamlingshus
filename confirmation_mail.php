<?php

use App\Mailer;

try {
    if (empty($email)) {
        // No customer email address was provided; nothing to send.
        return;
    }

    $mail3 = Mailer::create();
    $mail3->addAddress($email, htmlspecialchars($name ?? 'Guest'));
    Mailer::addDevBcc($mail3);

    $mail3->Subject = "Bekræftelse af Bestilling";
    $mail3->Body = 'Kære ' . htmlspecialchars($name ?? '') .
        '<br/> Vi har bekræftet din booking til ' . htmlspecialchars($dato ?? '') . ' og vil kontakte dig igen 7 dage inden <br/>
din booking dato af en af vores bestyrelsesmedlemmer.<br/>' .
        'M.v.h. fra  Skelby forsamlingshus på Falster';
    $mail3->send();
} catch (\Throwable $e) {
    error_log('confirmation_mail.php Exception: ' . $e->getMessage());
    echo "Message could not be sent.";
}
