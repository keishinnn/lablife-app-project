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
