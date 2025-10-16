<?php

namespace Models\Match;

use Models\User\User;
use Core\App;
use PDO;
use PDOException;

class MatchCandidate extends User
{
    public function __construct(array $data)
    {
        parent::__construct($data);
    }

    public static function findPotentialMatch(string $userId, array $activeUsers = [])
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $stmt = $pdo->prepare("SELECT * FROM find_best_match(:userId, :activeIds)");
        $stmt->execute([
            'userId' => $userId,
            'activeIds' => '{' . implode(',', $activeUsers) . '}',
        ]);

        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

    public static function likeOtherUser(string $userId, string $otherUserId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {

            // Ensuring a user can only like another user if they are part
            // of match session
            $session = MatchSession::checkSession($userId, $otherUserId);

            if (!$session) {
                $pdo->rollBack();
                return [
                    'status' => 'denied',
                    'message' => 'You can only like users within a pending match session.'
                ];
            }

            $stmt = $pdo->prepare("
            INSERT INTO likes (from_user_id, to_user_id, created_at)
            VALUES (:userId, :otherUserId, NOW())
            ");

            $stmt->execute([
                ':userId' => $userId,
                ':otherUserId' => $otherUserId
            ]);

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function dislikeOtherUser(string $userId, string $otherUserId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {

            $stmt = $pdo->prepare("
            INSERT INTO dislikes (from_user_id, to_user_id, created_at)
            VALUES (:userId, :otherUserId, NOW())
            ");

            $stmt->execute([
                ':userId' => $userId,
                ':otherUserId' => $otherUserId
            ]);

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
}
