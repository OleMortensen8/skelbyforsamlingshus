<?php

namespace App;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Shared PHPMailer factory/helper.
 *
 * Centralizes the SMTP setup (host/user/password/port/charset/from/reply-to)
 * that used to be duplicated across phpmailer.php, phpmailer_2.php,
 * confirmation_mail.php, rejected_mail.php and medlem.php, so config changes
 * or fixes only need to happen in one place.
 */
class Mailer
{
    /**
     * Build a PHPMailer instance configured from MAIL_* environment
     * variables, ready to have addAddress()/Subject/Body set and send().
     *
     * @throws \RuntimeException if required MAIL_* env vars are missing.
     */
    public static function create(string $defaultFromName = 'Forsamlingshuset'): PHPMailer
    {
        $mailHost = getenv('MAIL_HOST');
        $mailUsername = getenv('MAIL_USERNAME');
        $mailPassword = getenv('MAIL_PASSWORD');
        $mailFrom = getenv('MAIL_FROM');

        if (!$mailHost || !$mailUsername || !$mailPassword || !$mailFrom) {
            throw new \RuntimeException('Missing required mail configuration environment variable(s) (MAIL_HOST/MAIL_USERNAME/MAIL_PASSWORD/MAIL_FROM); email not sent.');
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
        $mail->setFrom($mailFrom, getenv('MAIL_FROM_NAME') ?: $defaultFromName);
        $mail->addReplyTo(getenv('MAIL_REPLY_TO') ?: 'kasserer@skelby-forsamlingshus.dk', 'Kasserer');

        return $mail;
    }

    /**
     * BCC the secretary role mailbox (MAIL_BCC_SECRETARY, default
     * mette@fiskebaek.com), if non-empty.
     */
    public static function addSecretaryBcc(PHPMailer $mail): void
    {
        $bccSecretary = getenv('MAIL_BCC_SECRETARY') ?: 'mette@fiskebaek.com';
        if ($bccSecretary !== '') {
            $mail->addBCC($bccSecretary, 'Sekrætær');
        }
    }

    /**
     * BCC the optional developer address (MAIL_BCC_DEV), only if explicitly set.
     */
    public static function addDevBcc(PHPMailer $mail): void
    {
        $bccDev = getenv('MAIL_BCC_DEV') ?: '';
        if ($bccDev !== '') {
            $mail->addBCC($bccDev);
        }
    }

    /**
     * BCC both the secretary and (if set) the developer address.
     */
    public static function addStandardBcc(PHPMailer $mail): void
    {
        self::addSecretaryBcc($mail);
        self::addDevBcc($mail);
    }
}
