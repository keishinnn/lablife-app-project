<?php

// file path = root/config/supabase.php

return [
    'url' => $_ENV['SUPABASE_URL'],
    'anon_key' => $_ENV['SUPABASE_ANON_KEY'],
    'service_role' => $_ENV['SUPABASE_SERVICE_ROLE_KEY']
];
