<?php

namespace Models\Match;

use Models\User\User;
use Core\App;
use PDO;
use PDOException;

class Matches
{
    public string $user1_id;
    public string $user2_id;

    public function __construct(array $data)
    {
        $this->user1_id = $data['user1_id'];
        $this->user2_id = $data['user2_id'];
    }

    public static function getAllUserMatches(string $userId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT user1_id, user2_id
                FROM matches
                WHERE user1_id = :userId OR user2_id = :userId
                ");
            $stmt->execute(['userId' => $userId]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) {
                return [];
            }

            $matches = [];
            foreach ($rows as $row) {
                $matches[] = new Matches($row);
            }

            return array_map(fn($row) => new Matches($row), $rows);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getMatchedUsers(string $userId): array
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        try {
            $stmt = $pdo->prepare("
                SELECT DISTINCT u.*
                FROM matches m
                INNER JOIN users u
                    ON u.id = CASE
                        WHEN m.user1_id = :userId THEN m.user2_id
                        ELSE m.user1_id
                    END
                WHERE m.user1_id = :userId OR m.user2_id = :userId
            ");
            $stmt->execute(['userId' => $userId]);

            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!$rows) {
                return [];
            }

            return array_map(fn(array $row) => new User($row), $rows);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function checkIfMatched(string $userId, string $otherUserId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        try {
            $stmt = $pdo->prepare("
            SELECT *
            FROM matches
            WHERE is_active = true
              AND (
                  (user1_id = :userId AND user2_id = :otherUserId)
                  OR (user1_id = :otherUserId AND user2_id = :userId)
              )
            LIMIT 1
        ");

            $stmt->execute([
                'userId' => $userId,
                'otherUserId' => $otherUserId
            ]);

            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$row) {
                return null;
            }

            return new Matches($row);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        }
    }

    public static function getAllMatchesCount(): int
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        try {
            $stmt = $pdo->query("
            SELECT COUNT(*) AS total_matches
            FROM matches
            WHERE is_active = true
        ");

            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return (int)($result['total_matches'] ?? 0);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
