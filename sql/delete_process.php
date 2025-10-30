<?php
session_start();
header('Content-Type: application/json');
include '../config/database.php';

function sendJson($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    sendJson(false, "Anda harus login untuk melakukan aksi ini.");
}

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role'] ?? 'user';
$post_id = $_POST['post_id'] ?? null;

if (empty($post_id)) {
    sendJson(false, "ID Postingan tidak ditemukan.");
}

$is_admin = ($user_role === 'admin');

if ($is_admin) {
    $sql_delete = "DELETE FROM posts WHERE post_id = ?";
} else {
    $sql_delete = "DELETE FROM posts WHERE post_id = ? AND user_id = ?";
}

$stmt = $conn->prepare($sql_delete);

if ($is_admin) {
    $stmt->bind_param("i", $post_id);
} else {
    $stmt->bind_param("ii", $post_id, $user_id);
}

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        sendJson(true, "Postingan berhasil dihapus.");
    } else {
        sendJson(false, "Gagal menghapus. Postingan tidak ditemukan atau Anda tidak memiliki izin.");
    }
} else {
    sendJson(false, "Terjadi kesalahan database: " . $conn->error);
}

$stmt->close();
$conn->close();
?>