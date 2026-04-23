<?php

namespace Models\User;

use Core\App;
use DateTime;
use PDOException;

class User
{
    private const PROFILE_CACHE_KEY_PREFIX = 'profile:bundle:';
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

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

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'full_name' => $this->fullName,
            'username' => $this->username,
            'email' => $this->email,
            'gender' => $this->gender,
            'birthdate' => $this->birthdate,
            'bio' => $this->bio,
            'avatar_url' => $this->avatarUrl,
            'location_lat' => $this->locationLat,
            'location_lng' => $this->locationLng,
            'last_active' => $this->lastActive,
            'is_verified' => $this->isVerified,
            'is_online' => $this->isOnline,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }

    public static function getProfileBundle(string $userId): array
    {
        $cached = self::getCachedProfileBundle($userId);
        if ($cached !== null) {
            return $cached;
        }

        return self::refreshProfileBundleCache($userId);
    }

    public static function refreshProfileBundleCache(string $userId): array
    {
        $bundle = self::buildProfileBundle($userId);
        self::storeProfileBundle($userId, $bundle);

        return $bundle;
    }

    public static function forgetProfileBundleCache(string $userId): void
    {
        try {
            App::resolve('redis')->del([self::profileCacheKey($userId)]);
        } catch (\Throwable $e) {
            app_log_exception($e, 'Failed to forget profile cache');
        }
    }

    private static function buildProfileBundle(string $userId): array
    {
        $user = self::getCurrentUserProfile($userId);

        return [
            'user' => $user,
            'preferences' => UserPreferences::getCurrentUserPreferences($userId) ?? null,
            'personalityType' => UserPersonality::getCurrentUserPersonality($userId) ?? null,
            'userHobbies' => UserHobbies::getCurrentUserHobbies($userId) ?? null,
            'userInterests' => UserInterests::getCurrentUserInterests($userId) ?? null,
        ];
    }

    private static function getCachedProfileBundle(string $userId): ?array
    {
        try {
            $payload = App::resolve('redis')->get(self::profileCacheKey($userId));
        } catch (\Throwable $e) {
            app_log_exception($e, 'Failed to read profile cache');
            return null;
        }

        if (!is_string($payload) || $payload === '') {
            return null;
        }

        $decoded = json_decode($payload, true);
        if (!is_array($decoded) || empty($decoded['user'])) {
            return null;
        }

        return [
            'user' => new self($decoded['user']),
            'preferences' => !empty($decoded['preferences']) ? $decoded['preferences'] : null,
            'personalityType' => !empty($decoded['personalityType']) ? new UserPersonality($decoded['personalityType']) : null,
            'userHobbies' => !empty($decoded['userHobbies'])
                ? array_map(fn(array $hobby) => new UserHobbies($hobby), $decoded['userHobbies'])
                : null,
            'userInterests' => !empty($decoded['userInterests'])
                ? array_map(fn(array $interest) => new UserInterests($interest), $decoded['userInterests'])
                : null,
        ];
    }

    private static function storeProfileBundle(string $userId, array $bundle): void
    {
        $user = $bundle['user'] ?? null;
        if (!$user instanceof self) {
            return;
        }

        $payload = [
            'user' => $user->toArray(),
            'preferences' => $bundle['preferences'] ?? null,
            'personalityType' => ($bundle['personalityType'] instanceof UserPersonality)
                ? $bundle['personalityType']->toArray()
                : null,
            'userHobbies' => !empty($bundle['userHobbies'])
                ? array_map(fn(UserHobbies $hobby) => $hobby->toArray(), $bundle['userHobbies'])
                : null,
            'userInterests' => !empty($bundle['userInterests'])
                ? array_map(fn(UserInterests $interest) => $interest->toArray(), $bundle['userInterests'])
                : null,
        ];

        try {
            App::resolve('redis')->setex(
                self::profileCacheKey($userId),
                self::profileCacheTtl(),
                json_encode($payload)
            );
        } catch (\Throwable $e) {
            app_log_exception($e, 'Failed to store profile cache');
        }
    }

    private static function profileCacheKey(string $userId): string
    {
        return self::PROFILE_CACHE_KEY_PREFIX . $userId;
    }

    private static function profileCacheTtl(): int
    {
        return max(60, (int) ($_ENV['PROFILE_CACHE_TTL'] ?? 300));
    }

    private static function syncCachedOnlineState(string $userId, bool $isOnline): void
    {
        try {
            $redis = App::resolve('redis');
            $key = self::profileCacheKey($userId);
            $payload = $redis->get($key);

            if (!is_string($payload) || $payload === '') {
                return;
            }

            $decoded = json_decode($payload, true);
            if (!is_array($decoded) || empty($decoded['user']) || !is_array($decoded['user'])) {
                return;
            }

            $decoded['user']['is_online'] = $isOnline;
            $redis->setex($key, self::profileCacheTtl(), json_encode($decoded));
        } catch (\Throwable $e) {
            app_log_exception($e, 'Failed to sync cached online state');
        }
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
            $mimeType = self::detectImageMimeType($file);
            if (!isset(self::ALLOWED_IMAGE_TYPES[$mimeType])) {
                throw new \Exception("Invalid file type. Only JPG, PNG, WEBP, HEIC, and HEIF allowed.");
            }

            // build filename
            $ext = self::ALLOWED_IMAGE_TYPES[$mimeType];
            $filePath = "{$id}_" . time() . ".{$ext}";

            // upload to Supabase
            $supabase = App::resolve(\Services\SupabaseService::class);
            $result   = $supabase->uploadFile(
                "profile-photos",
                $filePath,
                file_get_contents($file['tmp_name']),
                $mimeType
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
            self::refreshProfileBundleCache($id);

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
            self::refreshProfileBundleCache($id);
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

        $mimeType = self::detectImageMimeType($file);
        if (!isset(self::ALLOWED_IMAGE_TYPES[$mimeType])) {
            throw new \Exception("Invalid file type. Only JPG, PNG, WEBP, HEIC, and HEIF allowed.");
        }

        $db = App::resolve('Core\Database');

        // Get old avatar URL
        $oldAvatarUrl = $db->query("SELECT avatar_url FROM users WHERE id = :id", [
            "id" => $id
        ])->fetchColumn();

        // Upload new file
        $ext = self::ALLOWED_IMAGE_TYPES[$mimeType];
        $filePath = "{$id}_" . time() . ".{$ext}";

        $supabase = App::resolve(\Services\SupabaseService::class);
        $result = $supabase->uploadFile(
            "profile-photos",
            $filePath,
            file_get_contents($file['tmp_name']),
            $mimeType
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

            self::refreshProfileBundleCache($id);
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
            self::syncCachedOnlineState($id, true);
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
            self::syncCachedOnlineState($id, false);
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getFullNameAndAvatarUrl($userId)
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT full_name, avatar_url FROM users WHERE id = :id LIMIT 1", ['id' => $userId]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);

            return $row ?? null;
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getIsVerified(string $id): ?bool
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT is_verified FROM users WHERE id = :id LIMIT 1", ['id' => $id]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result === false) {
                return null;
            }

            return (bool)$result['is_verified'];
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }
    public static function setVerified(string $id): bool
    {
        $db = App::resolve('Core\Database');
        $pdo = $db->getConnection();

        try {
            $pdo->beginTransaction();

            $db->query(
                "UPDATE users SET is_verified = TRUE, updated_at = NOW() WHERE id = :id",
                ['id' => $id]
            );

            $pdo->commit();
            self::refreshProfileBundleCache($id);
            return true;
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function getAllActiveUsersCount(): int
    {
        $db = App::resolve('Core\Database');

        try {
            $stmt = $db->query("SELECT COUNT(*) AS active_count FROM users WHERE is_online = TRUE");
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            return (int)($result['active_count'] ?? 0);
        } catch (PDOException $e) {
            throw new \Exception("Database error: " . $e->getMessage());
        } catch (\Exception $e) {
            throw $e;
        }
    }

    private static function detectImageMimeType(array $file): string
    {
        $tmpPath = $file['tmp_name'] ?? '';
        $originalName = $file['name'] ?? '';
        $mimeType = $tmpPath !== '' ? (mime_content_type($tmpPath) ?: '') : '';

        if ($mimeType === 'application/octet-stream' || $mimeType === '') {
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            return match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'heic' => 'image/heic',
                'heif' => 'image/heif',
                default => $mimeType,
            };
        }

        return $mimeType;
    }
}
