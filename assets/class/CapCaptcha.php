<?php

namespace App;

/**
 * Server-side verification for Cap (https://trycap.dev), a self-hosted,
 * proof-of-work based CAPTCHA. This class calls the `/siteverify` endpoint
 * of a Cap Standalone instance to validate tokens produced by the
 * `cap-widget` client-side widget.
 *
 * Configuration is read from environment variables (never hardcoded):
 *  - CAP_API_ENDPOINT: full widget endpoint, e.g. http://host:3000/<site-key>/
 *  - CAP_SITE_KEY / CAP_SERVER_URL: used to build CAP_API_ENDPOINT if it is not set directly
 *  - CAP_SECRET_KEY: the secret key for the site key, from the Cap dashboard
 */
class CapCaptcha
{
    private string $apiEndpoint;
    private string $secretKey;
    private int $timeout;

    public function __construct(?string $apiEndpoint = null, ?string $secretKey = null, int $timeout = 8)
    {
        $this->apiEndpoint = rtrim($apiEndpoint ?? $this->resolveApiEndpoint(), '/');
        $this->secretKey = $secretKey ?? (getenv('CAP_SECRET_KEY') ?: '');
        $this->timeout = $timeout;
    }

    private function resolveApiEndpoint(): string
    {
        $endpoint = getenv('CAP_API_ENDPOINT');
        if ($endpoint) {
            return $endpoint;
        }

        $serverUrl = getenv('CAP_SERVER_URL');
        $siteKey = getenv('CAP_SITE_KEY');
        if ($serverUrl && $siteKey) {
            return rtrim($serverUrl, '/') . '/' . $siteKey . '/';
        }

        return '';
    }

    /**
     * Public widget endpoint, safe to render in HTML (not a secret).
     */
    public function getApiEndpoint(): string
    {
        return $this->apiEndpoint;
    }

    public function isConfigured(): bool
    {
        return $this->apiEndpoint !== '' && $this->secretKey !== '';
    }

    /**
     * Verify a Cap token server-side. This must be called on every
     * protected form submission; the widget's client-side check alone
     * is not sufficient.
     *
     * @param string|null $token The `cap-token` value submitted by the client.
     * @return array{success: bool, message: string}
     */
    public function verify(?string $token): array
    {
        $token = trim((string) $token);

        if ($token === '') {
            return [
                'success' => false,
                'message' => 'Manglende CAPTCHA-verifikation. Genindlæs venligst siden og prøv igen.',
            ];
        }

        if (!$this->isConfigured()) {
            error_log('CapCaptcha: CAP_API_ENDPOINT/CAP_SERVER_URL/CAP_SITE_KEY or CAP_SECRET_KEY is not configured.');
            return [
                'success' => false,
                'message' => 'CAPTCHA-verifikation er ikke konfigureret korrekt. Kontakt venligst administratoren.',
            ];
        }

        $url = $this->apiEndpoint . '/siteverify';
        $payload = json_encode([
            'secret' => $this->secretKey,
            'response' => $token,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => $this->timeout,
            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_FAILONERROR => false,
        ]);

        $response = curl_exec($ch);
        $curlErrno = curl_errno($ch);
        $curlError = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($response === false || $curlErrno !== 0) {
            error_log('CapCaptcha: network error contacting Cap server (' . $this->apiEndpoint . '): ' . $curlError);
            return [
                'success' => false,
                'message' => 'Kunne ikke kontakte CAPTCHA-serveren. Prøv venligst igen om lidt.',
            ];
        }

        $decoded = json_decode((string) $response, true);

        if (!is_array($decoded)) {
            error_log('CapCaptcha: invalid JSON response from Cap server (HTTP ' . $httpCode . '): ' . substr((string) $response, 0, 500));
            return [
                'success' => false,
                'message' => 'Uventet svar fra CAPTCHA-serveren. Prøv venligst igen.',
            ];
        }

        if (empty($decoded['success'])) {
            $error = $decoded['error'] ?? 'invalid_or_expired_token';
            error_log('CapCaptcha: token verification failed (HTTP ' . $httpCode . '): ' . $error);
            return [
                'success' => false,
                'message' => 'CAPTCHA-verifikationen fejlede. Genindlæs venligst siden og prøv igen.',
            ];
        }

        return ['success' => true, 'message' => 'ok'];
    }
}
