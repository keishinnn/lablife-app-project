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
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $serverClient = $this->getClientConnection();

            $streamToken = $serverClient->createToken($userId);

            $serverClient->upsertUser([
                'id' => $userId,
                'name' => $userName,
                'image' => $userImage
            ]);

            return [
                'token' => $streamToken,
                'userId' => $userId,
                'userName' => $userName,
                'userImage' => $userImage
            ];
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    /**
     * Generate a Stream Video token with proper video capabilities
     * This uses JWT encoding with video-specific claims
     */
    public function getStreamVideoToken($userId, $userName, $userImage = null)
    {
        if (!$userId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $payload = [
                'user_id' => $userId,
                'iat' => time(),
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
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
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
        try {
            $serverClient = new StreamChatClient($this->streamApiKey, $this->streamApiSecret);

            return $serverClient;
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }

    public function upsertUser($userId, $userName, $userImage)
    {
        if (!$userId) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Unauthorized']);
            exit;
        }

        try {
            $serverClient = $this->getClientConnection();

            $serverClient->upsertUser([
                'id' => $userId,
                'name' => $userName,
                'image' => $userImage ?? null
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            header('Content-Type: application/json');
            echo json_encode(['error' => $e->getMessage()]);
            exit;
        }
    }
}
