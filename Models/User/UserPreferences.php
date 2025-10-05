<?php

namespace Models\User;

use Core\App;
use PDOException;

class UserPreferences
{
    public int $minAge;
    public int $maxAge;
    public int $distance;
    public string $genderPreference; // ["male", "female", "other"]

    public function __construct(?array $data)
    {
        $this->minAge = $data['age_range']['min'] ?? 18;
        $this->maxAge = $data['age_range']['max'] ?? 99;
        $this->distance = $data['distance'] ?? 0;
        $this->genderPreference = $data['gender_preference'] ?? '';
    }

    public static function getCurrentUserPreferences(string $userId): array
    {
        $db = App::resolve('Core\Database');

        try {
            $agePref = $db->query(
                "SELECT min_age, max_age
         FROM user_age_preference
         WHERE user_id = :id",
                ["id" => $userId]
            )->fetch(\PDO::FETCH_ASSOC);

            $genderPref = $db->query(
                "SELECT gender
         FROM user_gender_preference
         WHERE user_id = :id",
                ["id" => $userId]
            )->fetch(\PDO::FETCH_ASSOC);

            $distancePref = $db->query(
                "SELECT max_distance
         FROM user_distance_preference
         WHERE user_id = :id",
                ["id" => $userId]
            )->fetch(\PDO::FETCH_ASSOC);

            return [
                'age_range' => [
                    'min' => $agePref['min_age'] ?? 18,
                    'max' => $agePref['max_age'] ?? 35,
                ],
                'gender_preference' => $genderPref['gender'] ?? 'other',
                'distance' => $distancePref['max_distance'] ?? 50,
            ];
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function savePreferences(string $userId): void
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $db->query(
                "INSERT INTO user_age_preference (user_id, min_age, max_age)
         VALUES (:id, :min_age, :max_age)
         ON CONFLICT (user_id) DO UPDATE
         SET min_age = EXCLUDED.min_age,
             max_age = EXCLUDED.max_age",
                [
                    "id"      => $userId,
                    "min_age" => $this->minAge,
                    "max_age" => $this->maxAge
                ]
            );

            $db->query(
                "INSERT INTO user_gender_preference (user_id, gender)
         VALUES (:id, :gender)
         ON CONFLICT (user_id) DO UPDATE
         SET gender = EXCLUDED.gender",
                [
                    "id"     => $userId,
                    "gender" => $this->genderPreference
                ]
            );

            $db->query(
                "INSERT INTO user_distance_preference (user_id, max_distance)
         VALUES (:id, :max_distance)
         ON CONFLICT (user_id) DO UPDATE
         SET max_distance = EXCLUDED.max_distance",
                [
                    "id"           => $userId,
                    "max_distance" => $this->distance
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
}
