<?php

namespace Core;

class Turnstile
{
    protected string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function validate(string $response, ?string $remoteIp = null): bool
    {
        $verifyResponse = file_get_contents(
            "https://challenges.cloudflare.com/turnstile/v0/siteverify",
            false,
            stream_context_create([
                'http' => [
                    'method'  => 'POST',
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => http_build_query([
                        'secret'   => $this->secretKey,
                        'response' => $response,
                        'remoteip' => $remoteIp,
                    ]),
                ],
            ])
        );

        $result = json_decode($verifyResponse, true);

        return isset($result['success']) && $result['success'] === true;
    }
}
