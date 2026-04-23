<?php

function base_path($path)
{
    $trimmedPath = ltrim($path, '/\\');
    $candidate = BASE_PATH . $trimmedPath;

    if (file_exists($candidate) || is_dir($candidate)) {
        return $candidate;
    }

    $parts = preg_split('#[\\/]+#', $trimmedPath);
    if (!$parts || $parts[0] === '') {
        return BASE_PATH . $trimmedPath;
    }

    $rootMap = [
        'Views' => 'views',
        'views' => 'views',
        'Controllers' => 'controllers',
        'controllers' => 'controllers',
        'Config' => 'config',
        'config' => 'config',
        'Public' => 'public',
        'public' => 'public',
        'Core' => 'Core',
        'Models' => 'Models',
        'Services' => 'Services',
    ];

    $first = $parts[0];
    if (isset($rootMap[$first])) {
        $parts[0] = $rootMap[$first];
        $fallback = BASE_PATH . implode(DIRECTORY_SEPARATOR, $parts);

        if (file_exists($fallback) || is_dir($fallback)) {
            return $fallback;
        }
    }

    return BASE_PATH . $trimmedPath;
}

function current_path(): string
{
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);

    return is_string($path) && $path !== '' ? $path : '/';
}

function path_is(string $path): bool
{
    return current_path() === $path;
}

function path_starts_with(string $prefix): bool
{
    return str_starts_with(current_path(), $prefix);
}

function app_env(): string
{
    return $_ENV['APP_ENV'] ?? 'production';
}

function app_debug(): bool
{
    $value = strtolower((string) ($_ENV['APP_DEBUG'] ?? 'false'));

    return in_array($value, ['1', 'true', 'yes', 'on'], true);
}

function is_https_request(): bool
{
    return (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (($_SERVER['SERVER_PORT'] ?? null) === '443')
        || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function is_json_request(): bool
{
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';

    return str_contains($accept, 'application/json')
        || str_contains($contentType, 'application/json')
        || strtolower($requestedWith) === 'xmlhttprequest';
}

function request_json(): array
{
    $raw = file_get_contents('php://input');

    if ($raw === false || $raw === '') {
        return [];
    }

    $decoded = json_decode($raw, true);

    return is_array($decoded) ? $decoded : [];
}

function json_response(array $payload, int $status = 200): void
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    echo json_encode($payload);
    exit;
}

function redirect(string $path): void
{
    header("Location: {$path}");
    exit;
}

function session_flash_set(string $key, $value): void
{
    if (!isset($_SESSION['_flash']) || !is_array($_SESSION['_flash'])) {
        $_SESSION['_flash'] = [];
    }

    $_SESSION['_flash'][$key] = $value;
}

function session_flash_pull(string $key, $default = null)
{
    $value = $_SESSION['_flash'][$key] ?? $default;

    if (isset($_SESSION['_flash'][$key])) {
        unset($_SESSION['_flash'][$key]);
    }

    if (empty($_SESSION['_flash'])) {
        unset($_SESSION['_flash']);
    }

    return $value;
}

function app_log(string $message, string $level = 'ERROR'): void
{
    error_log(sprintf('[%s] %s', strtoupper($level), $message));
}

function app_log_exception(Throwable $exception, string $context = ''): void
{
    $prefix = $context !== '' ? "{$context}: " : '';

    app_log($prefix . $exception::class . ' - ' . $exception->getMessage());
}

function generic_error_message(): string
{
    return app_debug() ? 'Something went wrong. Check server logs for details.' : 'Something went wrong. Please try again.';
}

function handle_exception(Throwable $exception): void
{
    app_log_exception($exception, 'Unhandled exception');

    if (headers_sent()) {
        return;
    }

    if (is_json_request()) {
        json_response(['error' => generic_error_message()], 500);
    }

    http_response_code(500);
    require base_path('Views/500.php');
}

function view($path, $data = [])
{
    extract($data);
    require base_path('Views/' . $path);
}

function calculateAge(string $birthdate): int
{
    $today = new DateTime();
    $birthDate = new DateTime($birthdate);
    $age = $today->format('Y') - $birthDate->format('Y');

    $monthDiff = $today->format('m') - $birthDate->format('m');
    if ($monthDiff < 0 || ($monthDiff === 0 && $today->format('d') < $birthDate->format('d'))) {
        $age--;
    }

    return $age;
}

function is_valid_image_url($url)
{
    if (empty($url)) {
        return false;
    }

    if (str_starts_with($url, '/')) {
        return true;
    }

    return filter_var($url, FILTER_VALIDATE_URL) !== false;
}
