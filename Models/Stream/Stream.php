<?php

namespace Models\Stream;

use Core\App;
use Models\Match\Matches;
use Models\User\User;

class Stream
{
    public string $token;

    public function __construct(string $token)
    {
        $this->token = $token;
    }

    public static function getStreamUserToken($userId, $fullName, $avatarUrl = null)
    {
        $serverClient = App::resolve('Services\StreamService');

        try {
            $avatarUrl = !empty($avatarUrl) ? $avatarUrl : null;

            $token = $serverClient->getStreamToken($userId, $fullName, $avatarUrl);

            return $token;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function createOrGetChannel(string $userId, string $otherUserId)
    {
        $serverClient = App::resolve('Services\StreamService');

        try {
            // check if matched
            $checkIfMatch = Matches::checkIfMatched($userId, $otherUserId);

            if (!$checkIfMatch) {
                throw new \Exception("Users are not matched. Cannot create chat channel.");
            }

            $sortedIds = [$userId, $otherUserId];
            sort($sortedIds);
            $combinedIds = implode("_", $sortedIds);

            $hash = sha1($combinedIds);
            $shortHash = substr(base_convert(substr($hash, 0, 15), 16, 36), 0, 12);
            $channelId = 'match_' . $shortHash;

            $otherUserData = User::getFullNameAndAvatarUrl($otherUserId);

            if (!$otherUserData) {
                throw new \Exception("Error fetching user data.");
            }

            $client = $serverClient->getClientConnection();

            $channel = $client->channel("messaging", $channelId, [
                'members' => [$userId, $otherUserId]
            ]);

            $serverClient->upsertUser(
                $otherUserId,
                $otherUserData['full_name'],
                $otherUserData['avatar_url']
            );
            try {
                $channel->create($userId);
            } catch (\Exception $e) {
                if (strpos($e->getMessage(), 'already exists') === false) {
                    throw $e;
                }
            }

            return [
                'channelType' => 'messaging',
                'channelId' => $channelId
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function createVideoCall(string $userId, $otherUserId)
    {
        try {
            // check if matched
            $checkIfMatch = Matches::checkIfMatched($userId, $otherUserId);

            if (!$checkIfMatch) {
                throw new \Exception("Users are not matched. Cannot create chat channel.");
            }

            $sortedIds = [$userId, $otherUserId];
            sort($sortedIds);
            $combinedIds = implode("_", $sortedIds);

            $hash = sha1($combinedIds);
            $shortHash = substr(base_convert(substr($hash, 0, 15), 16, 36), 0, 12);
            $callId = 'call_' . time();  // Use timestamp to make it unique per call

            return [
                'callId' => $callId,
                'callType' => 'default'
            ];
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Get Stream Video token (different from chat token)
     */
    public static function getStreamVideoToken(string $userId)
    {
        $serverClient = App::resolve('Services\StreamService');

        try {
            $userData = User::getFullNameAndAvatarUrl($userId);

            if (!$userData) {
                throw new \Exception("Error fetching user data.");
            }

            $token = $serverClient->getStreamVideoToken(
                $userId,
                $userData['full_name'],
                $userData['avatar_url']
            );

            // Return the token data directly (already flat from service)
            return $token;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
