<?php
/**
 * Mailer Configuration
 * 
 * This file contains configuration for the PHPMailer library.
 * It loads SMTP credentials from environment variables.
 */

// Default values
$smtp_config = [
    'host' => getenv('SMTP_HOST') ?: 'websmtp.simply.com',
    'username' => getenv('SMTP_USERNAME') ?: 'ue334094@skelby-forsamlingshus.dk',
    'password' => getenv('SMTP_PASSWORD') ?: '',
    'port' => getenv('SMTP_PORT') ?: 587,
    'from_email' => getenv('SMTP_FROM_EMAIL') ?: 'ue334094@skelby-forsamlingshus.dk',
    'from_name' => getenv('SMTP_FROM_NAME') ?: 'Kontaktformular'
];

/**
 * Configure a PHPMailer instance with SMTP settings
 * 
 * @param PHPMailer\PHPMailer\PHPMailer $mailer The mailer instance to configure
 * @return void
 */
function configureMailer($mailer) {
    global $smtp_config;
    
    $mailer->isSMTP();
    $mailer->Host = $smtp_config['host'];
    $mailer->SMTPAuth = true;
    $mailer->Username = $smtp_config['username'];
    $mailer->Password = $smtp_config['password'];
    $mailer->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
    $mailer->Port = $smtp_config['port'];
    $mailer->CharSet = 'UTF-8';
    $mailer->isHTML(true);
    $mailer->setFrom($smtp_config['from_email'], $smtp_config['from_name']);
}