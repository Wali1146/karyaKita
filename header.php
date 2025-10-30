<?php 
session_start();
include 'config/database.php';

if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$current_user_id = $_SESSION['user_id'];

$user_data_query = "SELECT username, full_name, bio, profile_picture FROM users WHERE user_id = ?";
$user_stmt = $conn->prepare($user_data_query);
$user_stmt->bind_param("i", $current_user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();
$current_user = $user_result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Karya Kita</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-3 fixed-top">
            <div class="container-fluid px-4 px-lg-5 gap-3">
                <div class="navbar-brand">
                    <a href="homePage.php" class="d-flex align-items-center text-decoration-none text-primary gap-2">
                        <img src="assets/sets/canvas.png" alt="logo" class="logo">
                        <h1>Karya Kita</h1>
                    </a>
                </div>
                <button class="navbar-toggler border-0" type="button" 
                    data-bs-toggle="collapse" data-bs-target="#navbarNav" 
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0 align-items-center gap-2 me-3">
                        <li class="nav-item">
                            <a class="nav-link" href="homePage.php">Menu utama</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="explore.php">Jelajah</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="postCreate.php">
                                <button class="btn btn-primary ms-2 form-control">Buat postingan</button>
                            </a>
                        </li>
                    </ul>
                </div>
                <form action="" class="d-flex" role="search">
                    <input class="form-control me-2" type="search" placeholder="Cari..." aria-label="Search">
                    <button class="btn btn-outline-primary ms-2" type="submit">Cari</button>
                </form>
                <div class="d-flex align-items-center">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" 
                            href="#" id="navbarDropdownMenuLink" 
                            role="button" data-bs-toggle="dropdown" 
                            aria-expanded="false">
                            <?php
                                $profile_pic = $current_user['profile_picture'] ?? 'default.png'; 
                                $profile_pic_path = "assets/post/profile/" . htmlspecialchars($profile_pic);
                            ?>
                            <img src="<?php echo $profile_pic_path; ?>" 
                                class="rounded-circle me-2" height="30" alt="Avatar"
                            />
                            <span class="d-none d-sm-block">Halo, <?php echo htmlspecialchars($current_user['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                            <li><a class="dropdown-item" href="profile.php">Profil Saya</a></li>
                            <li><a class="dropdown-item" href="settings.php">Pengaturan</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="logout.php">Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>