<?php

// file path - root/Core/Supabase.php

namespace Core;

class Supabase
{
    protected $url;
    protected $anonKey;

    public function __construct($config)
    {
        $this->url = $config['url'];
        $this->anonKey = $config['anon_key'];
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
