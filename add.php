<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hari = $_POST['hari'];
    $jam = (int)$_POST['jam'];
    $kelas = $_POST['kelas'];
    $mata_pelajaran = $_POST['mata_pelajaran'];

    $stmt = $conn->prepare("INSERT INTO jadwal (hari, jam, kelas, mata_pelajaran) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $hari, $jam, $kelas, $mata_pelajaran);
    $stmt->execute();
    $stmt->close();

    header("Location: index.php");
    exit;
}
?>

<?php include 'header.php'; ?>
    <div class="header">
        <h1>Tambah Jadwal</h1>
        <p>"Setiap detik belajar adalah investasi masa depan"</p>
    </div>
    <div class="container my-5">
        <div class="form-container shadow-sm">
            <form method="post" class="row g-3">
                <div class="col-md-6">
                    <label for="hari" class="form-label">Hari</label>
                    <select name="hari" id="hari" class="form-select" required>
                        <option value="Senin">Senin</option>
                        <option value="Selasa">Selasa</option>
                        <option value="Rabu">Rabu</option>
                        <option value="Kamis">Kamis</option>
                        <option value="Jumat">Jumat</option>
                        <option value="Sabtu">Sabtu</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="jam" class="form-label">Jam</label>
                    <select name="jam" id="jam" class="form-select" required>
                        <?php for ($i = 1; $i <= 6; $i++) { ?>
                            <option value="<?= $i ?>">Jam <?= $i ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="kelas" class="form-label">Kelas</label>
                    <input type="text" name="kelas" id="kelas" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="mata_pelajaran" class="form-label">Mata Pelajaran</label>
                    <input type="text" name="mata_pelajaran" id="mata_pelajaran" class="form-control" required>
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
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>