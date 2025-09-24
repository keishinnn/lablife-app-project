<?php

// file path - root/Core/Supabase.php

namespace Services;

use Core\App;

class SupabaseService
{
    protected $url;
    protected $anonKey;
    protected $service_role;

    public function __construct($config)
    {
        $this->url = $config['url'];
        $this->anonKey = $config['anon_key'];
        $this->service_role = $config['service_role'];
    }

    private function request($endpoint, $method = 'POST', $body = [])
    {
        $ch = curl_init("{$this->url}/auth/v1/{$endpoint}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anonKey}",
            "Authorization: Bearer {$this->anonKey}",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        if (!$response) {
            throw new \Exception("Supabase request failed: " . curl_error($ch));
        }
        return json_decode($response, true);
    }

    public function userExists($email)
    {
        $url = "{$this->url}/auth/v1/admin/users";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->service_role}",
            "Authorization: Bearer {$this->service_role}",
            "Content-Type: application/json",
            "Accept: application/json",
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \Exception(curl_error($ch));
        }

        $data = json_decode($response, true);
        error_log("userExists response: " . print_r($data, true));

        if (empty($data['users'])) {
            return false;
        }

        foreach ($data['users'] as $user) {
            if (isset($user['email']) && strtolower($user['email']) === strtolower($email)) {
                return true; // Email exists
            }
        }

        return false; // Email does not exist
    }

    public function handleSetupProfile()
    {
        \Core\Middleware::auth();
        $user = \Core\Auth::user();

        $fullName  = $_POST['full-name'] ?? '';
        $gender    = $_POST['gender'] ?? '';
        $birthdate = $_POST['birthdate'] ?? '';
        $bio       = $_POST['bio'] ?? '';
        $avatarUrl = null;
        $error     = '';
        $isLoading = true;

        try {
            // handle file upload
            if (isset($_FILES['avatar_input']) && $_FILES['avatar_input']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar_input'];

                // size check
                if ($file['size'] > 5 * 1024 * 1024) {
                    $error = "Avatar must be less than 5MB.";
                    error_log("[UPLOAD] File too large: {$file['size']} bytes for user {$user['id']}");
                    $isLoading = false;
                    view('user/setup.view.php', compact('user', 'error', 'isLoading', 'fullName', 'gender', 'birthdate', 'avatarUrl', 'bio'));
                    return;
                }

                // type check
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($file['type'], $allowed)) {
                    $error = "Invalid file type. Only JPG, PNG, WEBP allowed.";
                    error_log("[UPLOAD] Invalid file type: {$file['type']} for user {$user['id']}");
                    $isLoading = false;
                    view('user/setup.view.php', compact('user', 'error', 'isLoading', 'fullName', 'gender', 'birthdate', 'avatarUrl', 'bio'));
                    return;
                }

                $userId   = $user['id'] ?? uniqid("guest_");
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filePath = "{$userId}_" . time() . ".{$ext}";

                try {
                    $supabase = App::resolve(\Services\SupabaseService::class);

                    error_log("[UPLOAD] Uploading file for user {$userId} to {$filePath}");

                    $result = $supabase->uploadFile(
                        "profile-photos",
                        $filePath,
                        file_get_contents($file['tmp_name']),
                        $file['type']
                    );

                    error_log("[UPLOAD] Supabase upload result: " . print_r($result, true));

                    $avatarUrl = $result['url'];
                } catch (\Exception $e) {
                    $error = "Upload failed: " . $e->getMessage();
                    error_log("[ERROR] Supabase upload failed for user {$userId}: " . $e->getMessage());
                    $isLoading = false;
                    view('user/setup.view.php', compact('user', 'error', 'isLoading', 'fullName', 'gender', 'birthdate', 'avatarUrl', 'bio'));
                    return;
                }
            }

            // save to db
            $db = App::resolve('Core\Database');

            error_log("[DB] Updating profile for user {$user['id']}");

            $db->query(
                "UPDATE users 
             SET full_name = :name, gender = :gender, birthdate = :birthdate, bio = :bio, avatar_url = :avatar 
             WHERE id = :id",
                [
                    "name" => $fullName,
                    "gender" => $gender,
                    "birthdate" => $birthdate,
                    "bio" => $bio,
                    "avatar" => $avatarUrl,
                    "id" => $user['id']
                ]
            );

            $isLoading = false;
            header("Location: /u");
            exit;
        } catch (\Exception $e) {
            $error = "Something went wrong: " . $e->getMessage();
            error_log("[ERROR] Exception in handleSetupProfile: " . $e->getMessage());
            $isLoading = false;
            view('user/setup.view.php', compact('user', 'error', 'isLoading', 'fullName', 'gender', 'birthdate', 'avatarUrl', 'bio'));
        }
    }

    public function uploadFile($bucket, $filePath, $fileContent, $contentType)
    {
        $url = "{$this->url}/storage/v1/object/{$bucket}/{$filePath}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->service_role}",
            "Authorization: Bearer {$this->service_role}",
            "Content-Type: {$contentType}",
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);

        $response = curl_exec($ch);
        if ($response === false) {
            throw new \Exception("Upload failed: " . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            throw new \Exception("Supabase Storage error: {$response}");
        }

        return [
            "path" => $filePath,
            "url"  => "{$this->url}/storage/v1/object/public/{$bucket}/{$filePath}"
        ];
    }


    public function signUp($email, $password)
    {
        $body = [
            "email" => $email,
            "password" => $password,
        ];

        return $this->request("signup", "POST", $body);
    }

    public function signIn($email, $password)
    {
        return $this->request("token?grant_type=password", "POST", [
            "email" => $email,
            "password" => $password
        ]);
    }

    public function getUser($accessToken)
    {
        $ch = curl_init("{$this->url}/auth/v1/user");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anonKey}",
            "Authorization: Bearer {$accessToken}",
        ]);
        $response = curl_exec($ch);
        return json_decode($response, true);
    }
}
