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

    $mail3->Subject = "Anullering  af Bestilling";
    $mail3->Body = 'Kære ' . htmlspecialchars($name ?? '') .
        '<br/> Vi har bekræftet din booking til ' . htmlspecialchars($dato ?? '') . ' er anulleret af vores repræsentant.<br/>' .
        'M.v.h. fra  Skelby forsamlingshus på Falster';
    $mail3->send();
} catch (\Throwable $e) {
    error_log('rejected_mail.php Exception: ' . $e->getMessage());
    echo "Message could not be sent.";
}
