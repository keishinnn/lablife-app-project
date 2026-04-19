<?php

namespace Services;

use GetStream\StreamChat\Client as StreamChatClient;

class StreamService
{
    protected $streamApiKey;
    protected $streamApiSecret;

    public function __construct($config)
    {
        $this->streamApiKey = $config['stream_api_key'];
        $this->streamApiSecret = $config['stream_api_secret'];
    }

    public function getStreamToken($userId, $userName, $userImage = null)
    {
        if (!$userId) {
            throw new \RuntimeException('Unauthorized.');
        }

        $serverClient = $this->getClientConnection();

        $streamToken = $serverClient->createToken($userId);

        $syncState = $_SESSION['stream_user_sync'] ?? [];
        $signature = sha1(json_encode([
            'name' => (string) $userName,
            'image' => $userImage,
        ]));
        $lastSync = $syncState[$userId]['synced_at'] ?? 0;
        $lastSignature = $syncState[$userId]['signature'] ?? null;
        $shouldSyncUser = !is_int($lastSync)
            || $lastSignature !== $signature
            || (time() - $lastSync) >= 900;

        if ($shouldSyncUser) {
            $serverClient->upsertUser([
                'id' => $userId,
                'name' => $userName,
                'image' => $userImage
            ]);

            $_SESSION['stream_user_sync'][$userId] = [
                'signature' => $signature,
                'synced_at' => time(),
            ];
        }

        return [
            'token' => $streamToken,
            'userId' => $userId,
            'userName' => $userName,
            'userImage' => $userImage
        ];
    }

    /**
     * Generate a Stream Video token with proper video capabilities
     * This uses JWT encoding with video-specific claims
     */
    public function getStreamVideoToken($userId, $userName, $userImage = null)
    {
        if (!$userId) {
            throw new \RuntimeException('Unauthorized.');
        }

        $payload = [
            'user_id' => $userId,
            'iat' => time() - 10,
            'exp' => time() + 3600,
            'type' => 'video',
            'permissions' => [
                'can_publish' => true,
                'can_subscribe' => true
            ]
        ];

        $token = $this->generateJWT($payload, $this->streamApiSecret);

        return [
            'token' => $token,
            'userId' => $userId,
            'userName' => $userName,
            'userImage' => $userImage
        ];
    }

    /**
     * Generate JWT token manually for Stream Video
     */
    private function generateJWT($payload, $secret)
    {
        $header = [
            'typ' => 'JWT',
            'alg' => 'HS256'
        ];

        $headerEncoded = $this->base64UrlEncode(json_encode($header));
        $payloadEncoded = $this->base64UrlEncode(json_encode($payload));

        $signature = hash_hmac('sha256', "$headerEncoded.$payloadEncoded", $secret, true);
        $signatureEncoded = $this->base64UrlEncode($signature);

        return "$headerEncoded.$payloadEncoded.$signatureEncoded";
    }

    /**
     * Base64 URL encoding (used in JWT)
     */
    private function base64UrlEncode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public function getClientConnection()
    {
        if ($this->streamApiKey === '' || $this->streamApiSecret === '') {
            throw new \RuntimeException('Stream configuration is incomplete.');
        }

        return new StreamChatClient($this->streamApiKey, $this->streamApiSecret);
    }

    public function upsertUser($userId, $userName, $userImage)
    {
        if (!$userId) {
            throw new \RuntimeException('Unauthorized.');
        }

        $serverClient = $this->getClientConnection();

        $serverClient->upsertUser([
            'id' => $userId,
            'name' => $userName,
            'image' => $userImage ?? null
        ]);
    }
}
