<?php
include 'db.php';

// Proses filter by kelas jika ada
$filter_kelas = isset($_GET['filter_kelas']) ? $_GET['filter_kelas'] : '';
$where_clause = $filter_kelas ? "WHERE kelas = '".$conn->real_escape_string($filter_kelas)."'" : '';

// Proses bulk delete jika ada
if (isset($_POST['bulk_delete']) && isset($_POST['selected_ids'])) {
    $selected_ids = $_POST['selected_ids'];
    $id_count = count($selected_ids);
    
    if ($id_count > 0) {
        // Membuat string ID yang aman untuk query
        $ids = implode(',', array_map('intval', $selected_ids));
        $delete_query = "DELETE FROM jadwal WHERE id IN ($ids)";
        
        if ($conn->query($delete_query)) {
            $success_message = "Berhasil menghapus $id_count jadwal";
        } else {
            $error_message = "Gagal menghapus jadwal: " . $conn->error;
        }
    }
}

// Tampilkan pesan sukses dari bulk add jika ada
if (isset($_GET['success'])) {
    $success_count = intval($_GET['success']);
    $success_message = "Berhasil menambahkan $success_count jadwal baru";
}

// Query data dengan filter jika ada
$result = $conn->query("SELECT * FROM jadwal $where_clause ORDER BY hari, jam, kelas");

// Ambil daftar kelas unik untuk dropdown filter
$kelas_result = $conn->query("SELECT DISTINCT kelas FROM jadwal ORDER BY kelas");
$kelas_options = [];
while ($row = $kelas_result->fetch_assoc()) {
    $kelas_options[] = $row['kelas'];
}
?>

<!-- Gunakan header.php yang sudah ada -->
<?php include 'header.php'; ?>
    <div class="container my-5">
        <?php if (isset($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?= $success_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?= $error_message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <div class="action-buttons mb-3">
            <a href="add.php" class="btn btn-primary">Tambah Jadwal</a>
            <a href="bulk_add.php" class="btn btn-success">Tambah Jadwal Massal</a>
            <button id="toggleDelete" class="btn btn-danger">Mode Hapus Massal</button>
        </div>
        
        <!-- Form Filter -->
        <div class="row mb-3">
            <div class="col-md-4">
                <form method="get" class="d-flex">
                    <select name="filter_kelas" class="form-select me-2">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelas_options as $kelas): ?>
                            <option value="<?= htmlspecialchars($kelas) ?>" <?= $filter_kelas == $kelas ? 'selected' : '' ?>>
                                <?= htmlspecialchars($kelas) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-info">Filter</button>
                    <?php if ($filter_kelas): ?>
                        <a href="crud_jadwal.php" class="btn btn-outline-secondary ms-2">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
        
        <form id="bulkDeleteForm" method="post" style="display:none;">
            <div class="mb-3">
                <button type="submit" name="bulk_delete" class="btn btn-danger" id="confirmDelete" disabled>
                    Hapus Jadwal Terpilih (<span id="selectedCount">0</span>)
                </button>
                <button type="button" class="btn btn-secondary" id="cancelDelete">Batal</button>
            </div>
            
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th class="checkbox-column">
                            <input type="checkbox" id="selectAll" class="form-check-input">
                        </th>
                        <th>No</th>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td class='text-center'>
                                    <input type='checkbox' name='selected_ids[]' value='{$row['id']}' class='form-check-input jadwal-checkbox'>
                                </td>
                                <td>{$no}</td>
                                <td>{$row['hari']}</td>
                                <td>Jam {$row['jam']}</td>
                                <td>{$row['kelas']}</td>
                                <td>{$row['mata_pelajaran']}</td>
                              </tr>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </form>
        
        <div id="normalView">
            <table class="table table-bordered table-hover">
                <thead class="table-primary">
                    <tr>
                        <th>No</th>
                        <th>Hari</th>
                        <th>Jam</th>
                        <th>Kelas</th>
                        <th>Mata Pelajaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Reset result pointer
                    $result->data_seek(0);
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>
                                <td>{$no}</td>
                                <td>{$row['hari']}</td>
                                <td>Jam {$row['jam']}</td>
                                <td>{$row['kelas']}</td>
                                <td>{$row['mata_pelajaran']}</td>
                                <td>
                                    <a href='edit.php?id={$row['id']}' class='btn btn-warning btn-sm'>Edit</a>
                                    <a href='delete.php?id={$row['id']}' class='btn btn-danger btn-sm' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a>
                                </td>
                              </tr>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <footer class="text-center py-3">
        <small>&copy; <?= date('Y') ?> Menuntut Ilmu. Semua Hak Dilindungi.</small>
    </footer>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleDeleteBtn = document.getElementById('toggleDelete');
            const bulkDeleteForm = document.getElementById('bulkDeleteForm');
            const normalView = document.getElementById('normalView');
            const cancelDeleteBtn = document.getElementById('cancelDelete');
            const selectAllCheckbox = document.getElementById('selectAll');
            const confirmDeleteBtn = document.getElementById('confirmDelete');
            const selectedCountSpan = document.getElementById('selectedCount');
            const checkboxes = document.querySelectorAll('.jadwal-checkbox');
            
            // Toggle antara tampilan normal dan mode hapus massal
            toggleDeleteBtn.addEventListener('click', function() {
                const filterParam = new URLSearchParams(window.location.search).get('filter_kelas');
                if (filterParam) {
                    bulkDeleteForm.action = `?filter_kelas=${encodeURIComponent(filterParam)}`;
                }
                bulkDeleteForm.style.display = 'block';
                normalView.style.display = 'none';
                toggleDeleteBtn.style.display = 'none';
            });
            
            // Kembali ke tampilan normal
            cancelDeleteBtn.addEventListener('click', function() {
                bulkDeleteForm.style.display = 'none';
                normalView.style.display = 'block';
                toggleDeleteBtn.style.display = 'inline-block';
                // Reset semua checkbox
                selectAllCheckbox.checked = false;
                checkboxes.forEach(checkbox => checkbox.checked = false);
                updateSelectedCount();
            });
            
            // Select/deselect semua checkbox
            selectAllCheckbox.addEventListener('change', function() {
                checkboxes.forEach(checkbox => checkbox.checked = this.checked);
                updateSelectedCount();
            });
            
            // Update jumlah item yang dipilih dan status tombol hapus
            checkboxes.forEach(checkbox => {
                checkbox.addEventListener('change', updateSelectedCount);
            });
            
            function updateSelectedCount() {
                const selectedCount = document.querySelectorAll('.jadwal-checkbox:checked').length;
                selectedCountSpan.textContent = selectedCount;
                confirmDeleteBtn.disabled = selectedCount === 0;
            }
            
            // Konfirmasi sebelum menghapus
            bulkDeleteForm.addEventListener('submit', function(e) {
                const selectedCount = document.querySelectorAll('.jadwal-checkbox:checked').length;
                if (!confirm(`Yakin ingin menghapus ${selectedCount} jadwal terpilih?`)) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>