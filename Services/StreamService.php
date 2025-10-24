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
