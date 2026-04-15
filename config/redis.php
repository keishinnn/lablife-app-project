<?php

$redisUrl = $_ENV['REDIS_URL'] ?? '';

if ($redisUrl !== '') {
    return $redisUrl;
}

$config = [
    'scheme' => $_ENV['REDIS_SCHEME'] ?? 'tcp',
    'host' => $_ENV['REDIS_HOST'] ?? '127.0.0.1',
    'port' => (int) ($_ENV['REDIS_PORT'] ?? 6379),
];

if (!empty($_ENV['REDIS_PASSWORD'])) {
    $config['password'] = $_ENV['REDIS_PASSWORD'];
}

return $config;
