<?php
session_start();
include '../config/database.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}

$current_user = $_SESSION['user_id'];
$userdata_query = "SELECT username, full_name, profile_picture FROM users WHERE user_id = ?";
$userdata_stmt = $conn->prepare($userdata_query);
$userdata_stmt->bind_param("i", $current_user);
$userdata_stmt->execute();
$userdata_result = $userdata_stmt->get_result();
$current_user = $userdata_result->fetch_assoc();

$sql_users = "SELECT COUNT(user_id) AS total_users FROM users";
$result_users = $conn->query($sql_users);
$kpi_users = $result_users->fetch_assoc();
$total_users = $kpi_users['total_users'];

$sql_reports = "SELECT COUNT(report_id) AS pending_reports FROM reports WHERE status = 'pending'";
$result_reports = $conn->query($sql_reports);
$kpi_reports = $result_reports->fetch_assoc();
$pending_reports = $kpi_reports['pending_reports'];

$sql_recent_posts = "SELECT COUNT(post_id) AS recent_posts FROM posts WHERE created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY)";
$result_recent_posts = $conn->query($sql_recent_posts);
$kpi_recent_posts = $result_recent_posts->fetch_assoc();
$recent_posts = $kpi_recent_posts['recent_posts'];

$sql_flagged = "
SELECT 
    r.report_id, r.reason, r.created_at AS date, r.status, 
    u.username, p.post_id 
FROM reports r
JOIN users u ON r.reported_by = u.user_id
JOIN posts p ON r.post_id = p.post_id
ORDER BY r.created_at DESC
LIMIT 10
";

$flagged_result = $conn->query($sql_flagged);

if (!$flagged_result) {
    die("Error SQL pada flagged posts: " . $conn->error);
}

$sql_accounts = "
SELECT 
    user_id, username, email, 
    created_at AS join_date, status 
FROM users
ORDER BY created_at DESC
LIMIT 10
";

$accounts_result = $conn->query($sql_accounts);

if (!$accounts_result) {
    die("Error SQL pada User Accounts: " . $conn->error);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Admin Index | Karya Kita</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" 
        xintegrity="sha512-SnH5WK+bZxgPHs44uWIX+LLMDJdZ4w4Xy6gQ7xE/z/QjD/sI+hA3mB4+dGjWp/g9g09Xl3Qy/uDqWz4e7tO3w==" 
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <header>
        <nav class="navbar navbar-expand-lg navbar-light bg-light shadow-sm mb-3 fixed-top">
            <div class="container-fluid px-4 px-lg-5 gap-3">
                <div class="navbar-brand">
                    <a href="homePage.php" class="d-flex align-items-center text-decoration-none text-primary gap-2">
                        <img src="../assets/sets/canvas.png" alt="logo" class="logo">
                        <h1>Karya Kita</h1>
                    </a>
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
                                $profile_pic_path = "../assets/post/profile/" . htmlspecialchars($profile_pic);
                            ?>
                            <img src="<?php echo $profile_pic_path; ?>" 
                                class="rounded-circle me-2" height="30" alt="Avatar"
                            />
                            <span class="d-none d-sm-block">Halo, <?php echo htmlspecialchars($current_user['username']); ?></span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdownMenuLink">
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../logout.php">Keluar</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </nav>
    </header>
    <main class="container-fluid px-4 px-lg-5">
        <h2 class="h3 fw-bold pt-4 mb-4 mt-5">Admin Dashboard Overview</h2>
        <div class="row g-4 mb-5">
            <div class="col-md-4 col-sm-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <p class="text-uppercase text-muted mb-1 small fw-semibold">Total Pengguna</p>
                            <h3 class="h2 fw-bold mb-0 text-dark"><?php echo number_format($total_users); ?></h3>
                        </div>
                        <i class="fas fa-users card-icon"></i>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 pb-2">
                        <a href="#user-accounts" class="small text-decoration-none text-primary">Lihat Detail Akun <i class="fas fa-arrow-right small ms-1"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <p class="text-uppercase text-muted mb-1 small fw-semibold">Laporan Tertunda</p>
                            <h3 class="h2 fw-bold mb-0 text-danger"><?php echo $pending_reports; ?></h3>
                        </div>
                        <i class="fas fa-flag card-icon text-danger"></i>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 pb-2">
                        <a href="#flagged-posts" class="small text-decoration-none text-danger">Tinjau Laporan <i class="fas fa-arrow-right small ms-1"></i></a>
                    </div>
                </div>
            </div>
            <div class="col-md-4 col-sm-6">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-body d-flex justify-content-between align-items-center p-4">
                        <div>
                            <p class="text-uppercase text-muted mb-1 small fw-semibold">Postingan Terbaru (24 Jam)</p>
                            <h3 class="h2 fw-bold mb-0 text-dark"><?php echo $recent_posts; ?></h3>
                        </div>
                        <i class="fas fa-upload card-icon"></i>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 pb-2">
                         <span class="small text-muted">Aktivitas konten terbaru</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="row g-4">
            <div class="col-12" id="flagged-posts">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="h5 card-title fw-bold mb-3 text-danger">
                            <i class="fas fa-exclamation-triangle me-2"></i> Postingan Dilaporkan (Flagged Posts)
                        </h3>      
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="bg-light text-uppercase small">
                                    <tr>
                                        <th scope="col">Post ID</th>
                                        <th scope="col">User</th>
                                        <th scope="col">Alasan</th>
                                        <th scope="col">Tanggal Lapor</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($flagged_result->num_rows > 0): ?>
                                        <?php while ($report = $flagged_result->fetch_assoc()): ?>
                                            <?php 
                                                $badge_class = ($report['status'] == 'pending') ? 'status-pending' : 'status-resolved';
                                                $action_text = ($report['status'] == 'pending') ? 'Tinjau' : 'Detail';
                                                $action_color = ($report['status'] == 'pending') ? 'btn-outline-primary' : 'btn-outline-secondary';
                                            ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($report['post_id']); ?></td>
                                                <td><?php echo htmlspecialchars($report['username']); ?></td>
                                                <td><?php echo htmlspecialchars($report['reason']); ?></td>
                                                <td><?php echo date("Y-m-d", strtotime($report['date'])); ?></td>
                                                <td>
                                                    <span class="badge rounded-pill <?php echo $badge_class; ?> fw-medium">
                                                        <?php echo ucfirst(htmlspecialchars($report['status'])); ?>
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a class="btn btn-sm <?php echo $action_color; ?>" 
                                                        href="tinjau_post.php?id=<?php echo $report['post_id']; ?>">
                                                        <?php echo $action_text; ?>
                                                        <?php if ($report['status'] == 'pending'): ?>
                                                            <i class="fas fa-chevron-right small ms-1"></i>
                                                        <?php endif; ?>
                                                    </a>
                                                    <button onclick="deletePost(<?php echo $report['post_id']; ?>, true)" class="btn btn-sm btn-danger">
                                                        Hapus Permanen
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-success fw-medium">🥳 Tidak ada laporan tertunda!</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 
            <div class="col-12" id="user-accounts">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h3 class="h5 card-title fw-bold mb-3 text-primary">
                            <i class="fas fa-user-cog me-2"></i> Manajemen Akun Pengguna
                        </h3>      
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="bg-light text-uppercase small">
                                    <tr>
                                        <th scope="col">User ID</th>
                                        <th scope="col">Username</th>
                                        <th scope="col">Email</th>
                                        <th scope="col">Tanggal Gabung</th>
                                        <th scope="col">Status</th>
                                        <th scope="col" class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($accounts_result->num_rows > 0): ?>
                                        <?php while ($user = $accounts_result->fetch_assoc()): ?>
                                            <?php 
                                                $status_text = ucfirst(htmlspecialchars($user['status']));
                                                $badge_color = ($user['status'] == 'active') ? 'text-bg-success' : 'status-suspended';
                                                $action_text = ($user['status'] == 'active') ? 'Kelola' : 'Aktivasi';
                                                $action_color = ($user['status'] == 'active') ? 'btn-outline-secondary' : 'btn-outline-danger';
                                            ?>
                                            <tr>
                                                <td>#<?php echo htmlspecialchars($user['user_id']); ?></td>
                                                <td><?php echo htmlspecialchars($user['username']); ?></td>
                                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                                <td><?php echo date("Y-m-d", strtotime($user['join_date'])); ?></td>
                                                <td><span class="badge rounded-pill <?php echo $badge_color; ?> fw-medium"><?php echo $status_text; ?></span></td>
                                                <td class="text-end">
                                                    <a class="btn btn-sm <?php echo $action_color; ?>" href="manage_user.php?id=<?php echo $user['user_id']; ?>">
                                                        <?php echo $action_text; ?>
                                                    </a>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">Belum ada data pengguna yang terdaftar.</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div> 
        </div>
    </main>
<?php include '../footer.php'; ?>
