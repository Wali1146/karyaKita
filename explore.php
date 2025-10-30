<?php include 'header.php'; ?>
<?php
$explore_query = "
SELECT
    p.post_id, p.content_title, p.content_text, p.media_url, p.created_at, p.post_type,
    u.username, u.full_name, u.profile_picture,
    (SELECT COUNT(like_id) FROM likes WHERE post_id = p.post_id) AS total_likes,
    (SELECT COUNT(comment_id) FROM comments WHERE post_id = p.post_id) AS total_comments
FROM posts p
JOIN users u ON p.user_id = u.user_id 
WHERE p.user_id != ? 
ORDER BY p.created_at DESC
LIMIT 50
";

$explore_stmt = $conn->prepare($explore_query);
$explore_stmt->bind_param("i", $current_user_id);
$explore_stmt->execute();
$explore_result = $explore_stmt->get_result();
?>

<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <h2>Jelajahi Karya Lain</h2>
            <hr>
            <?php if ($explore_result->num_rows > 0): ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php while ($post = $explore_result->fetch_assoc()): 
                        $is_video = ($post['post_type'] == 'video');
                        $media_folder = $is_video ? 'vid/' : 'img/';
                        $media_full_path = "assets/post/" . $media_folder . htmlspecialchars($post['media_url']);
                        $profile_pic_path = "assets/post/profile/" . htmlspecialchars($post['profile_picture'] ?? 'default.png');
                    ?>
                        <div class="col">
                            <div class="card max-h-100">
                                <?php if(!empty($post['media_url'])): ?>
                                    <div style="aspect-ratio: 1 / 1; overflow: hidden; position: relative;">
                                        <?php if ($is_video): ?>
                                            <video src="<?php echo $media_full_path; ?>" 
                                                data-bs-toggle="modal"
                                                data-bs-target="#postDetailModal_<?php echo $post['post_id']; ?>" 
                                                style="cursor: pointer; object-fit:cover; width: 100%; height: 100%;" muted
                                            ></video>
                                            <i class="bi bi-play-circle-fill" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2rem; color: white;"></i>
                                        <?php else: ?>
                                            <img src="<?php echo $media_full_path; ?>" 
                                                alt="Gambar Postingan"
                                                class="img-fluid" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#postDetailModal_<?php echo $post['post_id']; ?>" 
                                                style="cursor: pointer; object-fit:cover; width: 100%; height: 100%;"
                                            >
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal fade" id="postDetailModal_<?php echo $post['post_id']; ?>" 
                                        tabindex="-1" aria-labelledby="postDetailModalLabel_<?php echo $post['post_id']; ?>" 
                                        aria-hidden="true"
                                    >
                                        <div class="modal-dialog modal-xl modal-dialog-centered"> 
                                            <div class="modal-content"> 
                                                <div class="modal-body p-0">
                                                    <div class="row g-0">
                                                        <div class="col-md-7 d-flex justify-content-center align-items-center bg-dark">
                                                            <?php if ($is_video): ?>
                                                                <div class="post-media-container video">
                                                                    <video src="<?php echo $media_full_path; ?>" 
                                                                        class="post-video w-100" muted
                                                                    ></video>
                                                                    <div class="video-overlay">
                                                                        <svg xmlns="http://www.w3.org/2000/svg" 
                                                                            width="60" height="60" fill="white" 
                                                                            class="bi bi-play-circle-fill" viewBox="0 0 16 16"
                                                                        >
                                                                            <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM6.79 5.093A.5.5 0 0 0 6 5.5v5a.5.5 0 0 0 .79.407l3.5-2.5a.5.5 0 0 0 0-.814l-3.5-2.5z"/>
                                                                        </svg>
                                                                    </div>
                                                                </div>
                                                            <?php else: ?>
                                                                <img src="<?php echo $media_full_path; ?>" 
                                                                    class="img-fluid w-100" alt="Gambar Diperbesar" 
                                                                    style="max-height: 80vh; object-fit: contain;"
                                                                >
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-5 p-4 d-flex flex-column">
                                                            <div class="d-flex align-items-center mb-3 pb-2 border-bottom">
                                                                <img src="<?php echo $profile_pic_path; ?>" 
                                                                    class="rounded-circle me-3" 
                                                                    alt="Avatar" 
                                                                    style="width: 40px; height: 40px; object-fit: cover;"
                                                                >
                                                                <div>
                                                                    <h6 class="mb-0"><?php echo htmlspecialchars($post['full_name'] ?? 'Pengguna'); ?></h6>
                                                                    <small class="text-muted">@<?php echo htmlspecialchars($post['username'] ?? 'username'); ?></small>
                                                                </div>
                                                            </div>
                                                            <h5 class="mb-2"><?php echo htmlspecialchars($post['content_title'] ?? 'Tanpa Judul'); ?></h5>
                                                            <p class="text-muted small mb-3">
                                                                <?php echo nl2br(htmlspecialchars($post['content_text'] ?? '')); ?>
                                                            </p>
                                                            <ul class="nav nav-pills nav-fill mb-3" id="pills-tab-explore-<?php echo $post['post_id']; ?>" role="tablist">
                                                                <li class="nav-item" role="presentation">
                                                                    <button class="nav-link active" 
                                                                        id="pills-likes-tab-explore-<?php echo $post['post_id']; ?>" 
                                                                        data-bs-toggle="pill" 
                                                                        data-bs-target="#pills-likes-explore-<?php echo $post['post_id']; ?>" 
                                                                        type="button" role="tab">Suka (<?php echo $post['total_likes']; ?>)
                                                                    </button>
                                                                </li>
                                                                <li class="nav-item" role="presentation">
                                                                    <button class="nav-link" 
                                                                        id="pills-comments-tab-explore-<?php echo $post['post_id']; ?>" 
                                                                        data-bs-toggle="pill" 
                                                                        data-bs-target="#pills-comments-explore-<?php echo $post['post_id']; ?>" 
                                                                        type="button" role="tab">Komentar (<?php echo $post['total_comments']; ?>)
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                            <div class="tab-content flex-grow-1 overflow-auto" style="height: 300px;" id="pills-tabContent-explore-<?php echo $post['post_id']; ?>">
                                                                <div class="tab-pane fade show active" id="pills-likes-explore-<?php echo $post['post_id']; ?>" role="tabpanel">
                                                                    <p class="text-center text-muted">Belum ada penyuka</p>
                                                                    <div id="likes-list-explore-<?php echo $post['post_id']; ?>"></div>
                                                                </div>
                                                                <div class="tab-pane fade" id="pills-comments-explore-<?php echo $post['post_id']; ?>" role="tabpanel">
                                                                    <p class="text-center text-muted">Belum ada komentar</p>
                                                                    <div id="comments-list-explore-<?php echo $post['post_id']; ?>"></div>  
                                                                    <form class="mt-3 border-top pt-2">
                                                                        <input type="text" class="form-control form-control-sm" placeholder="Tambahkan komentar...">
                                                                        <button type="submit" class="btn btn-primary btn-sm mt-2 w-100">Kirim</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                            <button 
                                                                class="btn btn-sm btn-light mt-auto" 
                                                                data-bs-toggle="modal" 
                                                                data-bs-target="#reportModal" 
                                                                onclick="setReportPostId(<?php echo $post['post_id']; ?>)">
                                                                <i class="fas fa-flag"></i> Laporkan
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-secondary mt-3" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
                                        <div class="modal-dialog"> <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="reportModalLabel">Laporkan Postingan</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <form id="reportForm">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="post_id" id="report_post_id">
                                                        <div class="mb-3">
                                                            <label for="reportReason" class="form-label">Alasan Pelaporan *</label>
                                                            <select class="form-select" id="reportReason" name="reason" required>
                                                                <option value="" selected disabled>Pilih salah satu alasan...</option>
                                                                <option value="Konten Seksual/Nude">Konten Seksual/Nude</option>
                                                                <option value="Ujaran Kebencian atau Simbol">Ujaran Kebencian atau Simbol</option>
                                                                <option value="Spam atau Penipuan">Spam atau Penipuan</option>
                                                                <option value="Perundungan/Cyberbullying">Perundungan/Cyberbullying</option>
                                                                <option value="Hak Cipta atau Kekayaan Intelektual">Hak Cipta atau Kekayaan Intelektual</option>
                                                                <option value="Lainnya">Lainnya (Jelaskan di bawah)</option>
                                                            </select>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="reportDetails" class="form-label">Detail Tambahan (Opsional)</label>
                                                            <textarea class="form-control" id="reportDetails" name="details" rows="3" placeholder="Berikan detail tambahan jika perlu..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Kirim Laporan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Tidak ada postingan lain untuk dijelajahi saat ini.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>