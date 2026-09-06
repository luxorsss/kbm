<?php
include 'db.php';
include 'header.php';

// Buat tabel batas_ajar jika belum ada
$create_table = "CREATE TABLE IF NOT EXISTS batas_ajar (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kelas VARCHAR(10) NOT NULL,
    mata_pelajaran VARCHAR(100) NOT NULL,
    tanggal DATE NOT NULL,
    batas_ajar TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
$conn->query($create_table);

// Proses form submission
if ($_POST) {
    $action = $_POST['action'];
    
    if ($action == 'add') {
        $kelas = $_POST['kelas'];
        $mata_pelajaran = $_POST['mata_pelajaran'];
        $tanggal = $_POST['tanggal'];
        $batas_ajar = $_POST['batas_ajar'];
        
        $stmt = $conn->prepare("INSERT INTO batas_ajar (kelas, mata_pelajaran, tanggal, batas_ajar) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $kelas, $mata_pelajaran, $tanggal, $batas_ajar);
        
        if ($stmt->execute()) {
            $success_message = "Batas ajar berhasil ditambahkan!";
        } else {
            $error_message = "Gagal menambahkan batas ajar: " . $conn->error;
        }
    }
    
    if ($action == 'edit') {
        $id = $_POST['id'];
        $kelas = $_POST['kelas'];
        $mata_pelajaran = $_POST['mata_pelajaran'];
        $tanggal = $_POST['tanggal'];
        $batas_ajar = $_POST['batas_ajar'];
        
        $stmt = $conn->prepare("UPDATE batas_ajar SET kelas=?, mata_pelajaran=?, tanggal=?, batas_ajar=? WHERE id=?");
        $stmt->bind_param("ssssi", $kelas, $mata_pelajaran, $tanggal, $batas_ajar, $id);
        
        if ($stmt->execute()) {
            $success_message = "Batas ajar berhasil diperbarui!";
        } else {
            $error_message = "Gagal memperbarui batas ajar: " . $conn->error;
        }
    }
    
    if ($action == 'delete') {
        $id = $_POST['id'];
        
        $stmt = $conn->prepare("DELETE FROM batas_ajar WHERE id=?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $success_message = "Batas ajar berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus batas ajar: " . $conn->error;
        }
    }
    
    if ($action == 'delete_all') {
        $result = $conn->query("DELETE FROM batas_ajar");
        
        if ($result) {
            $success_message = "Semua batas ajar berhasil dihapus!";
        } else {
            $error_message = "Gagal menghapus semua batas ajar: " . $conn->error;
        }
    }
    
    if ($action == 'bulk_add') {
        $kelas = $_POST['bulk_kelas'];
        $mata_pelajaran = $_POST['bulk_mata_pelajaran'];
        $bulk_input = $_POST['bulk_input'];
        
        $success_count = 0;
        $error_count = 0;
        $lines = explode("\n", trim($bulk_input));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;
            
            // Parse format: DD/MM/YY, Batas Ajar
            $parts = explode(',', $line, 2);
            if (count($parts) == 2) {
                $tanggal_input = trim($parts[0]);
                $batas_ajar = trim($parts[1]);
                
                // Convert DD/MM/YY to YYYY-MM-DD
                $date_parts = explode('/', $tanggal_input);
                if (count($date_parts) == 3) {
                    $day = str_pad($date_parts[0], 2, '0', STR_PAD_LEFT);
                    $month = str_pad($date_parts[1], 2, '0', STR_PAD_LEFT);
                    $year = $date_parts[2];
                    
                    // Handle 2-digit year (assume 20xx)
                    if (strlen($year) == 2) {
                        $year = '20' . $year;
                    }
                    
                    $tanggal = "$year-$month-$day";
                    
                    // Validate date
                    if (checkdate($month, $day, $year) && !empty($batas_ajar)) {
                        $stmt = $conn->prepare("INSERT INTO batas_ajar (kelas, mata_pelajaran, tanggal, batas_ajar) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param("ssss", $kelas, $mata_pelajaran, $tanggal, $batas_ajar);
                        
                        if ($stmt->execute()) {
                            $success_count++;
                        } else {
                            $error_count++;
                        }
                    } else {
                        $error_count++;
                    }
                } else {
                    $error_count++;
                }
            } else {
                $error_count++;
            }
        }
        
        if ($success_count > 0) {
            $success_message = "Berhasil menambahkan $success_count batas ajar!";
            if ($error_count > 0) {
                $success_message .= " ($error_count baris gagal/diabaikan)";
            }
        } else {
            $error_message = "Gagal menambahkan batas ajar! Periksa format input Anda.";
        }
    }
}

// Ambil data untuk dropdown
$kelas_options = [];
$mata_pelajaran_options = [];

$result = $conn->query("SELECT DISTINCT kelas FROM jadwal ORDER BY kelas");
while ($row = $result->fetch_assoc()) {
    $kelas_options[] = $row['kelas'];
}

$result = $conn->query("SELECT DISTINCT mata_pelajaran FROM jadwal ORDER BY mata_pelajaran");
while ($row = $result->fetch_assoc()) {
    $mata_pelajaran_options[] = $row['mata_pelajaran'];
}

// Ambil data batas ajar
$batas_ajar_list = [];
$result = $conn->query("SELECT * FROM batas_ajar ORDER BY tanggal DESC, kelas, mata_pelajaran");
while ($row = $result->fetch_assoc()) {
    $batas_ajar_list[] = $row;
}
?>

<!-- Custom CSS untuk halaman batas ajar -->
<style>
.batas-ajar-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.batas-ajar-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.form-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.batas-item {
    background: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border-left: 4px solid #667eea;
}

.batas-item:hover {
    transform: translateX(5px);
    box-shadow: 0 5px 25px rgba(0,0,0,0.15);
}

.batas-header {
    background: linear-gradient(45deg, #f093fb, #f5576c);
    color: white;
    padding: 10px 15px;
    border-radius: 8px;
    margin-bottom: 15px;
    font-weight: bold;
}

.batas-content {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    border-left: 3px solid #28a745;
    margin-top: 10px;
}

.btn-action {
    border-radius: 20px;
    padding: 8px 20px;
    margin: 0 5px;
    transition: all 0.3s ease;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.alert-custom {
    border-radius: 10px;
    border: none;
    box-shadow: 0 3px 15px rgba(0,0,0,0.1);
}
</style>

<div class="container my-5">
    <!-- Header -->
    <div class="batas-ajar-card fade-in">
        <h2 class="text-center text-white mb-3">
            <i class="fas fa-bookmark"></i> Manajemen Batas Ajar
        </h2>
        <p class="text-center text-white mb-0">
            Kelola batas ajar untuk setiap kelas dan mata pelajaran
        </p>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($success_message)): ?>
        <div class="alert alert-success alert-custom fade-in" role="alert">
            <i class="fas fa-check-circle me-2"></i><?= $success_message ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger alert-custom fade-in" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i><?= $error_message ?>
        </div>
    <?php endif; ?>

    <!-- Tab Navigation -->
    <div class="form-card fade-in">
        <ul class="nav nav-tabs mb-4" id="formTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="single-tab" data-bs-toggle="tab" data-bs-target="#single-form" type="button" role="tab">
                    <i class="fas fa-plus-circle"></i> Input Tunggal
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-form" type="button" role="tab">
                    <i class="fas fa-layer-group"></i> Input Bulk
                </button>
            </li>
        </ul>
        
        <div class="tab-content" id="formTabsContent">
            <!-- Form Tambah/Edit Batas Ajar Tunggal -->
            <div class="tab-pane fade show active" id="single-form" role="tabpanel">
                <h4 class="mb-4">
                    <i class="fas fa-plus-circle text-primary"></i> 
                    <span id="form-title">Tambah Batas Ajar Baru</span>
                </h4>
        
        <form method="POST" id="batasAjarForm">
            <input type="hidden" name="action" id="form-action" value="add">
            <input type="hidden" name="id" id="form-id">
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="kelas" class="form-label">
                        <i class="fas fa-school"></i> Kelas
                    </label>
                    <select class="form-select" name="kelas" id="kelas" required>
                        <option value="">Pilih Kelas</option>
                        <?php foreach ($kelas_options as $kelas): ?>
                            <option value="<?= $kelas ?>"><?= $kelas ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="mata_pelajaran" class="form-label">
                        <i class="fas fa-book"></i> Mata Pelajaran
                    </label>
                    <select class="form-select" name="mata_pelajaran" id="mata_pelajaran" required disabled>
                        <option value="">Pilih Kelas Terlebih Dahulu</option>
                    </select>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="tanggal" class="form-label">
                        <i class="fas fa-calendar"></i> Tanggal
                    </label>
                    <input type="date" class="form-control" name="tanggal" id="tanggal" required>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="batas_ajar" class="form-label">
                    <i class="fas fa-bookmark"></i> Batas Ajar
                </label>
                <textarea class="form-control" name="batas_ajar" id="batas_ajar" rows="4" 
                          placeholder="Masukkan batas ajar untuk hari tersebut..." required></textarea>
            </div>
            
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-action">
                    <i class="fas fa-save"></i> <span id="btn-text">Simpan</span>
                </button>
                <button type="button" class="btn btn-secondary btn-action" onclick="resetForm()">
                    <i class="fas fa-times"></i> Reset
                </button>
            </div>
        </form>
            </div>
            
            <!-- Form Bulk Add -->
            <div class="tab-pane fade" id="bulk-form" role="tabpanel">
                <h4 class="mb-4">
                    <i class="fas fa-layer-group text-success"></i> Input Bulk Batas Ajar
                </h4>
                
                <form method="POST" id="bulkBatasAjarForm">
                    <input type="hidden" name="action" value="bulk_add">
                    
                    <div class="row mb-4">
                        <div class="col-md-6 mb-3">
                            <label for="bulk_kelas" class="form-label">
                                <i class="fas fa-school"></i> Kelas
                            </label>
                            <select class="form-select" name="bulk_kelas" id="bulk_kelas" required>
                                <option value="">Pilih Kelas</option>
                                <?php foreach ($kelas_options as $kelas): ?>
                                    <option value="<?= $kelas ?>"><?= $kelas ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="bulk_mata_pelajaran" class="form-label">
                                <i class="fas fa-book"></i> Mata Pelajaran
                            </label>
                            <select class="form-select" name="bulk_mata_pelajaran" id="bulk_mata_pelajaran" required disabled>
                                <option value="">Pilih Kelas Terlebih Dahulu</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="bulk_input" class="form-label">
                            <i class="fas fa-edit"></i> Input Batas Ajar (Format: DD/MM/YY, Batas Ajar)
                        </label>
                        <textarea class="form-control" name="bulk_input" id="bulk_input" rows="10" 
                                  placeholder="Contoh format input:\n09/07/25, Pengertian fotosintesis\n13/07/25, Latihan pengenalan\n15/07/25, Praktikum laboratorium\n\nSetiap baris = satu data batas ajar\nFormat: tanggal (DD/MM/YY), batas ajar" required></textarea>
                        <div class="form-text">
                            <i class="fas fa-info-circle"></i> 
                            <strong>Petunjuk:</strong> Setiap baris berisi satu data. Format: <code>DD/MM/YY, Batas Ajar</code>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <button type="button" class="btn btn-outline-info btn-sm" onclick="previewBulkData()">
                            <i class="fas fa-eye"></i> Preview Data
                        </button>
                    </div>
                    
                    <div id="bulk-preview" class="mb-3" style="display: none;">
                        <div class="alert alert-info">
                            <h6><i class="fas fa-list"></i> Preview Data yang akan disimpan:</h6>
                            <div id="preview-content"></div>
                        </div>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-success btn-action">
                            <i class="fas fa-save"></i> Simpan Semua
                        </button>
                        <button type="button" class="btn btn-secondary btn-action" onclick="resetBulkForm()">
                            <i class="fas fa-times"></i> Reset
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Daftar Batas Ajar -->
    <div class="form-card fade-in">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="fas fa-list text-success"></i> Daftar Batas Ajar
            </h4>
            <?php if (count($batas_ajar_list) > 0): ?>
                <button class="btn btn-danger btn-action" onclick="deleteAllBatasAjar()">
                    <i class="fas fa-trash-alt"></i> Hapus Semua
                </button>
            <?php endif; ?>
        </div>
        
        <?php if (count($batas_ajar_list) > 0): ?>
            <?php foreach ($batas_ajar_list as $item): ?>
                <div class="batas-item">
                    <div class="batas-header">
                        <div class="row align-items-center">
                            <div class="col-md-8">
                                <i class="fas fa-school me-2"></i>Kelas <?= $item['kelas'] ?> - 
                                <i class="fas fa-book me-2"></i><?= $item['mata_pelajaran'] ?>
                            </div>
                            <div class="col-md-4 text-end">
                                <i class="fas fa-calendar me-2"></i>
                                <?= date('d F Y', strtotime($item['tanggal'])) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="batas-content">
                        <strong>Batas Ajar:</strong><br>
                        <?= nl2br(htmlspecialchars($item['batas_ajar'])) ?>
                    </div>
                    
                    <div class="mt-3 text-end">
                        <button class="btn btn-warning btn-action btn-sm" 
                                onclick="editBatasAjar(<?= htmlspecialchars(json_encode($item)) ?>)">
                            <i class="fas fa-edit"></i> Edit
                        </button>
                        <button class="btn btn-danger btn-action btn-sm" 
                                onclick="deleteBatasAjar(<?= $item['id'] ?>)">
                            <i class="fas fa-trash"></i> Hapus
                        </button>
                    </div>
                    
                    <div class="mt-2">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Dibuat: <?= date('d/m/Y H:i', strtotime($item['created_at'])) ?>
                            <?php if ($item['updated_at'] != $item['created_at']): ?>
                                | Diperbarui: <?= date('d/m/Y H:i', strtotime($item['updated_at'])) ?>
                            <?php endif; ?>
                        </small>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-bookmark fa-4x text-muted mb-3"></i>
                <h5 class="text-muted">Belum Ada Batas Ajar</h5>
                <p class="text-muted">Mulai tambahkan batas ajar untuk kelas dan mata pelajaran Anda.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>Apakah Anda yakin ingin menghapus batas ajar ini?</p>
                <p class="text-danger"><strong>Tindakan ini tidak dapat dibatalkan!</strong></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" id="delete-id">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Semua -->
<div class="modal fade" id="deleteAllModal" tabindex="-1" aria-labelledby="deleteAllModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="deleteAllModalLabel">
                    <i class="fas fa-exclamation-triangle"></i> Konfirmasi Hapus Semua
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-3">
                    <i class="fas fa-exclamation-triangle fa-4x text-danger"></i>
                </div>
                <h5 class="text-center text-danger mb-3">PERINGATAN!</h5>
                <p class="text-center">Anda akan menghapus <strong>SEMUA</strong> data batas ajar yang ada.</p>
                <p class="text-center text-danger"><strong>Tindakan ini tidak dapat dibatalkan dan akan menghapus <?= count($batas_ajar_list) ?> data batas ajar!</strong></p>
                <div class="alert alert-danger mt-3">
                    <i class="fas fa-info-circle me-2"></i>
                    Pastikan Anda telah membackup data jika diperlukan sebelum melanjutkan.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times"></i> Batal
                </button>
                <form method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete_all">
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash-alt"></i> Ya, Hapus Semua
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
// Data mata pelajaran per kelas
const mataPelajaranPerKelas = <?= json_encode(getMataPelajaranPerKelas($conn)) ?>;
let bulkRowCounter = 0;

function updateMataPelajaran() {
    const kelasSelect = document.getElementById('kelas');
    const mataPelajaranSelect = document.getElementById('mata_pelajaran');
    const selectedKelas = kelasSelect.value;
    
    // Reset dropdown mata pelajaran
    mataPelajaranSelect.innerHTML = '<option value="">Pilih Mata Pelajaran</option>';
    
    if (selectedKelas && mataPelajaranPerKelas[selectedKelas]) {
        mataPelajaranSelect.disabled = false;
        mataPelajaranPerKelas[selectedKelas].forEach(function(mapel) {
            const option = document.createElement('option');
            option.value = mapel;
            option.textContent = mapel;
            mataPelajaranSelect.appendChild(option);
        });
    } else {
        mataPelajaranSelect.disabled = true;
        mataPelajaranSelect.innerHTML = '<option value="">Pilih Kelas Terlebih Dahulu</option>';
    }
}

function updateBulkMataPelajaran() {
    const kelasSelect = document.getElementById('bulk_kelas');
    const mataPelajaranSelect = document.getElementById('bulk_mata_pelajaran');
    const selectedKelas = kelasSelect.value;
    
    // Reset dropdown mata pelajaran
    mataPelajaranSelect.innerHTML = '<option value="">Pilih Mata Pelajaran</option>';
    
    if (selectedKelas && mataPelajaranPerKelas[selectedKelas]) {
        mataPelajaranSelect.disabled = false;
        mataPelajaranPerKelas[selectedKelas].forEach(function(mapel) {
            const option = document.createElement('option');
            option.value = mapel;
            option.textContent = mapel;
            mataPelajaranSelect.appendChild(option);
        });
    } else {
        mataPelajaranSelect.disabled = true;
        mataPelajaranSelect.innerHTML = '<option value="">Pilih Kelas Terlebih Dahulu</option>';
    }
}

function previewBulkData() {
    const bulkInput = document.getElementById('bulk_input').value.trim();
    const previewDiv = document.getElementById('bulk-preview');
    const previewContent = document.getElementById('preview-content');
    
    if (!bulkInput) {
        alert('Silakan masukkan data terlebih dahulu!');
        return;
    }
    
    const lines = bulkInput.split('\n');
    let previewHtml = '<ol>';
    let validCount = 0;
    let invalidCount = 0;
    
    lines.forEach(function(line) {
        line = line.trim();
        if (line) {
            const parts = line.split(',', 2);
            if (parts.length === 2) {
                const tanggal = parts[0].trim();
                const batasAjar = parts[1].trim();
                
                // Validate date format
                const datePattern = /^\d{1,2}\/\d{1,2}\/\d{2,4}$/;
                if (datePattern.test(tanggal) && batasAjar) {
                    previewHtml += `<li><strong>${tanggal}</strong>: ${batasAjar}</li>`;
                    validCount++;
                } else {
                    previewHtml += `<li class="text-danger"><strong>ERROR</strong>: ${line} (format salah)</li>`;
                    invalidCount++;
                }
            } else {
                previewHtml += `<li class="text-danger"><strong>ERROR</strong>: ${line} (format salah)</li>`;
                invalidCount++;
            }
        }
    });
    
    previewHtml += '</ol>';
    previewHtml += `<div class="mt-2"><strong>Valid:</strong> ${validCount} | <strong>Invalid:</strong> ${invalidCount}</div>`;
    
    previewContent.innerHTML = previewHtml;
    previewDiv.style.display = 'block';
}

function resetBulkForm() {
    document.getElementById('bulkBatasAjarForm').reset();
    document.getElementById('bulk-preview').style.display = 'none';
    
    // Reset dropdown mata pelajaran
    const mataPelajaranSelect = document.getElementById('bulk_mata_pelajaran');
    mataPelajaranSelect.disabled = true;
    mataPelajaranSelect.innerHTML = '<option value="">Pilih Kelas Terlebih Dahulu</option>';
}

function editBatasAjar(data) {
    document.getElementById('form-action').value = 'edit';
    document.getElementById('form-id').value = data.id;
    document.getElementById('kelas').value = data.kelas;
    
    // Update mata pelajaran berdasarkan kelas
    updateMataPelajaran();
    
    // Set mata pelajaran setelah dropdown diupdate
    setTimeout(function() {
        document.getElementById('mata_pelajaran').value = data.mata_pelajaran;
    }, 100);
    
    document.getElementById('tanggal').value = data.tanggal;
    document.getElementById('batas_ajar').value = data.batas_ajar;
    
    document.getElementById('form-title').textContent = 'Edit Batas Ajar';
    document.getElementById('btn-text').textContent = 'Update';
    
    // Scroll ke form
    document.getElementById('batasAjarForm').scrollIntoView({ behavior: 'smooth' });
}

function resetForm() {
    document.getElementById('batasAjarForm').reset();
    document.getElementById('form-action').value = 'add';
    document.getElementById('form-id').value = '';
    document.getElementById('form-title').textContent = 'Tambah Batas Ajar Baru';
    document.getElementById('btn-text').textContent = 'Simpan';
    
    // Reset dropdown mata pelajaran
    const mataPelajaranSelect = document.getElementById('mata_pelajaran');
    mataPelajaranSelect.disabled = true;
    mataPelajaranSelect.innerHTML = '<option value="">Pilih Kelas Terlebih Dahulu</option>';
}

function deleteBatasAjar(id) {
    document.getElementById('delete-id').value = id;
    const modal = new bootstrap.Modal(document.getElementById('deleteModal'));
    modal.show();
}

function deleteAllBatasAjar() {
    const modal = new bootstrap.Modal(document.getElementById('deleteAllModal'));
    modal.show();
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const today = new Date().toISOString().split('T')[0];
    document.getElementById('tanggal').value = today;
    
    // Event listener untuk perubahan kelas
    document.getElementById('kelas').addEventListener('change', updateMataPelajaran);
    document.getElementById('bulk_kelas').addEventListener('change', updateBulkMataPelajaran);
});
</script>

<?php
// Fungsi untuk mendapatkan mata pelajaran per kelas
function getMataPelajaranPerKelas($conn) {
    $result = [];
    $query = $conn->query("SELECT DISTINCT kelas, mata_pelajaran FROM jadwal ORDER BY kelas, mata_pelajaran");
    while ($row = $query->fetch_assoc()) {
        $result[$row['kelas']][] = $row['mata_pelajaran'];
    }
    return $result;
}
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

</body>
</html>