<?php
include 'db.php';
include 'header.php';

// Ambil ID dari URL
$id = $_GET['id'];

// Ambil data jadwal berdasarkan ID
$jadwal = $conn->query("SELECT * FROM jadwal WHERE id = $id")->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Ambil data dari form
    $hari = $_POST['hari'];
    $jam = $_POST['jam'];
    $kelas = $_POST['kelas'];
    $mata_pelajaran = $_POST['mata_pelajaran'];

    // Update data ke database
    $conn->query("UPDATE jadwal SET 
        hari = '$hari', 
        jam = '$jam', 
        kelas = '$kelas', 
        mata_pelajaran = '$mata_pelajaran' 
        WHERE id = $id");
    
    // Redirect ke halaman utama
    header("Location: index.php");
    exit;
}
?>

<!-- Header sudah disertakan di atas -->
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
                            <option value="<?= $i ?>" <?= $jadwal['jam'] == $i ? 'selected' : '' ?>>Jam <?= $i ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="kelas" class="form-label">Kelas</label>
                    <input type="text" name="kelas" id="kelas" class="form-control" value="<?= $jadwal['kelas'] ?>" required>
                </div>
                <div class="col-md-6">
                    <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                    <input type="text" name="mata_pelajaran" id="mata_pelajaran" class="form-control" value="<?= $jadwal['mata_pelajaran'] ?>" required>
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