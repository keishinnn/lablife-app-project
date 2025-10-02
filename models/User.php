<?php

namespace Models;

use Core\App;
use DateTime;
use PDOException;

class User
{
    public string $id;
    public string $fullName;
    public string $username;
    public string $email;
    public string $gender;
    public string $birthdate;
    public ?string $bio;
    public ?string $avatarUrl;
    public ?float $locationLat;
    public ?float $locationLng;
    public string $lastActive;
    public bool $isVerified;
    public bool $isOnline;
    public string $createdAt;
    public string $updatedAt;

    public function __construct(array $data)
    {
        $this->id = $data['id'];
        $this->fullName = $data['full_name'];
        $this->username = $data['username'];
        $this->email = $data['email'];
        $this->gender = $data['gender'];
        $this->birthdate = $data['birthdate'];
        $this->bio = $data['bio'] ?? null;
        $this->avatarUrl = $data['avatar_url'] ?? null;
        $this->locationLat = $data['location_lat'] ?? null;
        $this->locationLng = $data['location_lng'] ?? null;
        $this->lastActive = $data['last_active'];
        $this->isVerified = $data['is_verified'];
        $this->isOnline = $data['is_online'];
        $this->createdAt = $data['created_at'];
        $this->updatedAt = $data['updated_at'];
    }

    public static function getCurrentUserProfile(string $id): ?User
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT * FROM users WHERE id = :id LIMIT 1", ['id' => $id]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $row ? new User($row) : null;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function setupProfile(string $id, array $data, ?array $file = null): ?string
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();
        $avatarUrl = $data['avatar_url'] ?? null;

        if ($file && $file['error'] === UPLOAD_ERR_OK) {
            // validate size
            if ($file['size'] > 5 * 1024 * 1024) {
                throw new \Exception("Avatar must be less than 5MB.");
            }

            // validate type
            $allowed = ['image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($file['type'], $allowed)) {
                throw new \Exception("Invalid file type. Only JPG, PNG, WEBP allowed.");
            }

            // build filename
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filePath = "{$id}_" . time() . ".{$ext}";

            // upload to Supabase
            $supabase = App::resolve(\Services\SupabaseService::class);
            $result   = $supabase->uploadFile(
                "profile-photos",
                $filePath,
                file_get_contents($file['tmp_name']),
                $file['type']
            );

            $avatarUrl = $result['url'] ?? null;
        }

        try {
            // update DB
            $db->query(
                "UPDATE users
         SET full_name = :name,
             gender = :gender,
             birthdate = :birthdate,
             bio = :bio,
             avatar_url = :avatar,
             updated_at = NOW()
         WHERE id = :id",
                [
                    "name"      => $data['full_name'] ?? '',
                    "gender"    => $data['gender'] ?? '',
                    "birthdate" => $data['birthdate'] ?? '',
                    "bio"       => $data['bio'] ?? '',
                    "avatar"    => $avatarUrl,
                    "id"        => $id
                ]
            );

            $pdo->commit();

            return $avatarUrl;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function editProfile(string $id, array $data)
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();
        try {
            $db->query(
                "UPDATE users
         SET full_name = :full_name,
             username = :username,
             gender = :gender,
             birthdate = :birthdate,
             bio = :bio,
             updated_at = NOW()
         WHERE id = :id",
                [
                    "full_name" => $data['full_name'] ?? '',
                    "username"  => $data['username'] ?? '',
                    "gender"    => $data['gender'] ?? '',
                    "birthdate" => $data['birthdate'] ?? '',
                    "bio"       => $data['bio'] ?? '',
                    "id"        => $id
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

    public static function updateProfilePicture(string $id, array $file): ?string
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        // Validate size & type
        if ($file['size'] > 5 * 1024 * 1024) {
            throw new \Exception("Avatar must be less than 5MB.");
        }

        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($file['type'], $allowed)) {
            throw new \Exception("Invalid file type. Only JPG, PNG, WEBP allowed.");
        }

        $db = App::resolve('Core\Database');

        // Get old avatar URL
        $oldAvatarUrl = $db->query("SELECT avatar_url FROM users WHERE id = :id", [
            "id" => $id
        ])->fetchColumn();

        // Upload new file
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $filePath = "{$id}_" . time() . ".{$ext}";

        $supabase = App::resolve(\Services\SupabaseService::class);
        $result = $supabase->uploadFile(
            "profile-photos",
            $filePath,
            file_get_contents($file['tmp_name']),
            $file['type']
        );

        $avatarUrl = $result['url'] ?? null;

        if ($avatarUrl) {
            if ($oldAvatarUrl && !str_contains($oldAvatarUrl, "default-avatar.png")) {
                try {
                    // Extract file path from the URL
                    $parsed = parse_url($oldAvatarUrl, PHP_URL_PATH);
                    // Remove the /storage/v1/object/public/profile-photos/ part
                    $relativePath = str_replace("/storage/v1/object/public/profile-photos/", "", $parsed);
                    $supabase->deleteFile("profile-photos", $relativePath);
                } catch (\Exception $e) {
                    // log if deletion fails
                    error_log("Failed to delete old avatar: " . $e->getMessage());
                }
            }

            // Update DB with new URL
            $db->query(
                "UPDATE users
             SET avatar_url = :avatar,
                 updated_at = NOW()
             WHERE id = :id",
                [
                    "avatar" => $avatarUrl,
                    "id" => $id
                ]
            );
        }

        return $avatarUrl;
    }


    public static function updateIsOnline(string $id): void
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();

        try {
            $db->query("UPDATE users SET is_online = TRUE WHERE id = :id", [
                'id' => $id
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

    public static function updateIsOffline(string $id): void
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        $pdo->beginTransaction();
        try {
            $db->query("UPDATE users SET is_online = FALSE WHERE id = :id", [
                'id' => $id
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
