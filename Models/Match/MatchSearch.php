<?php

namespace Models\Match;

use Core\App;
use PDO;
use PDOException;

class MatchSearch
{
    public static function startSearch(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        try {
            // Insert or update existing entry
            $stmt = $pdo->prepare("
                INSERT INTO active_match_searches (user_id, last_active)
                VALUES (:userId, NOW())
                ON CONFLICT (user_id) 
                DO UPDATE SET last_active = NOW(), status = 'active'
                RETURNING *;
            ");

            $stmt->execute(['userId' => $userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public static function stopSearch(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $db->prepare("DELETE FROM active_match_searches WHERE user_id = :user_id AND status IN ('active', 'expired', 'matched')");
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

    public static function setStatusInMatch(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            UPDATE active_match_searches
            SET status = 'in_match', last_active = NOW()
            WHERE user_id = :user_id
              AND status IN ('active', 'expired')
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

    public static function setStatusActive(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            UPDATE active_match_searches
            SET status = 'active', last_active = NOW()
            WHERE user_id = :user_id
              AND status IN ('active', 'expired')
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

    public static function setStatusExpired(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("
            UPDATE active_match_searches
            SET status = 'expired', last_active = NOW()
            WHERE user_id = :user_id
              AND status IN ('active', 'in_match')
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


    public static function deactivateUser(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("
        UPDATE active_match_searches
        SET status = 'expired'
        WHERE user_id = :userId
    ");

        $stmt->execute(['userId' => $userId]);
    }

    public static function getActiveUsersExcept(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("
        SELECT user_id 
        FROM active_match_searches
        WHERE status = 'active'
          AND user_id != :userId
          AND last_active > NOW() - INTERVAL '30 seconds'
        ORDER BY created_at ASC;
    ");

        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
