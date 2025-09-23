<?php

return [
    'site_key'   => $_ENV['CLOUDFLARE_TURNSTILE_SITE_KEY'] ?? '',
    'secret_key' => $_ENV['CLOUDFLARE_TURNSTILE_SECRET_KEY'] ?? '',
];
