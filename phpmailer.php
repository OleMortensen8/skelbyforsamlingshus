<?php

use App\Mailer;

try {
    $adminEmail = getenv('ADMIN_EMAIL');

    if (!$adminEmail) {
        throw new \RuntimeException('Missing required ADMIN_EMAIL environment variable; booking notification email not sent.');
    }

    $mail = Mailer::create('Skelby Forsamlinghus');
    $mail->addAddress($adminEmail, 'Administrator');
    Mailer::addSecretaryBcc($mail);

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
