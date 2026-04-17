<?php

return [
    'base_url' => rtrim($_ENV['INTELLIGENT_SERVICE_URL'] ?? '', '/'),
    'api_key' => $_ENV['INTELLIGENT_SERVICE_API_KEY'] ?? '',
    'timeout_seconds' => (int) ($_ENV['INTELLIGENT_SERVICE_TIMEOUT_SECONDS'] ?? 30),
];
