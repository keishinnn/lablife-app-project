<?php

namespace Services;

class TurnstileService
{
    protected string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function validate(string $response, ?string $remoteIp = null): bool
    {
        if ($response === '' || $this->secretKey === '') {
            return false;
        }

        $ch = curl_init('https://challenges.cloudflare.com/turnstile/v0/siteverify');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded',
        ]);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
            'secret'   => $this->secretKey,
            'response' => $response,
            'remoteip' => $remoteIp,
        ]));

        $verifyResponse = curl_exec($ch);

        if ($verifyResponse === false) {
            app_log('Turnstile validation request failed: ' . curl_error($ch));
            curl_close($ch);
            return false;
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode >= 400) {
            app_log("Turnstile validation failed with HTTP {$httpCode}");
            return false;
        }

        $result = json_decode($verifyResponse, true);

        return isset($result['success']) && $result['success'] === true;
    }
}
