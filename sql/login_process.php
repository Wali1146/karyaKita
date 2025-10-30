<?php
session_start();
header('Content-Type: application/json');
include '../config/database.php';

function sendJson($success, $message, $redirect = null) {
    echo json_encode(['success' => $success, 'message' => $message, 'redirectUrl' => $redirect]);
    exit(); 
}

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    $email_username = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    if(empty($email_username)|| empty($password)) {
        sendJson(false, "Mohon isi username/email dan password.");
    }

    $sql = "SELECT user_id, password, username, role, status FROM users WHERE email = ? OR username = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        sendJson(false, "SQL Prepare Gagal: " . $conn->error);
    }

    $stmt->bind_param("ss", $email_username , $email_username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        $hashed_password = $user['password'];

        if (password_verify($password, $hashed_password)) {

            if ($user['status'] == 'suspended') {
                sendJson(false, "Akun Anda telah di-nonaktifkan. Silakan hubungi admin.");
            }

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['status'] = $user['status'];
            $redirect_path = ($user['role'] == 'admin') ? 'admin/index.php' : 'homePage.php';
            
            sendJson(true, "Login berhasil! Mengalihkan...", $redirect_path);

        } else {
            sendJson(false, "Password salah.");
        }
    
    } else {
        sendJson(false, "Username atau Email salah.");
    }

    $stmt->close();
    $conn->close();
}