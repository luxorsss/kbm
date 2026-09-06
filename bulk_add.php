<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['submit_bulk'])) {
        // Mendapatkan data dari textarea
        $bulk_data = trim($_POST['bulk_data']);
        $lines = explode("\n", $bulk_data);
        
        // Menyimpan data ke database
        $success = 0;
        foreach ($lines as $line) {
            // Hanya proses jika baris tidak kosong
            $line = trim($line);
            if (!empty($line)) {
                // Memisahkan data berdasarkan koma
                $data = array_map('trim', explode(',', $line));
                
                // Pastikan ada 4 elemen (hari, jam, kelas, mata pelajaran)
                if (count($data) >= 4) {
                    $hari = $conn->real_escape_string($data[0]);
                    $jam = $conn->real_escape_string($data[1]);
                    $kelas = $conn->real_escape_string($data[2]);
                    $mata_pelajaran = $conn->real_escape_string($data[3]);
                    
                    $sql = "INSERT INTO jadwal (hari, jam, kelas, mata_pelajaran) VALUES ('$hari', '$jam', '$kelas', '$mata_pelajaran')";
                    
                    if ($conn->query($sql)) {
                        $success++;
                    }
                }
            }
        }
        
        // Redirect dengan pesan sukses
        header("Location: crud_jadwal.php?success=$success");
        exit;
    }
}
?>

<?php include 'header.php'; ?>
    
    <div class="header">
        <h1>Tambah Jadwal Massal</h1>
        <p>"Efisiensi waktu untuk hasil maksimal"</p>
    </div>
    
    <div class="container my-5">
        <div class="form-container shadow-sm">
            <div class="format-example">
                <h5>Format Input:</h5>
                <p>Masukkan data jadwal dengan format: <strong>Hari, Jam, Kelas, Mata Pelajaran</strong> (satu jadwal per baris)</p>
                <pre>Contoh:
Senin, 1, 10B, IPA
Senin, 1, 10A, IPA
Selasa, 3, 11C, Matematika</pre>
            </div>
            
            <form method="post" id="bulkForm">
                <div class="mb-3">
                    <label for="bulk_data" class="form-label">Data Jadwal</label>
                    <textarea name="bulk_data" id="bulk_data" class="form-control" rows="10" placeholder="Masukkan data jadwal di sini..." required></textarea>
                </div>
                
                <div class="row mt-3">
                    <div class="col-12">
                        <button type="submit" name="submit_bulk" class="btn btn-success">Simpan Semua</button>
                        <a href="crud_jadwal.php" class="btn btn-secondary">Kembali</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <footer class="text-center py-3">
        <small>&copy; <?= date('Y') ?> Menuntut Ilmu. Semua Hak Dilindungi.</small>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Validasi form sebelum submit
            document.getElementById('bulkForm').addEventListener('submit', function(e) {
                const bulkData = document.getElementById('bulk_data').value.trim();
                
                if (!bulkData) {
                    e.preventDefault();
                    alert('Silakan masukkan data jadwal!');
                    return;
                }
                
                const lines = bulkData.split('\n');
                let valid = false;
                
                for (const line of lines) {
                    const trimmedLine = line.trim();
                    if (trimmedLine && trimmedLine.split(',').length >= 4) {
                        valid = true;
                        break;
                    }
                }
                
                if (!valid) {
                    e.preventDefault();
                    alert('Format data tidak valid! Pastikan setiap baris memiliki format: Hari, Jam, Kelas, Mata Pelajaran');
                }
            });
        });
    </script>
</body>
</html>