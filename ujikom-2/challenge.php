<?php
// Koneksi database
$servername = "localhost";
$username = "";
$password = "";
$dbname = "db_";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle actions
$message = "";
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $nama = $conn->real_escape_string($_POST['nama']);
    $email = $conn->real_escape_string($_POST['email']);
    $umur = intval($_POST['umur']);
    $kota = $conn->real_escape_string($_POST['kota']);
    
    if (isset($_POST['create'])) {
        $sql = "INSERT INTO table_asprak (nama, email, umur, kota) VALUES ('$nama', '$email', $umur, '$kota')";
        $message = $conn->query($sql) ? "User berhasil ditambahkan!" : "Error: " . $conn->error;
    } elseif (isset($_POST['update'])) {
        $sql = "UPDATE table_asprak SET nama='$nama', email='$email', umur=$umur, kota='$kota' WHERE id=$id";
        $message = $conn->query($sql) ? "User berhasil diupdate!" : "Error: " . $conn->error;
    }
}

if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM table_asprak WHERE id=$id";
    $message = $conn->query($sql) ? "User berhasil dihapus!" : "Error: " . $conn->error;
}

// Get data
$edit_user = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM table_asprak WHERE id=$id");
    if ($result->num_rows > 0) $edit_user = $result->fetch_assoc();
}

$users = $conn->query("SELECT * FROM table_asprak ORDER BY created_at DESC");
$total_users = $conn->query("SELECT COUNT(*) as total FROM table_asprak")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Database Server - Modul Praktikum</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .server-info { background: #f8f9fa; border-left: 4px solid #0d6efd; }
        .db-stats { font-size: 0.9rem; }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-4">
        <!-- Header -->
        <div class="row mb-4">
            <div class="col">
                <h1 class="text-primary">Database Server</h1>
                <p class="text-muted">Modul Praktikum - Management Database dan Tabel</p>
            </div>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-info"><?= $message ?></div>
        <?php endif; ?>

        <div class="row">
            <!-- Database Information -->
            <div class="col-md-3">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <i class="fas fa-database"></i> Server Info
                    </div>
                    <div class="card-body server-info">
                        <p><strong>Host:</strong> <?= $servername ?></p>
                        <p><strong>Database:</strong> <?= $dbname ?></p>
                        <p><strong>Status:</strong> 
                            <span class="badge bg-success">Connected</span>
                        </p>
                        <p><strong>Total Records:</strong> <?= $total_users ?></p>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <i class="fas fa-table"></i> Table Info
                    </div>
                    <div class="card-body db-stats">
                        <p><strong>Table Name:</strong> table_asprak</p>
                        <p><strong>Columns:</strong> 6 fields</p>
                        <p><strong>Operations:</strong> CRUD Ready</p>
                        <p><strong>Engine:</strong> MySQL</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <?= $edit_user ? 'Edit User' : 'Tambah Data Baru' ?>
                            </div>
                            <div class="card-body">
                                <form method="POST">
                                    <?php if ($edit_user): ?>
                                        <input type="hidden" name="id" value="<?= $edit_user['id'] ?>">
                                    <?php endif; ?>
                                    
                                    <div class="mb-2">
                                        <label class="form-label">Nama</label>
                                        <input type="text" class="form-control" name="nama" 
                                               value="<?= $edit_user ? $edit_user['nama'] : '' ?>" required>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email"
                                               value="<?= $edit_user ? $edit_user['email'] : '' ?>" required>
                                    </div>
                                    
                                    <div class="row mb-2">
                                        <div class="col">
                                            <label class="form-label">Umur</label>
                                            <input type="number" class="form-control" name="umur"
                                                   value="<?= $edit_user ? $edit_user['umur'] : '' ?>" required>
                                        </div>
                                        <div class="col">
                                            <label class="form-label">Kota</label>
                                            <input type="text" class="form-control" name="kota"
                                                   value="<?= $edit_user ? $edit_user['kota'] : '' ?>" required>
                                        </div>
                                    </div>
                                    
                                    <?php if ($edit_user): ?>
                                        <button type="submit" name="update" class="btn btn-warning w-100">Update Data</button>
                                        <a href="?" class="btn btn-secondary w-100 mt-2">Batal Edit</a>
                                    <?php else: ?>
                                        <button type="submit" name="create" class="btn btn-success w-100">Simpan ke Database</button>
                                    <?php endif; ?>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-header bg-info text-white">
                                Database Statistics
                            </div>
                            <div class="card-body">
                                <div class="text-center">
                                    <h3><?= $total_users ?></h3>
                                    <p class="text-muted">Total Records</p>
                                </div>
                                <hr>
                                <small class="text-muted">
                                    <strong>Last Operation:</strong> 
                                    <?= $message ?: 'Ready' ?>
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table -->
                <div class="card mt-4">
                    <div class="card-header bg-dark text-white">
                        <i class="fas fa-table"></i> Data Table: table_asprak
                    </div>
                    <div class="card-body">
                        <?php if ($users->num_rows > 0): ?>
                            <div class="table-responsive">
                                <table class="table table-striped table-sm">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Nama</th>
                                            <th>Email</th>
                                            <th>Umur</th>
                                            <th>Kota</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while($row = $users->fetch_assoc()): ?>
                                        <tr>
                                            <td><?= $row['id'] ?></td>
                                            <td><?= htmlspecialchars($row['nama']) ?></td>
                                            <td><?= htmlspecialchars($row['email']) ?></td>
                                            <td><?= $row['umur'] ?></td>
                                            <td><?= htmlspecialchars($row['kota']) ?></td>
                                            <td>
                                                <a href="?edit=<?= $row['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                                <a href="?delete=<?= $row['id'] ?>" class="btn btn-sm btn-outline-danger" 
                                                   onclick="return confirm('Hapus data <?= htmlspecialchars($row['nama']) ?>?')">Delete</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted text-center">Database table is empty</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="row mt-4">
            <div class="col">
                <p class="text-center text-muted small">
                    Created by Asprak CC &copy; <?= date('Y') ?>
                </p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php $conn->close(); ?>