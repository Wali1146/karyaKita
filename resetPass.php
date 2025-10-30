<?php
session_start();
include 'config/database.php';

$error = '';
$success = '';
$token = $_GET['token'] ?? ''; 

if (empty($token)) {
    $error = "Token reset tidak ditemukan.";
} else {
    $sql = "SELECT email, expiry FROM password_resets WHERE token = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        $error = "Token tidak valid atau sudah digunakan.";
    } else {
        $row = $result->fetch_assoc();
        $reset_email = $row['email'];
        $expiry_time = strtotime($row['expiry']);

        if ($expiry_time < time()) {
            $error = "Token sudah kedaluwarsa. Silakan ajukan permintaan reset baru.";
            $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE token = ?");
            $delete_stmt->bind_param("s", $token);
            $delete_stmt->execute();
        } else {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $new_password = $_POST['new_password'];
                $confirm_password = $_POST['confirm_password'];

                if ($new_password !== $confirm_password) {
                    $error = "Kata sandi baru dan konfirmasi tidak cocok.";
                } else {
                    $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $update_sql = "UPDATE users SET password = ? WHERE email = ?";
                    $update_stmt = $conn->prepare($update_sql);
                    $update_stmt->bind_param("ss", $hashed_password, $reset_email);

                    if ($update_stmt->execute()) {
                        $delete_stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                        $delete_stmt->bind_param("s", $reset_email);
                        $delete_stmt->execute();

                        $success = "Kata sandi Anda berhasil diubah! Silakan <a href='login.php'>Login</a>.";
                    } else {
                        $error = "Gagal memperbarui kata sandi di database.";
                    }
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css"/>
</head>
<body>
    <main class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6">
                <h2 class="mb-4">Atur Ulang Kata Sandi</h2>
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php elseif (!empty($error)): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php else: ?>
                    <div class="alert alert-info">
                        Silakan masukkan kata sandi baru Anda. Token ini akan kedaluwarsa pada **<?php echo date('d-M-Y H:i:s', $expiry_time); ?>**.
                    </div>
                    <form action="resetPass.php?token=<?php echo htmlspecialchars($token); ?>" method="POST">
                        <div class="mb-3">
                            <label for="new_password" class="form-label">Kata Sandi Baru</label>
                            <input type="password" class="form-control" 
                                id="new_password" name="new_password" required minlength="6"
                            >
                        </div>
                        <div class="mb-3">
                            <label for="confirm_password" class="form-label">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" class="form-control" 
                                id="confirm_password" name="confirm_password" required minlength="6"
                            >
                        </div>
                        <button type="submit" class="btn btn-primary">Ganti Kata Sandi</button>
                    </form>
                <?php endif; ?>
                <p class="mt-3 text-center">
                    <a href="login.php">Kembali ke Halaman Login</a>
                </p>
            </div>
        </div>
    </main>
<?php include 'footer.php'; ?>