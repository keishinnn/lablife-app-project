<?php

namespace Models;

use Core\App;
use PDOException;

class UserPersonality
{

    public string $user_id;
    public string $personality_id;
    public string $updatedAt;
    public string $createdAt;

    public function __construct(array $data)
    {
        $this->user_id = $data['user_id'];
        $this->personality_id = $data['personality_id'];
        $this->updatedAt = $data['updated_at'] ?? null;
        $this->createdAt = $data['created_at'] ?? null;
    }

    // GET ALL USER PERSONALITIES
    public static function getAllPersonalityTypes()
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT * FROM personality_types");
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function setUserPersonality(string $userId, string $pTypeId)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $db->query(
                "INSERT INTO user_personality_type (user_id, personality_id, created_at, updated_at) 
         VALUES (:user_id, :personality_id, NOW(), NOW())
         ON CONFLICT (user_id) DO UPDATE SET
            personality_id = EXCLUDED.personality_id,
            updated_at = NOW()",
                [
                    "user_id"        => $userId,
                    "personality_id" => $pTypeId
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

    // GET SPECIFIC USER PERSONALITY
    public static function getCurrentUserPersonality(string $userId)
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query(
                "SELECT pt.id, pt.name 
         FROM user_personality_type up
         JOIN personality_types pt ON up.personality_id = pt.id
         WHERE up.user_id = :user_id
         LIMIT 1",
                ['user_id' => $userId]
            );

            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $row ? $row : null;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
