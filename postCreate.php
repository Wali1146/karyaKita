<?php include 'header.php';?>
<?php 
$base_upload_dir = "assets/post/";
$image_upload_dir = $base_upload_dir . "img/";
$video_upload_dir = $base_upload_dir . "vid/";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    $media_url = NULL;
    $media_type = NULL;

    if (empty($title) || empty($content)) {
        $_SESSION['post_error'] = "Judul dan konten tidak boleh kosong.";
        header("Location: postCreate.php");
        exit();
    }

    if (isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_name = $_FILES['media']['tmp_name'];
        $filename = basename($_FILES['media']['name']);
        $file_extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($file_extension, ['mp4', 'mov', 'avi', 'webm'])) {
            $media_type = 'video';
            $target_dir = $video_upload_dir;
        } elseif (in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
            $media_type = 'image';
            $target_dir = $image_upload_dir;
        } else {
            $_SESSION['post_error'] = "Format media tidak didukung.";
            header("Location: postCreate.php");
            exit();
        }

        $unique_filename = uniqid() . '_' . time() . '_' . $filename;
        $target_file = $target_dir . $unique_filename;

        if (move_uploaded_file($file_tmp_name, $target_file)) {
            $media_url = $unique_filename; 
        } else {
            $_SESSION['post_error'] = "Gagal mengupload media. Periksa izin folder.";
            header("Location: postCreate.php");
            exit();
        }
    }

    $sql = "INSERT INTO posts (user_id, content_title, content_text, media_url, post_type) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issss", $current_user_id, $title, $content, $media_url, $media_type);

    if ($stmt->execute()) {
        $_SESSION['post_success'] = "Postingan berhasil diterbitkan!";
        header("Location: profile.php");
        exit();
    } else {
        $_SESSION['post_error'] = "Terjadi kesalahan database: " . $stmt->error;
        header("Location: postCreate.php");
        exit();
    }
}

$postError = $_SESSION['post_error'] ?? '';
$postSuccess = $_SESSION['post_success'] ?? '';
unset($_SESSION['post_error']);
unset($_SESSION['post_success']);
?>
<main class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card mb-5 shadow-lg">
                <div class="card-body p-4">
                    <h2 class="mb-4">Buat Postingan Baru</h2>
                    <?php if (!empty($postSuccess)): ?>
                        <div class="alert alert-success" role="alert"><?php echo htmlspecialchars($postSuccess); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($postError)): ?>
                        <div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($postError); ?></div>
                    <?php endif; ?>
                    <form action="postCreate.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="title" class="form-label">Judul</label>
                            <input type="text" class="form-control" id="title" name="title" required>
                        </div>
                        <div class="mb-3">
                            <label for="content" class="form-label">Deskripsi</label>
                            <textarea class="form-control" id="content" name="content" rows="5" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="media" class="form-label">Media (Foto / Video)</label>
                            <input type="file" class="form-control" id="media" name="media">
                        </div>
                        <button type="submit" class="btn btn-primary">Kirim</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'footer.php';?>