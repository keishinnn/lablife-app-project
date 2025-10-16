<?php

// file path = root/Models/Match/MatchSession.php

namespace Models\Match;

use Core\App;
use DateTime;
use DateTimeZone;
use PDO;
use PDOException;

class MatchSession
{

    // Models/MatchSession.php
    public static function createSession(string $userA, string $userB, int $durationSeconds = 60)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        try {
            $pdo->beginTransaction();

            // lock any existing pending session for this unordered pair
            $stmt = $pdo->prepare("
            SELECT id FROM match_sessions
            WHERE ((user_a = :a AND user_b = :b) OR (user_a = :b AND user_b = :a))
              AND status = 'pending' AND expires_at > NOW()
            FOR UPDATE
        ");
            $stmt->execute(['a' => $userA, 'b' => $userB]);

            if ($stmt->fetch()) {
                // session already pending — return that session or throw
                $pdo->commit();
                return null; // or return existing session info
            }

            $expiresAt = (new DateTime('now', new DateTimeZone('UTC')))
                ->modify("+{$durationSeconds} seconds")
                ->format('Y-m-d H:i:sP');

            $insert = $pdo->prepare("
            INSERT INTO match_sessions (user_a, user_b, expires_at)
            VALUES (:a, :b, :expires_at)
            RETURNING *
        ");
            $insert->execute([
                'a' => $userA,
                'b' => $userB,
                'expires_at' => $expiresAt
            ]);

            $session = $insert->fetch(PDO::FETCH_ASSOC);
            $pdo->commit();
            return $session;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function checkSession(string $userId, string $otherUserId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        try {
            // 1️⃣ Check if both users are in an active match session
            $checkSessionStmt = $pdo->prepare("
            SELECT id 
            FROM match_sessions
            WHERE 
                status = 'pending'
                AND expires_at > NOW()
                AND (
                    (user_a = :userId AND user_b = :otherUserId)
                    OR (user_b = :userId AND user_a = :otherUserId)
                )
            LIMIT 1
        ");

            $checkSessionStmt->execute([
                ':userId' => $userId,
                ':otherUserId' => $otherUserId
            ]);

            $session = $checkSessionStmt->fetch(PDO::FETCH_ASSOC);

            return $session;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function setMatchSessionExpired(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            UPDATE match_sessions
            SET status = 'expired', expires_at = NOW()
            WHERE status = 'pending'
            AND (user_a = :user_id OR user_b = :user_id)
        ");
            $stmt->execute([':user_id' => $userId]);

            $pdo->commit();

            return $stmt;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function setMatchSessionRejected(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            UPDATE match_sessions
            SET status = 'rejected', expires_at = NOW()
            WHERE status = 'pending'
            AND (user_a = :user_id OR user_b = :user_id)
        ");
            $stmt->execute([':user_id' => $userId]);

            $pdo->commit();

            return $stmt;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function setMatchSessionMatched(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            UPDATE match_sessions
            SET status = 'matched', expires_at = NOW()
            WHERE status = 'pending'
            AND (user_a = :user_id OR user_b = :user_id)
        ");
            $stmt->execute([':user_id' => $userId]);

            $pdo->commit();

            return $stmt;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function deleteSession(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            UPDATE match_sessions
            SET status = 'expired', expires_at = NOW()
            WHERE status = 'pending'
            AND (user_a = :user_id OR user_b = :user_id)
        ");

            $stmt->execute([':user_id' => $userId]);

            $pdo->commit();

            return $stmt;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
