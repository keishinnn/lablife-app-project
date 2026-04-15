<?php

// file path - root/Core/Supabase.php

namespace Services;

class SupabaseService
{
    protected $url;
    protected $anonKey;
    protected $service_role;
    protected $accessToken;

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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anonKey}",
            "Authorization: Bearer {$this->anonKey}",
            "Content-Type: application/json",
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));

        $response = curl_exec($ch);
        if (!$response) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Supabase request failed: {$message}");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            return [
                'error' => [
                    'message' => $data['msg'] ?? $data['error_description'] ?? $data['error'] ?? 'Supabase request failed.',
                ],
            ];
        }

        return is_array($data) ? $data : [];
    }

    public function userExists($email)
    {
        $url = "{$this->url}/auth/v1/admin/users";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->service_role}",
            "Authorization: Bearer {$this->service_role}",
            "Content-Type: application/json",
            "Accept: application/json",
        ]);

        $response = curl_exec($ch);
        if ($response === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new \Exception($message);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            throw new \Exception('Unable to query authentication users.');
        }

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

    public function uploadFile($bucket, $filePath, $fileContent, $contentType)
    {
        $url = "{$this->url}/storage/v1/object/{$bucket}/{$filePath}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->service_role}",
            "Authorization: Bearer {$this->service_role}",
            "Content-Type: {$contentType}",
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fileContent);

        $response = curl_exec($ch);
        if ($response === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Upload failed: {$message}");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new \Exception("Supabase Storage error");
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
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->anonKey}",
            "Authorization: Bearer {$accessToken}",
        ]);
        $response = curl_exec($ch);
        if ($response === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Failed to fetch current user: {$message}");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($response, true);

        if ($httpCode >= 400) {
            throw new \Exception('Failed to fetch current user.');
        }

        return is_array($data) ? $data : [];
    }

    public function deleteFile($bucket, $filePath)
    {
        $url = "{$this->url}/storage/v1/object/{$bucket}/{$filePath}";

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: {$this->service_role}",
            "Authorization: Bearer {$this->service_role}"
        ]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $response = curl_exec($ch);
        if ($response === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new \Exception("Delete failed: {$message}");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($httpCode >= 400) {
            throw new \Exception("Supabase Storage delete error");
        }

        return true;
    }
}
