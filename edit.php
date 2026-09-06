<?php
include 'db.php';

// 1. Ambil ID dari parameter GET
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 2. Tangani request POST sebelum ada output HTML
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hari = $_POST['hari'];
    $jam = $_POST['jam'];
    $kelas = $_POST['kelas'];
    $mata_pelajaran = $_POST['mata_pelajaran'];

    // Update menggunakan prepared statement
    $stmt = $conn->prepare("UPDATE jadwal SET hari = ?, jam = ?, kelas = ?, mata_pelajaran = ? WHERE id = ?");
    $stmt->bind_param("sissi", $hari, $jam, $kelas, $mata_pelajaran, $id);
    $stmt->execute();
    $stmt->close();
    
    // Redirect aman tanpa terhalang output HTML
    header("Location: index.php");
    exit;
}

// 3. Ambil data jadwal untuk form
$stmt = $conn->prepare("SELECT * FROM jadwal WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$jadwal = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$jadwal) {
    header("Location: index.php");
    exit;
}

// 4. Baru sertakan header setelah semua operasi redirect selesai
include 'header.php';
?>

<div class="container my-5">
    <div class="form-container shadow-sm">
        <form method="post" class="row g-3">
            <div class="col-md-6">
                <label for="hari" class="form-label">Hari</label>
                <select name="hari" id="hari" class="form-select" required>
                    <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $day) { ?>
                        <option value="<?= $day ?>" <?= $jadwal['hari'] === $day ? 'selected' : '' ?>><?= $day ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="jam" class="form-label">Jam</label>
                <select name="jam" id="jam" class="form-select" required>
                    <?php for ($i = 1; $i <= 6; $i++) { ?>
                        <option value="<?= $i ?>" <?= (int)$jadwal['jam'] === $i ? 'selected' : '' ?>>Jam <?= $i ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-6">
                <label for="kelas" class="form-label">Kelas</label>
                <input type="text" name="kelas" id="kelas" class="form-control" value="<?= htmlspecialchars($jadwal['kelas']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                <input type="text" name="mata_pelajaran" id="mata_pelajaran" class="form-control" value="<?= htmlspecialchars($jadwal['mata_pelajaran']) ?>" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-success">Simpan</button>
                <a href="index.php" class="btn btn-secondary">Kembali</a>
            </div>
        </form>
    </div>
</div>
<footer class="text-center py-3">
    <small>&copy; <?= date('Y') ?> Menuntut Ilmu. Semua Hak Dilindungi.</small>
</footer>
</body>
</html>