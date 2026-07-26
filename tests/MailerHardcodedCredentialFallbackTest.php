<?php

use PHPUnit\Framework\TestCase;

/**
 * Regression test: guard against hardcoded credential fallbacks like
 * `getenv('MAIL_PASSWORD') ?: 'literal-secret'` being reintroduced into the
 * mailer scripts / shared Mailer helper.
 *
 * This exact regression happened once already (see
 * docs/security_improvements.md / repo history): phpmailer.php and
 * phpmailer_2.php regressed to hardcode the real SMTP password as a
 * getenv() fallback, and a separate dead file (assets/config/mailer_config.php,
 * since removed) hardcoded a real SMTP username/host as a fallback too.
 */
class MailerHardcodedCredentialFallbackTest extends TestCase
{
    private const ROOT = __DIR__ . '/..';

    /** Files that configure/send mail and must never hardcode credentials. */
    private const MAILER_FILES = [
        'phpmailer.php',
        'phpmailer_2.php',
        'confirmation_mail.php',
        'rejected_mail.php',
        'medlem.php',
        'assets/class/Mailer.php',
        'assets/class/CapCaptcha.php',
        'assets/class/Database.php',
    ];

    /**
     * Matches `getenv('X_PASSWORD'|'X_SECRET'|'X_KEY'|...) ?: '<non-empty literal>'`
     * i.e. a credential-shaped env var name with a non-empty string fallback.
     */
    private const HARDCODED_CREDENTIAL_PATTERN =
    '/getenv\(\s*[\'"][A-Z0-9_]*(PASSWORD|SECRET|API_KEY|PASSWD|PASS)[A-Z0-9_]*[\'"]\s*\)\s*\?\:\s*[\'"][^\'"]+[\'"]/';

    public function testNoHardcodedCredentialFallbacksInMailerFiles(): void
    {
        $violations = [];

        foreach (self::MAILER_FILES as $relativePath) {
            $path = self::ROOT . '/' . $relativePath;
            if (!file_exists($path)) {
                continue;
            }

            $content = file_get_contents($path);
            if (preg_match_all(self::HARDCODED_CREDENTIAL_PATTERN, $content, $matches)) {
                foreach ($matches[0] as $match) {
                    $violations[] = $relativePath . ': ' . trim($match);
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Hardcoded credential fallback(s) found (secrets must never have a literal getenv() fallback):\n" . implode("\n", $violations)
        );
    }
}
