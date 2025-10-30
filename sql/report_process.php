<?php
session_start();
header('Content-Type: application/json');
include '../config/database.php';

function sendJson($success, $message) {
    echo json_encode(['success' => $success, 'message' => $message]);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    sendJson(false, "Anda harus login untuk melaporkan postingan.");
}

$reporter_id = $_SESSION['user_id'];
$post_id = $_POST['post_id'] ?? null;
$reason = $_POST['reason'] ?? null;
$details = $_POST['details'] ?? null;

if (empty($post_id) || empty($reason)) {
    sendJson(false, "Postingan dan alasan harus diisi.");
}

$sql_check = "SELECT report_id FROM reports WHERE post_id = ? AND reported_by = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("ii", $post_id, $reporter_id);
$stmt_check->execute();
if ($stmt_check->get_result()->num_rows > 0) {
    sendJson(false, "Anda sudah pernah melaporkan postingan ini.");
}

$stmt_check->close();
$status = 'pending'; 
$sql_insert = "INSERT INTO reports (post_id, reported_by, reason, details, status) VALUES (?, ?, ?, ?, ?)";
$stmt_insert = $conn->prepare($sql_insert);
$stmt_insert->bind_param("iisss", $post_id, $reporter_id, $reason, $details, $status);

if ($stmt_insert->execute()) {
    sendJson(true, "Laporan Anda telah terkirim dan akan segera ditinjau oleh Admin.");
} else {
    sendJson(false, "Gagal menyimpan laporan ke database: " . $conn->error);
}

$stmt_insert->close();
$conn->close();
?>