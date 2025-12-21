<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../controllers/UserController.php';

if (!isset($_SESSION['user_name']) || $_SESSION['role'] !== 'admin') {
  header("Location: ../../auth/login.php");
  exit;
}

$controller = new UserController();
$users = $controller->index(); // ambil semua user
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Management - Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

  <!-- Google Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
      background: #f8fafc;
      min-height: 100vh;
    }

    /* Sidebar */
    .sidebar {
      min-height: 100vh;
      background: linear-gradient(180deg, #1e293b 0%, #0f172a 100%);
      color: #fff;
      padding: 2rem 1.5rem;
      box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
    }

    .sidebar h4 {
      font-weight: 700;
      font-size: 1.5rem;
      margin-bottom: 2rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .sidebar .nav-link {
      color: rgba(255, 255, 255, 0.7);
      padding: 0.875rem 1.25rem;
      margin-bottom: 0.5rem;
      border-radius: 12px;
      transition: all 0.3s ease;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 0.75rem;
    }

    .sidebar .nav-link:hover {
      background: rgba(255, 255, 255, 0.1);
      color: #fff;
      transform: translateX(4px);
    }

    .sidebar .nav-link.active {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .sidebar .nav-link i {
      width: 20px;
      text-align: center;
    }

    /* Main Content */
    .main-content {
      padding: 2.5rem 3rem;
    }

    .page-header {
      margin-bottom: 2.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 2px solid #e2e8f0;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }

    .page-header-left h2 {
      font-weight: 700;
      font-size: 2rem;
      color: #1e293b;
      display: flex;
      align-items: center;
      gap: 0.75rem;
      margin-bottom: 0.5rem;
    }

    .page-header-left h2 i {
      color: #667eea;
    }

    .page-header-left p {
      color: #64748b;
      margin: 0;
    }

    .btn-add-new {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      border: none;
      color: #fff;
      font-weight: 600;
      padding: 0.875rem 1.75rem;
      border-radius: 12px;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-add-new:hover {
      transform: translateY(-2px);
      box-shadow: 0 8px 16px rgba(102, 126, 234, 0.4);
      color: #fff;
    }

    /* Table Card */
    .table-card {
      background: #fff;
      border-radius: 20px;
      padding: 2rem;
      box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }

    .table-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 1.5rem;
      padding-bottom: 1rem;
      border-bottom: 2px solid #e2e8f0;
    }

    .table-header h5 {
      font-weight: 700;
      font-size: 1.25rem;
      color: #1e293b;
      display: flex;
      align-items: center;
      gap: 0.5rem;
      margin: 0;
    }

    .table-header h5 i {
      color: #667eea;
    }

    .count-badge {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
      padding: 0.5rem 1rem;
      border-radius: 12px;
      font-weight: 600;
      font-size: 0.875rem;
    }

    .table {
      margin-bottom: 0;
    }

    .table thead th {
      background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
      color: #475569;
      font-weight: 600;
      border: none;
      padding: 1rem;
      font-size: 0.875rem;
      text-transform: uppercase;
      letter-spacing: 0.5px;
    }

    .table tbody td {
      padding: 1rem;
      vertical-align: middle;
      color: #334155;
      border-bottom: 1px solid #e2e8f0;
    }

    .table tbody tr:last-child td {
      border-bottom: none;
    }

    .table tbody tr {
      transition: all 0.3s ease;
    }

    .table tbody tr:hover {
      background: #f8fafc;
    }

    .user-id {
      font-weight: 700;
      color: #667eea;
      font-size: 0.9375rem;
    }

    .user-name {
      font-weight: 600;
      color: #1e293b;
      font-size: 0.9375rem;
    }

    .user-email {
      color: #64748b;
      font-size: 0.875rem;
    }

    .role-badge {
      display: inline-block;
      padding: 0.375rem 0.875rem;
      border-radius: 8px;
      font-size: 0.8125rem;
      font-weight: 600;
    }

    .role-badge.admin {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
    }

    .role-badge.user {
      background: linear-gradient(135deg, #10b981 0%, #059669 100%);
      color: #fff;
    }

    .btn-edit {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      border: none;
      color: #fff;
      font-weight: 600;
      padding: 0.5rem 1rem;
      border-radius: 10px;
      font-size: 0.875rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
      margin-right: 0.5rem;
    }

    .btn-edit:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
      color: #fff;
    }

    .btn-delete {
      background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
      border: none;
      color: #fff;
      font-weight: 600;
      padding: 0.5rem 1rem;
      border-radius: 10px;
      font-size: 0.875rem;
      transition: all 0.3s ease;
      display: inline-flex;
      align-items: center;
      gap: 0.375rem;
    }

    .btn-delete:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
      color: #fff;
    }

    /* Modal */
    .modal-content {
      border-radius: 20px;
      border: none;
      overflow: hidden;
    }

    .modal-header {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: #fff;
      border: none;
      padding: 1.5rem;
    }

    .modal-header .modal-title {
      font-weight: 700;
      font-size: 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .modal-header .btn-close {
      filter: brightness(0) invert(1);
    }

    .modal-body {
      padding: 2rem;
    }

    .modal-footer {
      border: none;
      padding: 1rem 2rem 2rem;
    }

    .form-label {
      font-weight: 600;
      color: #475569;
      margin-bottom: 0.5rem;
      font-size: 0.875rem;
      display: flex;
      align-items: center;
      gap: 0.375rem;
    }

    .form-control,
    .form-select {
      height: 48px;
      border: 2px solid #e2e8f0;
      border-radius: 12px;
      padding: 0.75rem 1rem;
      font-size: 0.9375rem;
      transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
      border-color: #667eea;
      box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }

    .modal-footer .btn {
      padding: 0.75rem 1.5rem;
      border-radius: 12px;
      font-weight: 600;
      border: none;
    }

    .modal-footer .btn-secondary {
      background: #94a3b8;
    }

    .modal-footer .btn-secondary:hover {
      background: #64748b;
    }

    .modal-footer .btn-primary {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    .modal-footer .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(102, 126, 234, 0.5);
    }

    /* Empty State */
    .empty-state {
      text-align: center;
      padding: 4rem 2rem;
    }

    .empty-state i {
      font-size: 4rem;
      color: #cbd5e1;
      margin-bottom: 1rem;
    }

    .empty-state h5 {
      color: #64748b;
      font-weight: 600;
      margin-bottom: 0.5rem;
    }

    .empty-state p {
      color: #94a3b8;
      margin: 0;
    }

    /* Responsive */
    @media (max-width: 768px) {
      .main-content {
        padding: 1.5rem 1rem;
      }

      .page-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
      }

      .page-header-left h2 {
        font-size: 1.5rem;
      }

      .table-card {
        padding: 1.5rem;
      }

      .table-responsive {
        border-radius: 12px;
        overflow-x: auto;
      }

      .table thead th,
      .table tbody td {
        padding: 0.75rem 0.5rem;
        font-size: 0.8125rem;
      }

      .btn-edit,
      .btn-delete {
        padding: 0.375rem 0.75rem;
        font-size: 0.8125rem;
      }
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
      width: 10px;
      height: 10px;
    }

    ::-webkit-scrollbar-track {
      background: #f1f5f9;
    }

    ::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 5px;
    }

    ::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
    }
  </style>
</head>

<body>
  <div class="d-flex">
    <!-- Sidebar -->
    <div class="sidebar flex-shrink-0" style="width: 280px;">
      <h4><i class="fa-solid fa-shield-halved me-2"></i>Admin Panel</h4>
      <ul class="nav nav-pills flex-column mb-auto">
        <li><a href="/belajar-online/app/views/admin/dashboard.php" class="nav-link"><i class="fa-solid fa-chart-line"></i>Dashboard</a></li>
<li><a href="/belajar-online/app/views/admin/user_management.php" class="nav-link active"><i class="fa-solid fa-users"></i>Manage Users</a></li>
<li><a href="/belajar-online/app/views/admin/playlist_management.php" class="nav-link"><i class="fa-solid fa-layer-group"></i>Manage Playlist</a></li>
<li><a href="/belajar-online/app/views/admin/content_management.php" class="nav-link"><i class="fa-solid fa-film"></i>Manage Content</a></li>
<li><a href="/belajar-online/app/views/admin/jadwal_management.php" class="nav-link"><i class="fa-solid fa-calendar"></i>Manage Jadwal</a></li>
<li><a href="/belajar-online/app/views/admin/reports.php" class="nav-link"><i class="fa-solid fa-chart-bar"></i>Reports</a></li>
<li><a href="/belajar-online/app/controllers/auth_action.php?action=logout" class="nav-link"><i class="fa-solid fa-right-from-bracket"></i>Logout</a></li>

    </div>

    <div class="main-content flex-grow-1">
      <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
          <div class="page-header-left">
            <h2>
              <i class="fa-solid fa-users"></i>
              User Management
            </h2>
            <p>Kelola pengguna dan hak akses sistem</p>
          </div>
          <button class="btn-add-new" data-bs-toggle="modal" data-bs-target="#addUserModal">
            <i class="fa-solid fa-user-plus"></i>
            Add New User
          </button>
        </div>

        <!-- Table Card -->
        <div class="table-card">
          <div class="table-header">
            <h5>
              <i class="fa-solid fa-table"></i>
              Daftar Pengguna
            </h5>
            <span class="count-badge"><?= count($users) ?> users</span>
          </div>

          <?php if (!empty($users)): ?>
            <div class="table-responsive">
              <table class="table">
                <thead>
                  <tr>
                    <th style="width: 80px;"><i class="fa-solid fa-hashtag me-2"></i>ID</th>
                    <th><i class="fa-solid fa-user me-2"></i>Name</th>
                    <th><i class="fa-solid fa-envelope me-2"></i>Email</th>
                    <th style="width: 120px;"><i class="fa-solid fa-shield me-2"></i>Role</th>
                    <th style="width: 200px;"><i class="fa-solid fa-gear me-2"></i>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($users as $user): ?>
                    <tr>
                      <td><span class="user-id">#<?= htmlspecialchars($user['id']) ?></span></td>
                      <td><span class="user-name"><?= htmlspecialchars($user['name']) ?></span></td>
                      <td><span class="user-email"><?= htmlspecialchars($user['email']) ?></span></td>
                      <td>
                        <span class="role-badge <?= htmlspecialchars($user['role']) ?>">
                          <?= htmlspecialchars(ucfirst($user['role'])) ?>
                        </span>
                      </td>
                      <td>
                        <button class="btn-edit"
                          data-bs-toggle="modal"
                          data-bs-target="#editUserModal<?= $user['id'] ?>">
                          <i class="fa-solid fa-pen"></i>
                          Edit
                        </button>
                        <a href="/belajar-online/app/controllers/user_action.php?action=delete&id=<?= $user['id'] ?>" class="btn-delete" onclick="return confirm('Are you sure?')">
    <i class="fa-solid fa-trash"></i>Delete
                        </a>
                      </td>
                    </tr>

                    <!-- Edit Modal -->
                    <div class="modal fade" id="editUserModal<?= $user['id'] ?>" tabindex="-1" aria-hidden="true">
                      <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                          <form action="../../controllers/user_action.php?action=update" method="POST">
                            <div class="modal-header">
                              <h5 class="modal-title">
                                <i class="fa-solid fa-user-pen"></i>
                                Edit User
                              </h5>
                              <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                              <input type="hidden" name="id" value="<?= $user['id'] ?>">
                              <div class="mb-3">
                                <label class="form-label">
                                  <i class="fa-solid fa-user"></i>
                                  Name
                                </label>
                                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">
                                  <i class="fa-solid fa-envelope"></i>
                                  Email
                                </label>
                                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                              </div>
                              <div class="mb-3">
                                <label class="form-label">
                                  <i class="fa-solid fa-shield"></i>
                                  Role
                                </label>
                                <select name="role" class="form-select" required>
                                  <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                  <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                </select>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                              <button type="submit" class="btn btn-primary">
                                <i class="fa-solid fa-floppy-disk me-2"></i>
                                Save Changes
                              </button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="empty-state">
              <i class="fa-solid fa-users-slash"></i>
              <h5>No Users Found</h5>
              <p>Add your first user using the button above</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- Add Modal -->
  <div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form action="/belajar-online/app/controllers/user_action.php?action=store" method="POST">
          <div class="modal-header">
            <h5 class="modal-title">
              <i class="fa-solid fa-user-plus"></i>
              Add New User
            </h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">
                <i class="fa-solid fa-user"></i>
                Name
              </label>
              <input type="text" name="name" class="form-control" placeholder="Enter full name..." required>
            </div>
            <div class="mb-3">
              <label class="form-label">
                <i class="fa-solid fa-envelope"></i>
                Email
              </label>
              <input type="email" name="email" class="form-control" placeholder="Enter email address..." required>
            </div>
            <div class="mb-3">
              <label class="form-label">
                <i class="fa-solid fa-lock"></i>
                Password
              </label>
              <input type="password" name="password" class="form-control" placeholder="Enter password..." required>
            </div>
            <div class="mb-3">
              <label class="form-label">
                <i class="fa-solid fa-shield"></i>
                Role
              </label>
              <select name="role" class="form-select" required>
                <option value="admin">Admin</option>
                <option value="user" selected>User</option>
              </select>
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            <button type="submit" class="btn btn-primary">
              <i class="fa-solid fa-user-plus me-2"></i>
              Add User
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>