<?php

namespace Models\User;

use Core\App;
use PDOException;
use Exception;

class UserInterests
{
    public ?string $id;
    public ?string $name;

    public function __construct(?array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
    }

    public static function getCurrentUserInterests(string $userId)
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query(
                "SELECT h.id, h.name 
             FROM user_interests uh
             JOIN interests h ON uh.interest_id = h.id
             WHERE uh.user_id = :user_id",
                ['user_id' => $userId]
            );

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!$rows) {
                return null;
            }

            $userInterests = [];
            foreach ($rows as $row) {
                $userInterests[] = new UserInterests($row);
            }

            return $userInterests;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function syncUserInterests(string $userId, array $interestIds)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("DELETE FROM user_interests WHERE user_id = :user_id");
            $stmt->execute(["user_id" => $userId]);

            $stmt = $pdo->prepare(
                "INSERT INTO user_interests (user_id, interest_id, created_at, updated_at) 
             VALUES (:user_id, :interest_id, NOW(), NOW())"
            );

            foreach ($interestIds as $interestId) {
                $stmt->execute([
                    "user_id"  => $userId,
                    "interest_id" => $interestId
                ]);
            }

            $pdo->commit();
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getAllInterests()
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT * FROM interests ORDER BY name ASC");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!$rows) {
                return null;
            }

            $interests = [];
            foreach ($rows as $row) {
                $interests[] = new UserInterests($row);
            }

            return $interests;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
