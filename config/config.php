<?php

$dbUrl = $_ENV['DB_URI'] ?? null;

if (!$dbUrl) {
    throw new RuntimeException("DB_URI not set in .env");
}

$dsnParts = parse_url($dbUrl);

return [
    'database' => [
        'host'     => $dsnParts['host'] ?? null,
        'port'     => $dsnParts['port'] ?? 5432,
        'dbname'   => ltrim($dsnParts['path'], '/'),
        'user'     => $dsnParts['user'] ?? null,
        'password' => $dsnParts['pass'] ?? null,
    ],
];
