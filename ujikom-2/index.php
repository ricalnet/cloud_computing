<?php
// Koneksi ke database
$servername = "localhost";
$username = "user";
$password = "password";
$dbname = "crud_app";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Cek koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Variabel untuk pesan
$message = "";
$message_type = "";

// Handle Create/Update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['create'])) {
        // Create new user
        $nama = $conn->real_escape_string($_POST['nama']);
        $email = $conn->real_escape_string($_POST['email']);
        $umur = intval($_POST['umur']);
        $kota = $conn->real_escape_string($_POST['kota']);
        
        $sql = "INSERT INTO users (nama, email, umur, kota) VALUES ('$nama', '$email', $umur, '$kota')";
        
        if ($conn->query($sql) === TRUE) {
            $message = "User berhasil ditambahkan!";
            $message_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $message_type = "error";
        }
    } elseif (isset($_POST['update'])) {
        // Update user
        $id = intval($_POST['id']);
        $nama = $conn->real_escape_string($_POST['nama']);
        $email = $conn->real_escape_string($_POST['email']);
        $umur = intval($_POST['umur']);
        $kota = $conn->real_escape_string($_POST['kota']);
        
        $sql = "UPDATE users SET nama='$nama', email='$email', umur=$umur, kota='$kota' WHERE id=$id";
        
        if ($conn->query($sql) === TRUE) {
            $message = "User berhasil diupdate!";
            $message_type = "success";
        } else {
            $message = "Error: " . $conn->error;
            $message_type = "error";
        }
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM users WHERE id=$id";
    
    if ($conn->query($sql) === TRUE) {
        $message = "User berhasil dihapus!";
        $message_type = "success";
    } else {
        $message = "Error: " . $conn->error;
        $message_type = "error";
    }
}

// Get user data for editing
$edit_user = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM users WHERE id=$id");
    if ($result->num_rows > 0) {
        $edit_user = $result->fetch_assoc();
    }
}

// Get all users
$result = $conn->query("SELECT * FROM users ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Server - Management Users</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .navbar {
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
            margin-bottom: 20px;
        }
        .card-header {
            border-radius: 10px 10px 0 0 !important;
            font-weight: 600;
        }
        .btn-primary {
            background-color: #4361ee;
            border-color: #4361ee;
        }
        .btn-primary:hover {
            background-color: #3a56d4;
            border-color: #3a56d4;
        }
        .table-responsive {
            border-radius: 10px;
            overflow: hidden;
        }
        .table th {
            background-color: #f1f5fd;
            font-weight: 600;
        }
        .alert {
            border: none;
            border-radius: 8px;
        }
        .info-box {
            background: linear-gradient(135deg, #4361ee, #3a56d4);
            color: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .info-box h5 {
            margin-bottom: 15px;
            font-weight: 600;
        }
        .action-buttons .btn {
            margin-right: 5px;
        }
        .form-control:focus {
            border-color: #4361ee;
            box-shadow: 0 0 0 0.2rem rgba(67, 97, 238, 0.25);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-database me-2"></i>Database CRUD App
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fas fa-home me-1"></i> Home</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Alert Message -->
        <?php if (!empty($message)): ?>
            <div class="alert alert-<?php echo $message_type == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show" role="alert">
                <i class="fas fa-<?php echo $message_type == 'success' ? 'check-circle' : 'exclamation-triangle'; ?> me-2"></i>
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-md-4">
                <!-- Form Card -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-<?php echo $edit_user ? 'edit' : 'user-plus'; ?> me-2"></i>
                        <?php echo $edit_user ? 'Edit User' : 'Tambah User Baru'; ?>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="">
                            <?php if ($edit_user): ?>
                                <input type="hidden" name="id" value="<?php echo $edit_user['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="mb-3">
                                <label for="nama" class="form-label">Nama Lengkap</label>
                                <input type="text" class="form-control" id="nama" name="nama" 
                                       value="<?php echo $edit_user ? $edit_user['nama'] : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo $edit_user ? $edit_user['email'] : ''; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="umur" class="form-label">Umur</label>
                                <input type="number" class="form-control" id="umur" name="umur" 
                                       value="<?php echo $edit_user ? $edit_user['umur'] : ''; ?>" min="1" max="120" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="kota" class="form-label">Kota</label>
                                <input type="text" class="form-control" id="kota" name="kota" 
                                       value="<?php echo $edit_user ? $edit_user['kota'] : ''; ?>" required>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <?php if ($edit_user): ?>
                                    <button type="submit" name="update" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i> Update User
                                    </button>
                                    <a href="index.php" class="btn btn-secondary">
                                        <i class="fas fa-times me-1"></i> Batal
                                    </a>
                                <?php else: ?>
                                    <button type="submit" name="create" class="btn btn-primary">
                                        <i class="fas fa-plus-circle me-1"></i> Tambah User
                                    </button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Database Info Card -->
                <div class="info-box">
                    <h5><i class="fas fa-database me-2"></i>Informasi Database Server</h5>
                    <p><strong>Host:</strong> <?php echo $servername; ?></p>
                    <p><strong>Database:</strong> <?php echo $dbname; ?></p>
                    <p><strong>Status:</strong> 
                        <span class="badge bg-success">
                            <i class="fas fa-check-circle me-1"></i>Connected
                        </span>
                    </p>
                    <p><strong>Total Users:</strong> 
                        <?php 
                            $count_result = $conn->query("SELECT COUNT(*) as total FROM users");
                            $count_data = $count_result->fetch_assoc();
                            echo $count_data['total'];
                        ?>
                    </p>
                </div>
            </div>

            <div class="col-md-8">
                <!-- Users Table Card -->
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-users me-2"></i>Daftar Users
                    </div>
                    <div class="card-body">
                        <?php if ($result->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Umur</th>
                                            <th>Kota</th>
                                            <th>Tanggal Dibuat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['nama']); ?></td>
                                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                                            <td><?php echo $row['umur']; ?></td>
                                            <td><?php echo htmlspecialchars($row['kota']); ?></td>
                                            <td><?php echo date('d M Y', strtotime($row['created_at'])); ?></td>
                                            <td class="action-buttons">
                                                <a href="index.php?edit=<?php echo $row['id']; ?>" class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="index.php?delete=<?php echo $row['id']; ?>" class="btn btn-sm btn-danger" 
                                                   onclick="return confirm('Yakin ingin menghapus user <?php echo htmlspecialchars($row['nama']); ?>?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Belum ada data user</h5>
                                <p class="text-muted">Silakan tambahkan user baru menggunakan form di samping</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="row">
                    <div class="col-md-4">
                        <div class="card bg-info text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h5>Total Users</h5>
                                <h3><?php echo $count_data['total']; ?></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-success text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-database fa-2x mb-2"></i>
                                <h5>Database</h5>
                                <h3>MySQL</h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-warning text-white">
                            <div class="card-body text-center">
                                <i class="fas fa-server fa-2x mb-2"></i>
                                <h5>Server</h5>
                                <h3>AWS EC2</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-database me-2"></i>Self-Managed Database Server</h5>
                    <p>Aplikasi CRUD dengan arsitektur LAMP stack di AWS EC2</p>
                </div>
                <div class="col-md-6 text-end">
                    <p>Powered by RicalNet</p>
                    <p>Deployed on Amazon Web Services EC2</p>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
// Tutup koneksi database
$conn->close();
?>