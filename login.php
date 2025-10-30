<?php
session_start();
include 'config/database.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <main class="container">
        <div class="row justify-content-center mt-5">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-lg p-3 mt-5">
                    <div class="card-body">
                        <h3 class="card-title text-center mb-4">Masuk ke Akun Anda</h3>
                        <form action="sql/login_process.php" method="POST" id="loginForm" data-redirect="homePage.php"> 
                            <div id="loginMessage"></div>
                            <div class="mb-3">
                                <label for="inputEmail" class="form-label">Email / Username</label>
                                <input type="text" class="form-control" id="inputEmail" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="inputPassword" class="form-label">Kata Sandi</label>
                                <input type="password" class="form-control" id="inputPassword" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="rememberMe" name="rememberMe">
                                <label class="form-check-label" for="rememberMe">Ingat Saya</label>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg" id="submitBtn">Login</button>
                            </div>
                            <div class="text-center mt-3">
                                <a href="forgotPass.php" class="text-decoration-none">Lupa Kata Sandi?</a>
                                <p class="mt-2">Belum punya akun? <a href="register.php">Daftar di sini</a></p>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>
<?php include 'footer.php';?>