<?php

namespace Models;

use Core\App;
use PDOException;
use Exception;

class UserHobbies
{
    public ?string $id;
    public ?string $name;

    public function __construct(array $data)
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
    }

    public static function getCurrentUserHobbies(string $userId)
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query(
                "SELECT h.id, h.name 
             FROM user_hobbies uh
             JOIN hobbies h ON uh.hobby_id = h.id
             WHERE uh.user_id = :user_id",
                ['user_id' => $userId]
            );

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $userHobbies = [];
            foreach ($rows as $row) {
                $userHobbies[] = new UserHobbies($row);
            }

            return $userHobbies;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }


    public static function syncUserHobbies(string $userId, array $hobbyIds)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare("DELETE FROM user_hobbies WHERE user_id = :user_id");
            $stmt->execute(["user_id" => $userId]);

            $stmt = $pdo->prepare(
                "INSERT INTO user_hobbies (user_id, hobby_id, created_at, updated_at) 
             VALUES (:user_id, :hobby_id, NOW(), NOW())"
            );

            foreach ($hobbyIds as $hobbyId) {
                $stmt->execute([
                    "user_id"  => $userId,
                    "hobby_id" => $hobbyId
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



    public static function getAllHobbies()
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT * FROM hobbies ORDER BY name ASC");

            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
