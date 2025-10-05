<?php

namespace Models\User;

use Core\App;
use PDOException;

class UserPersonality
{

    public string $id;
    public string $name;

    public function __construct(?array $data)
    {
        $this->id = $data['id'];
        $this->name = $data['name'];
    }

    // GET ALL USER PERSONALITIES
    public static function getAllPersonalityTypes()
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT * FROM personality_types");

            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            if (!$rows) {
                return null;
            }

            $personality_types = [];
            foreach ($rows as $row) {
                $personality_types[] = new UserPersonality($row);
            }

            return $personality_types;
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
    public static function getCurrentUserPersonality(string $userId): ?self
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

            if (!$row) {
                return null;
            }

            return new self($row);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
