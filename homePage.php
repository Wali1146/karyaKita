<?php include 'header.php'; ?>
<?php
$feed_query = "
SELECT
    p.post_id, p.content_title, p.content_text, p.media_url, p.created_at, p.post_type,
    u.username, u.profile_picture, u.full_name,
    (SELECT COUNT(like_id) FROM likes WHERE post_id = p.post_id) AS total_likes,
    (SELECT COUNT(comment_id) FROM comments WHERE post_id = p.post_id) AS total_comments
FROM posts p
JOIN users u ON p.user_id = u.user_id 
LEFT JOIN follows f ON p.user_id = f.following_id
WHERE f.follower_id = ? OR p.user_id = ?
ORDER BY p.created_at DESC
";

$feed_stmt = $conn->prepare($feed_query);
$feed_stmt->bind_param("ii", $current_user_id, $current_user_id);
$feed_stmt->execute();
$feed_result = $feed_stmt->get_result();
?>

<main class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8 col-md-10">
            <h3>Postingan Terbaru</h3>
            <hr>
            <?php if ($feed_result->num_rows > 0): ?>
                <?php while ($post = $feed_result->fetch_assoc()):
                    $is_video = ($post['post_type'] == 'video');
                    $media_folder = $is_video ? 'vid/' : 'img/';
                    $media_full_path = "assets/post/" . $media_folder . htmlspecialchars($post['media_url']);
                    $profile_pic_path = "assets/post/profile/" . htmlspecialchars($post['profile_picture'] ?? 'default.png');
                ?>
                    <div class="card mb-5 shadow-sm">
                        <div class="card-header d-flex align-items-center bg-white border-0 py-3">
                            <img src="<?php echo $profile_pic_path; ?>"
                                class="rounded-circle me-3"
                                alt="<?php echo htmlspecialchars($post['username']); ?>"
                                style="width: 40px; height: 40px; object-fit: cover;">
                            <div>
                                <h6 class="mb-0"><?php echo htmlspecialchars($post['full_name']); ?></h6>
                                <small class="text-muted"><?php echo date('d M Y', strtotime($post['created_at'])); ?></small>
                            </div>
                        </div>
                        <?php if (!empty($post['media_url'])): ?>
                            <?php if ($is_video): ?>
                                <div class="ratio ratio-16x9 post-media-container video">
                                    <video controls src="<?php echo $media_full_path; ?>" 
                                        class="post-video" muted
                                    >
                                    </video>
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
                                    class="card-img-top"
                                    alt="Gambar Konten Postingan"
                                    style="object-fit: cover;">
                            <?php endif; ?>
                        <?php endif; ?>
                        <div class="card-body py-2">
                            <div class="d-flex gap-3">
                                <button class="btn btn-sm text-secondary p-0 me-2 d-flex flex-column align-items-center" title="Suka">
                                    <img src="assets/sets/heart.png" alt="heart" class="heart">
                                    <i class="bi bi-heart-fill"></i> Suka <br>
                                    <?php echo $post['total_likes']; ?>
                                </button>
                                <button class="btn btn-sm text-secondary p-0 me-2 d-flex flex-column align-items-center" title="Komentar">
                                    <img src="assets/sets/bubble.png" alt="bubble-chat" class="coment">
                                    <i class="bi bi-chat-dots"></i> Komentar <br>
                                    <?php echo $post['total_comments']; ?>
                                </button>
                                <button class="btn btn-sm text-secondary p-0 d-flex flex-column align-items-center" title="Berbagi">
                                    <img src="assets/sets/send.png" alt="send" class="share">
                                    <i class="bi bi-share"></i> Berbagi
                                </button>
                            </div>
                        </div>
                        <div class="card-body pt-0">
                            <h5 class="card-title mb-1"><?php echo htmlspecialchars($post['content_title']); ?></h5>
                            <p class="card-text">
                                <?php echo nl2br(htmlspecialchars($post['content_text'])); ?>
                            </p>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-muted">Tidak ada postingan terbaru. Ikuti pengguna lain untuk melihat konten mereka di sini.</p>
            <?php endif; ?>
        </div>
    </div>
</main>
<?php include 'footer.php'; ?>