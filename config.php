<?php
define('DB_HOST', 'localhost');
define('DB_USER', 'biblio_user');
define('DB_PASS', 'password');
define('DB_NAME', 'bibliotheque_vintage');

function connectDB()
{
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    if ($conn->connect_error) {
        die("Erreur de connexion: " . $conn->connect_error);
    }

    $conn->set_charset("utf8");
    return $conn;
}
