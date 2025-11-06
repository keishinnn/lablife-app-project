<?php

namespace Models\User;

use Core\App;
use DateTime;
use PDOException;

class UserBlock
{
    public string $blockedUserId;

    public function __construct($blockedUserId)
    {
        $this->blockedUserId = $blockedUserId ?? null;
    }

    public static function blockOtherUser(string $blockerId, string $blockedUserId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $db->query(
                "INSERT INTO user_blocks (blocker_id, blocked_user_id, is_active, created_at, updated_at)
             VALUES (:blocker_id, :blocked_user_id, TRUE, NOW(), NOW())
             ON CONFLICT (blocker_id, blocked_user_id)
             DO UPDATE SET
                 is_active = TRUE,
                 updated_at = NOW();",
                [
                    "blocker_id" => $blockerId,
                    "blocked_user_id" => $blockedUserId
                ]
            );

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function unblockOtherUser(string $userId, string $otherUserId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $db->query(
                "UPDATE user_blocks
             SET is_active = FALSE, updated_at = NOW()
             WHERE blocker_id = :blocker_id
               AND blocked_user_id = :blocked_user_id
               AND is_active = TRUE",
                [
                    "blocker_id" => $userId,
                    "blocked_user_id" => $otherUserId
                ]
            );

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getAllBlockedUsers(string $userId): array
    {
        $db = App::resolve('Core\Database');

        try {
            $result = $db->query(
                "SELECT blocked_user_id 
                 FROM user_blocks 
                 WHERE blocker_id = :blocker_id 
                   AND is_active = TRUE",
                [
                    "blocker_id" => $userId
                ]
            );

            $rows = $result->fetchAll();

            $blockedUsers = [];
            foreach ($rows as $row) {
                $blockedUsers[] = new UserBlock($row['blocked_user_id']);
            }

            return $blockedUsers;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
