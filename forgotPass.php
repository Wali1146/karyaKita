<?php
session_start();
include 'config/database.php';
date_default_timezone_set('Asia/Jakarta');

if (isset($_SESSION['user_id'])) {
    header("Location: homePage.php");
    exit();
}

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $sql = "SELECT user_id FROM users WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $user_id = $user['user_id'];
        $token = bin2hex(random_bytes(32));
        $expiry = date('Y-m-d H:i:s', time() + 900);
        
        $insert_sql = "INSERT INTO password_resets (email, token, expiry) VALUES (?, ?, ?)
                       ON DUPLICATE KEY UPDATE token = ?, expiry = ?";
        
        $insert_stmt = $conn->prepare($insert_sql);
        $insert_stmt->bind_param("sssss", $email, $token, $expiry, $token, $expiry);
        
        if ($insert_stmt->execute()) {
            $reset_link = "http://localhost/karyaKita/resetPass.php?token=" . $token;
            $message = "Permintaan reset kata sandi berhasil. Cek email Anda untuk tautan reset.";
            $message .= " (TEST LINK: <a href='$reset_link'>Reset Sekarang</a>)"; 
            $message_type = 'success';
        } else {
            $message = "Terjadi kesalahan saat membuat token reset.";
            $message_type = 'danger';
        }

    } else {
        $message = "Jika email Anda terdaftar, tautan reset akan dikirimkan.";
        $message_type = 'info';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
    <main class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <h2 class="mb-4">Lupa password</h2>
                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?>" role="alert">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>
                <form action="forgotPass.php" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim tautan reset</button>
                    <p class="mt-3 text-center">
                        <a href="login.php">Kembali ke Halaman Login</a>
                    </p>
                </form>
            </div>
        </div>
    </main>
<?php include 'footer.php'; ?>