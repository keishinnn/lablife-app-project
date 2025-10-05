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

    public static function getActiveUsersExcept(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("
            SELECT user_id 
            FROM active_match_searches
            WHERE status = 'active' AND user_id != :userId;
        ");

        $stmt->execute(['userId' => $userId]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }
}
