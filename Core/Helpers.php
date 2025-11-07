<?php

function base_path($path)
{
    return BASE_PATH . $path;
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

function is_valid_image_url($url) {
    if (empty($url)) return false;
    $headers = @get_headers($url, 1);
    return $headers && strpos($headers[0], '200') !== false;
}
