<?php
// Desactivar la visualización de errores brutos para que no rompan el JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

include 'db.php';
header('Content-Type: application/json');

// Función para responder siempre en JSON
function respond($data) {
    echo json_encode($data);
    exit;
}

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$usuario_id = $_POST['usuario_id'] ?? $_GET['usuario_id'] ?? null;

if (!$usuario_id) {
    respond(['status' => 'error', 'message' => 'Usuario no identificado']);
}

try {
    // --- ACCIÓN: CANCIONES (Favoritos normal) ---
    if ($action == 'toggle') {
        $track_id = $_POST['track_id'];
        $titulo = $_POST['titulo'];
        $artista = $_POST['artista'];
        $portada = $_POST['portada'];

        $check = $conn->prepare("SELECT id FROM favoritos_canciones WHERE usuario_id = ? AND track_id = ?");
        $check->bind_param("is", $usuario_id, $track_id);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            $del = $conn->prepare("DELETE FROM favoritos_canciones WHERE usuario_id = ? AND track_id = ?");
            $del->bind_param("is", $usuario_id, $track_id);
            $del->execute();
            respond(['status' => 'removed']);
        } else {
            $ins = $conn->prepare("INSERT INTO favoritos_canciones (usuario_id, track_id, titulo, artista, portada) VALUES (?, ?, ?, ?, ?)");
            $ins->bind_param("issss", $usuario_id, $track_id, $titulo, $artista, $portada);
            $ins->execute();
            respond(['status' => 'added']);
        }
    }

    // --- ACCIÓN: ARTISTAS (Tus campos específicos) ---


    if ($action == 'toggle_artista') {
        $artista_id = $_POST['artista_id'];
        $nombre_artista = $_POST['nombre_artista'];
        $foto_artista = $_POST['foto_artista'];

        $check = $conn->prepare("SELECT id FROM favoritos_artistas WHERE usuario_id = ? AND artista_id = ?");
        $check->bind_param("is", $usuario_id, $artista_id);
        $check->execute();

        if ($check->get_result()->num_rows > 0) {
            $del = $conn->prepare("DELETE FROM favoritos_artistas WHERE usuario_id = ? AND artista_id = ?");
            $del->bind_param("is", $usuario_id, $artista_id);
            $del->execute();
            respond(['status' => 'removed']);
        } else {
            $ins = $conn->prepare("INSERT INTO favoritos_artistas (usuario_id, artista_id, nombre_artista, foto_artista) VALUES (?, ?, ?, ?)");
            $ins->bind_param("isss", $usuario_id, $artista_id, $nombre_artista, $foto_artista);
            $ins->execute();
            respond(['status' => 'added']);
        }
    }

    // --- ACCIÓN: LISTA DE CANCIONES ---
    if ($action == 'list') {
        $stmt = $conn->prepare("SELECT track_id as id, titulo as title, artista as artist, portada as cover FROM favoritos_canciones WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        respond(['status' => 'success', 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }

    // --- ACCIÓN: LISTA DE ARTISTAS ---
    if ($action == 'list_artistas') {
        $stmt = $conn->prepare("SELECT artista_id as id, nombre_artista as name, foto_artista as picture FROM favoritos_artistas WHERE usuario_id = ?");
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        respond(['status' => 'success', 'data' => $stmt->get_result()->fetch_all(MYSQLI_ASSOC)]);
    }

} catch (Exception $e) {
    respond(['status' => 'error', 'message' => $e->getMessage()]);
}

// --- ACCIÓN: OBTENER DATOS DE INICIO (Artistas + Top 20 Canciones) ---
if ($action == 'get_home_data') {
    // 1. Obtener todos los artistas favoritos
    $resArtistas = $conn->prepare("SELECT artista_id as id, nombre_artista as name, foto_artista as picture FROM favoritos_artistas WHERE usuario_id = ? ORDER BY id DESC");
    $resArtistas->bind_param("i", $usuario_id);
    $resArtistas->execute();
    $artistas = $resArtistas->get_result()->fetch_all(MYSQLI_ASSOC);

    // 2. Obtener las últimas 20 canciones favoritas
    $resCanciones = $conn->prepare("SELECT track_id as id, titulo as title, artista as artist, portada as cover FROM favoritos_canciones WHERE usuario_id = ? ORDER BY id DESC LIMIT 20");
    $resCanciones->bind_param("i", $usuario_id);
    $resCanciones->execute();
    $canciones = $resCanciones->get_result()->fetch_all(MYSQLI_ASSOC);

    respond([
        'status' => 'success',
        'artistas' => $artistas,
        'canciones' => $canciones
    ]);
}


?>