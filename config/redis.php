<?php

// file path = root/config/redis.php

return [
    'scheme'   => 'tcp',
    'host'     => $_ENV['REDIS_HOST'],
    'port'     => $_ENV['REDIS_PORT'],
    'password' => $_ENV['REDIS_PASSWORD'],
];
