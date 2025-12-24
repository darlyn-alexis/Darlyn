<?php
$host = "localhost";
$user = "root"; // Tu usuario de phpMyAdmin (por defecto es root)
$pass = "";     // Tu contraseña de phpMyAdmin (por defecto vacía en XAMPP)
$db   = "music";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Configurar para que acepte caracteres especiales como tildes o la ñ
$conn->set_charset("utf8");
?>