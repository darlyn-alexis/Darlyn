<?php
include 'db.php';
session_start();

// Recibimos la acción (login o registro)
$action = $_POST['action'] ?? '';

if ($action == 'register') {
    $user = $_POST['username'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT); // Encriptación

    $sql = "INSERT INTO usuarios (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $user, $pass);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "message" => "Usuario creado"]);
    } else {
        echo json_encode(["status" => "error", "message" => "El usuario ya existe"]);
    }
}

if ($action == 'login') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    $sql = "SELECT id, password FROM usuarios WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($pass, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $user;
            echo json_encode([
                "status" => "success", 
                "user_id" => $row['id'], 
                "username" => $user
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Usuario no encontrado"]);
    }
}
?>