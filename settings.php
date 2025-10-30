<?php include 'header.php'; ?>
<?php
$full_name = htmlspecialchars($current_user['full_name'] ?? '');
$email = htmlspecialchars($current_user['email'] ?? '');
$username = htmlspecialchars($current_user['username'] ?? '');
$bio = htmlspecialchars($current_user['bio'] ?? '');
$profile_picture = htmlspecialchars($current_user['profile_picture'] ?? 'default.png');

if (isset($_POST['update_profile'])) {    
    $full_name = trim($_POST['full_name']);
    $bio = trim($_POST['bio']);
    $update_fields = [];
    $update_values = [];
    $types = '';

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['profile_picture']['tmp_name'];
        $file_name = uniqid() . '_' . basename($_FILES['profile_picture']['name']);
        $target_dir = "assets/post/profile/";
        $target_file = $target_dir . $file_name;
        $image_file_type = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        if (!in_array($image_file_type, ['jpg', 'jpeg', 'png', 'gif'])) {
            $_SESSION['settings_error'] = "Format file tidak diizinkan. Gunakan JPG, JPEG, PNG, atau GIF.";
            header("Location: settings.php");
            exit();
        }
        
        if (move_uploaded_file($file_tmp_name, $target_file)) {
            $old_pic_query = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
            $old_pic_query->bind_param("i", $current_user_id);
            $old_pic_query->execute();
            $old_pic_result = $old_pic_query->get_result()->fetch_assoc();
            $old_pic_name = $old_pic_result['profile_picture'];

            if ($old_pic_name !== 'default.png' && file_exists($target_dir . $old_pic_name)) {
                unlink($target_dir . $old_pic_name);
            }
            
            $update_fields[] = "profile_picture = ?";
            $update_values[] = $file_name;
            $types .= 's';
        } else {
            $_SESSION['settings_error'] = "Gagal mengupload gambar.";
            header("Location: settings.php");
            exit();
        }
    }

    $update_fields[] = "full_name = ?";
    $update_values[] = $full_name;
    $types .= 's';
    $update_fields[] = "bio = ?";
    $update_values[] = $bio;
    $types .= 's';

    if (!empty($update_fields)) {
        $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE user_id = ?";
        $update_values[] = $current_user_id;
        $types .= 'i';
        $stmt = $conn->prepare($sql);
        $bind_params = array_merge([$types], $update_values);
        $references = [];

        foreach ($bind_params as $key => $value) {
            $references[$key] = &$bind_params[$key];
        }
        call_user_func_array([$stmt, 'bind_param'], $references);
        
        if ($stmt->execute()) {
            $_SESSION['settings_success'] = "Profil berhasil diperbarui! Silakan refresh untuk melihat perubahan.";
        } else {
            $_SESSION['settings_error'] = "Gagal memperbarui profil: " . $stmt->error;
        }
    } else {
        $_SESSION['settings_error'] = "Tidak ada yang diubah.";
    }
    
    header("Location: settings.php");
    exit();
}

if (isset($_POST['update_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $sql = "SELECT password FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $current_user_id);
    $stmt->execute();
    $user_data = $stmt->get_result()->fetch_assoc();

    if ($new_password !== $confirm_password) {
        $_SESSION['settings_error'] = "Kata sandi baru dan konfirmasi tidak cocok.";
    } elseif (!password_verify($current_password, $user_data['password'])) {
        $_SESSION['settings_error'] = "Kata sandi saat ini salah.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
        $update_sql = "UPDATE users SET password = ? WHERE user_id = ?";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bind_param("si", $hashed_password, $current_user_id);
        
        if ($update_stmt->execute()) {
            $_SESSION['settings_success'] = "Kata sandi berhasil diperbarui!";
        } else {
            $_SESSION['settings_error'] = "Gagal memperbarui kata sandi.";
        }
    }
    header("Location: settings.php#password-pane");
    exit();
}
?>

<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <h2 class="mb-4">Pengaturan Akun</h2>
            <?php if (isset($_SESSION['settings_success'])): ?>
                <div class="alert alert-success"><?php echo $_SESSION['settings_success']; unset($_SESSION['settings_success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['settings_error'])): ?>
                <div class="alert alert-danger"><?php echo $_SESSION['settings_error']; unset($_SESSION['settings_error']); ?>
            </div>
            <?php endif; ?>
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="list-group" id="settingsList" role="tablist">
                        <a class="list-group-item list-group-item-action active" id="account-list" data-bs-toggle="list" href="#account-pane" role="tab" aria-controls="account-pane">Akun & Profil</a>
                        <a class="list-group-item list-group-item-action" id="password-list" data-bs-toggle="list" href="#password-pane" role="tab" aria-controls="password-pane">Ganti Kata Sandi</a>
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="account-pane" role="tabpanel" aria-labelledby="account-list">
                            <div class="card p-4 shadow-sm">
                                <h4>Ubah Profil</h4>
                                <form action="settings.php" method="POST" enctype="multipart/form-data">
                                    <div class="mb-4 text-center">
                                        <label class="form-label d-block">Foto Profil</label>
                                        <img src="assets/post/profile/<?php echo $profile_picture; ?>"
                                            class="rounded-circle mb-2"
                                            alt="Foto Profil"
                                            style="width: 120px; height: 120px; object-fit: cover;">
                                        <input class="form-control form-control-sm mx-auto" type="file" name="profile_picture" style="max-width: 300px;">
                                        <small class="text-muted">Maksimal 2MB, format JPG, PNG.</small>
                                    </div>
                                    <div class="mb-3">
                                        <label for="fullName" class="form-label">Nama Lengkap</label>
                                        <input type="text" class="form-control" id="fullName" name="full_name" value="<?php echo $full_name; ?>">
                                    </div>
                                    <div class="mb-3">
                                        <label for="bio" class="form-label">Bio (Tentang Anda)</label>
                                        <textarea class="form-control" id="bio" name="bio" rows="3"><?php echo $bio; ?></textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" value="<?php echo $email; ?>" disabled>
                                        <small class="text-muted">Email tidak dapat diubah di sini.</small>
                                    </div>
                                    <button type="submit" name="update_profile" class="btn btn-primary">Simpan Perubahan Profil</button>
                                </form>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="password-pane" role="tabpanel" aria-labelledby="password-list">
                            <div class="card p-4 shadow-sm">
                                <h4>Ganti Kata Sandi</h4>
                                <form action="settings.php" method="POST">
                                    <div class="mb-3">
                                        <label for="currentPassword" class="form-label">Kata Sandi Saat Ini</label>
                                        <input type="password" class="form-control" id="currentPassword" name="current_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="newPassword" class="form-label">Kata Sandi Baru</label>
                                        <input type="password" class="form-control" id="newPassword" name="new_password" required>
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label">Konfirmasi Kata Sandi Baru</label>
                                        <input type="password" class="form-control" id="confirmPassword" name="confirm_password" required>
                                    </div>
                                    <button type="submit" name="update_password" class="btn btn-warning">Ganti Kata Sandi</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>