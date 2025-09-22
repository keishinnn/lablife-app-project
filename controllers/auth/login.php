<?php

$loading = false;
$error = "";

view("auth/login.view.php");

/* if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. select the id from the db 

    // 2. check if the user is authorized
    authorize();

    // 3. logic for desired action

    // 4. redirect to specific page
    header('location: /u/12354');
    exit();
}; */