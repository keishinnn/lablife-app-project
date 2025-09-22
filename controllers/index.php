<?php

view("index.view.php");

use Core\App;
use Core\Database;

$db = App::resolve(Database::class);
$results = $db->query("SELECT * FROM users WHERE id = '1cd07edb-1c7c-4621-8093-824793224364'")->fetch(PDO::FETCH_ASSOC);

var_dump($results);
