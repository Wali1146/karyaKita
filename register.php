<?php
session_start();
include 'config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST"){
    unset($_SESSION['regisError']);
    unset($_SESSION['regisSuccess']);

    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    if (empty($username) || empty($email) || empty($password)) {
        $_SESSION['regisError'] = "Semua kolom harus diisi.";
        header("Location: register.php");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['regisError'] = "Format email tidak valid.";
        header("Location: register.php");
        exit();
    }

    $checkSql = "SELECT user_id FROM users WHERE username = ? OR email = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();

    if ($checkResult->num_rows > 0) {
        $user_row = $checkResult->fetch_assoc();
        $_SESSION['regisError'] = "Email atau Username sudah terdaftar. Silakan gunakan yang lain.";
        header("Location: register.php");
        exit();
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $sql = "INSERT INTO users (username, email, password) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    if ($stmt->execute()) {
        $_SESSION['regisSuccess'] = "Pendaftaran berhasil! Selamat datang!";
        header("Location: register.php");
        exit();

    } else {
        $_SESSION['regisError'] = "Terjadi kesalahan server. Coba lagi nanti.";
        header("Location: register.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <main class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg p-3 mt-5">
                    <div class="card-body">
                        <h2 class="mb-4">Register</h2>
                        <?php if (isset($_SESSION['regisError'])): ?>
                            <div class="alert alert-danger" role="alert">
                                <?php 
                                    echo htmlspecialchars($_SESSION['regisError']); 
                                    unset($_SESSION['regisError']);
                                ?>
                            </div>
                        <?php endif ?>
                        <?php if (isset($_SESSION['regisSuccess'])): ?>
                            <div class="alert alert-success" role="alert">
                                <?php 
                                    echo htmlspecialchars($_SESSION['regisSuccess']); 
                                    unset($_SESSION['regisSuccess']);
                                ?>
                            </div>
                        <?php endif ?>
                        <form action="register.php" method="POST">
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Register</button>
                            <p class="mt-2">Sudah punya akun? <a href="login.php">Login di sini</a></p>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php include 'footer.php';?>