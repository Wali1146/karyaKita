<?php include 'header.php'; ?>
<?php
$posts_query = "
SELECT
    p.post_id, p.content_title, p.content_text, p.media_url, p.created_at, p.post_type, 
    (SELECT COUNT(like_id) FROM likes WHERE post_id = p.post_id) AS total_likes,
    (SELECT COUNT(comment_id) FROM comments WHERE post_id = p.post_id) AS total_comments
FROM posts p
WHERE p.user_id = ? 
ORDER BY p.created_at DESC
";

$posts_stmt = $conn->prepare($posts_query);
$posts_stmt->bind_param("i", $current_user_id);
$posts_stmt->execute();
$posts_result = $posts_stmt->get_result();

$stats_query = "
SELECT
(SELECT COUNT(post_id) FROM posts WHERE user_id = ?) AS total_posts,
(SELECT COUNT(follower_id) FROM follows WHERE following_id = ?) AS total_followers,
(SELECT COUNT(following_id) FROM follows WHERE follower_id = ?) AS total_following
";

$stats_stmt = $conn->prepare($stats_query);
$stats_stmt->bind_param("iii", $current_user_id, $current_user_id, $current_user_id);
$stats_stmt->execute();
$stats_result = $stats_stmt->get_result();
$stats = $stats_result->fetch_assoc();
?>

<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <div class="card p-4 mb-4 shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-3 text-center mb-3 mb-md-0">
                        <img src="assets/post/profile/<?php echo htmlspecialchars($current_user['profile_picture']) ?? 'default.png'; ?>"
                            class="rounded-circle border border-2 border-primary"
                            alt="Foto Profil"
                            data-bs-toggle="modal"
                            data-bs-target="#profilePictureModal"
                            style="width: 150px; height: 150px; object-fit: cover;"
                        >
                    </div>
                    <div class="col-md-9">
                        <h2 class="mb-1">
                            <?php echo !empty($current_user['full_name']) ? htmlspecialchars($current_user['full_name']) : 'Nama Belum Diatur.'; ?>
                            <?php if (empty($current_user['full_name'])): ?>
                                <small class="text-muted d-block fs-6">(ubah namamu di pengaturan)</small>
                            <?php endif; ?>
                        </h2>
                        <p class="text-muted">@<?php echo htmlspecialchars($current_user['username']); ?></p>
                        <div class="d-flex justify-content-start mb-3">
                            <div class="me-4 text-center">
                                <h5 class="mb-0"><?php echo $stats['total_posts']; ?></h5>
                                <small class="text-muted">Postingan</small>
                            </div>
                            <div class="me-4 text-center">
                                <h5 class="mb-0"><?php echo $stats['total_followers'] ?></h5>
                                <small class="text-muted">Pengikut</small>
                            </div>
                            <div class="text-center">
                                <h5 class="mb-0"><?php echo $stats['total_following'] ?></h5>
                                <small class="text-muted">Mengikuti</small>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 border-top pt-3">
                    <p class="mb-0">
                        <?php echo !empty($current_user['bio']) ? htmlspecialchars($current_user['bio']) : 'Belum ada bio.'; ?>
                    </p>
                </div>
            </div>
            <hr class="mb-4">
            <h4 class="mb-3">Koleksi Postingan (<?php echo $stats['total_posts']; ?>)</h4>
            <?php if ($posts_result->num_rows > 0): ?>
                <div class="row row-cols-1 row-cols-md-3 g-4">
                    <?php while ($post = $posts_result->fetch_assoc()): 
                        $is_video = ($post['post_type'] == 'video');
                        $media_folder = $is_video ? 'vid/' : 'img/';
                        $media_full_path = "assets/post/" . $media_folder . htmlspecialchars($post['media_url']);
                    ?>
                        <div class="col">
                            <div class="card max-h-100">
                                <?php if (!empty($post['media_url'])): ?>
                                    <div style="aspect-ratio: 1 / 1; overflow: hidden; position: relative;">
                                        <?php if ($is_video): ?>
                                            <video src="<?php echo $media_full_path; ?>"
                                                data-bs-toggle="modal"
                                                data-bs-target="#postDetailModal_<?php echo $post['post_id']; ?>" 
                                                style="cursor: pointer; object-fit:cover; width: 100%; height: 100%;" muted
                                            ></video>
                                            <i class="bi bi-play-circle-fill text-white" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 2rem; opacity: 0.8;"></i>
                                        <?php else: ?>
                                            <img src="<?php echo $media_full_path; ?>"
                                                alt="Gambar Postingan"
                                                class="img-fluid"
                                                data-bs-toggle="modal"
                                                data-bs-target="#postDetailModal_<?php echo $post['post_id']; ?>" 
                                                style="cursor: pointer; object-fit:cover; width: 100%; height: 100%;">
                                        <?php endif; ?>
                                    </div>
                                    <div class="modal fade" id="postDetailModal_<?php echo $post['post_id']; ?>" tabindex="-1" aria-labelledby="postDetailModalLabel_<?php echo $post['post_id']; ?>" aria-hidden="true">
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
                                                                <img src="<?php echo $media_full_path; ?>" class="img-fluid w-100" alt="Gambar Diperbesar" style="max-height: 80vh; object-fit: contain;">
                                                            <?php endif; ?>
                                                        </div>
                                                        <div class="col-md-5 p-4 d-flex flex-column">
                                                            <div class="d-flex flex-column mb-3 pb-2 border-bottom">
                                                                <h4 class="mb-2"><?php echo htmlspecialchars($post['content_title'] ?? 'Tanpa Judul'); ?></h4>
                                                                <p class="text-muted small">
                                                                    <?php echo nl2br(htmlspecialchars($post['content_text'] ?? '')); ?>
                                                                </p>
                                                            </div>
                                                            <ul class="nav nav-pills nav-fill mb-3" id="pills-tab-<?php echo $post['post_id']; ?>" role="tablist">
                                                                <li class="nav-item" role="presentation">
                                                                    <button class="nav-link active" 
                                                                        data-bs-toggle="pill" data-bs-target="#pills-likes-<?php echo $post['post_id']; ?>" 
                                                                        type="button" role="tab">Suka (<?php echo $post['total_likes']; ?>)
                                                                    </button>
                                                                </li>
                                                                <li class="nav-item" role="presentation">
                                                                    <button class="nav-link" 
                                                                        data-bs-toggle="pill" data-bs-target="#pills-comments-<?php echo $post['post_id']; ?>" 
                                                                        type="button" role="tab">Komentar (<?php echo $post['total_comments']; ?>)
                                                                    </button>
                                                                </li>
                                                            </ul>
                                                            <div class="tab-content flex-grow-1 overflow-auto" style="height: 300px;">
                                                                <div class="tab-pane fade show active" id="pills-likes-<?php echo $post['post_id']; ?>" role="tabpanel">
                                                                    <p class="text-center text-muted">Belum ada penyuka</p>
                                                                </div>
                                                                <div class="tab-pane fade" id="pills-comments-<?php echo $post['post_id']; ?>" role="tabpanel">
                                                                    <p class="text-center text-muted">Belum ada komentar</p>
                                                                    <form class="mt-3 border-top pt-2">
                                                                        <input type="text" class="form-control form-control-sm" placeholder="Tambahkan komentar...">
                                                                        <button type="submit" class="btn btn-primary btn-sm mt-2 w-100">Kirim</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                            <button onclick="deletePost(<?php echo $post['post_id']; ?>)" class="btn btn-sm btn-outline-danger">
                                                                <i class="fas fa-trash"></i> Hapus
                                                            </button>
                                                            <button type="button" class="btn btn-sm btn-secondary mt-3" data-bs-dismiss="modal">Tutup</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                                <div class="card-footer bg-transparent border-0 d-flex justify-content-center p-1 small">
                                    <span class="me-3">
                                        <img src="assets/sets/heart.png" alt="heart" class="heart">
                                        <i class="bi bi-heart-fill text-danger me-1"></i> Suka: <?php echo $post['total_likes']; ?>
                                    </span>
                                    <span>
                                        <img src="assets/sets/bubble.png" alt="bubble-chat" class="coment">
                                        <i class="bi bi-chat-dots-fill text-primary me-1"></i> Komentar: <?php echo $post['total_comments']; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <p class="text-muted">Belum ada postingan.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>