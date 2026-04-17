<?php

namespace Services;

class IntelligentService
{
    private string $baseUrl;
    private string $apiKey;
    private int $timeoutSeconds;

    public function __construct(array $config)
    {
        $this->baseUrl = $config['base_url'] ?? '';
        $this->apiKey = $config['api_key'] ?? '';
        $this->timeoutSeconds = max(1, (int) ($config['timeout_seconds'] ?? 30));
    }

    public function health(): array
    {
        return $this->request('/health', 'GET');
    }

    public function verifyUser(string $userId, string $profileUrl, array $frames): array
    {
        $fields = [
            'user_id' => $userId,
            'profile_url' => $profileUrl,
        ];

        foreach ($frames as $index => $frame) {
            if (
                !isset($frame['tmp_name']) ||
                !is_string($frame['tmp_name']) ||
                $frame['tmp_name'] === '' ||
                !is_uploaded_file($frame['tmp_name'])
            ) {
                continue;
            }

            $mimeType = $frame['type'] ?? 'image/jpeg';
            $originalName = $frame['name'] ?? "frame{$index}.jpg";
            $fields["frames[{$index}]"] = new \CURLFile($frame['tmp_name'], $mimeType, $originalName);
        }

        return $this->request('/verify', 'POST', $fields);
    }

    private function request(string $path, string $method, array $fields = []): array
    {
        if ($this->baseUrl === '') {
            throw new \RuntimeException('Intelligent service URL is not configured.');
        }

        if ($this->apiKey === '') {
            throw new \RuntimeException('Intelligent service API key is not configured.');
        }

        $ch = curl_init($this->baseUrl . $path);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_TIMEOUT, $this->timeoutSeconds);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "X-Service-Key: {$this->apiKey}",
            'Accept: application/json',
        ]);

        if ($method !== 'GET') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }

        $response = curl_exec($ch);
        if ($response === false) {
            $message = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("Intelligent service request failed: {$message}");
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $decoded = json_decode($response, true);
        if (!is_array($decoded)) {
            $decoded = [
                'success' => false,
                'message' => 'Intelligent service returned an invalid response.',
            ];
        }

        return [
            'status' => $httpCode,
            'body' => $decoded,
        ];
    }
}
