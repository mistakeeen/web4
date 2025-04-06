<?php

$server = "localhost";
$user = "root";
$password = "";
$db = "new_db";


$mysqli = new mysqli($server, $user, $password, $db);

if ($mysqli->connect_error) {
    die("Ошибка подключения: " . $mysqli->connect_error);
}
?>