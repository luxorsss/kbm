<?php
include 'db.php';
include 'header.php';

// Query semua jadwal dengan informasi tambahan
$query = "SELECT * FROM jadwal ORDER BY FIELD(hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'), jam";
$result = $conn->query($query);

// Susun jadwal per hari dan jam dalam array
$jadwal_per_pekan = [];
$jadwal_per_hari = [];
$jadwal_detail = [];
while ($row = $result->fetch_assoc()) {
    $jadwal_per_pekan[$row['jam']][$row['hari']] = [
        'kelas' => $row['kelas'],
        'mata_pelajaran' => $row['mata_pelajaran'],
        'id' => $row['id']
    ];
    $jadwal_per_hari[$row['hari']][$row['jam']] = [
        'kelas' => $row['kelas'],
        'mata_pelajaran' => $row['mata_pelajaran'],
        'id' => $row['id']
    ];
    $jadwal_detail[$row['id']] = $row;
}

// Daftar hari dalam satu pekan
$hari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
$jam_labels = [
    1 => '08:00 - 08:40',
    2 => '08:41 - 09:20', 
    3 => '09:21 - 10:00',
    4 => '10:31 - 11:10',
    5 => '11:11 - 11:50',
    6 => '11:51 - 12:30'
];

// Cek apakah ada parameter 'hari' yang dipilih untuk tampilan per hari
$selected_hari = isset($_GET['hari']) ? $_GET['hari'] : 'Senin';

// Cek apakah ada parameter untuk memilih tampilan
$tampilan = isset($_GET['tampilan']) ? $_GET['tampilan'] : 'pekan';
?>

<!-- Custom CSS untuk halaman kelas -->
<style>
.schedule-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.schedule-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 40px rgba(0,0,0,0.2);
}

.schedule-cell {
    background: #fff;
    border-radius: 10px;
    padding: 15px;
    margin: 5px;
    transition: all 0.3s ease;
    cursor: pointer;
    min-height: 80px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

.schedule-cell:hover {
    transform: scale(1.05);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    background: linear-gradient(45deg, #f093fb 0%, #f5576c 100%);
    color: white;
}

.schedule-cell.empty {
    background: #f8f9fa;
    border: 2px dashed #dee2e6;
    color: #6c757d;
}

.schedule-cell.empty:hover {
    background: #e9ecef;
    transform: none;
    box-shadow: none;
}

.time-header {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    font-weight: bold;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
}

.day-header {
    background: linear-gradient(45deg, #f093fb, #f5576c);
    color: white;
    font-weight: bold;
    border-radius: 10px;
    padding: 15px;
    text-align: center;
}

.view-toggle {
    background: white;
    border-radius: 50px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.toggle-btn {
    border-radius: 25px;
    padding: 10px 25px;
    margin: 0 10px;
    transition: all 0.3s ease;
    border: none;
    font-weight: 600;
}

.toggle-btn.active {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    transform: scale(1.1);
}

.schedule-info {
    font-size: 0.9em;
    margin-bottom: 5px;
}

.subject-name {
    font-weight: bold;
    color: #2c3e50;
}

.class-name {
    color: #7f8c8d;
    font-size: 0.8em;
}

.fade-in {
    animation: fadeIn 0.5s ease-in;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.modal-content {
    border-radius: 15px;
    border: none;
}

.modal-header {
    background: linear-gradient(45deg, #667eea, #764ba2);
    color: white;
    border-radius: 15px 15px 0 0;
}

.schedule-grid {
    display: grid;
    gap: 10px;
    margin-top: 20px;
}

.weekly-grid {
    grid-template-columns: 150px repeat(6, 1fr);
}

.daily-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.day-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.day-card:hover {
    transform: translateY(-5px);
}

.day-title {
    background: linear-gradient(45deg, #f093fb, #f5576c);
    color: white;
    padding: 10px;
    border-radius: 10px;
    text-align: center;
    margin-bottom: 15px;
    font-weight: bold;
}
</style>

<!-- Header sudah disertakan di atas -->
<div class="container my-5">
    <!-- Filter untuk memilih tampilan -->
    <div class="view-toggle text-center fade-in">
        <h2 class="mb-4" style="color: #2c3e50; font-weight: 700;">📅 Jadwal Mengajar</h2>
        <div class="d-flex justify-content-center align-items-center flex-wrap">
            <button class="toggle-btn <?= $tampilan == 'pekan' ? 'active' : '' ?>" onclick="changeView('pekan')">
                📊 Tampilan Mingguan
            </button>
            <button class="toggle-btn <?= $tampilan == 'hari' ? 'active' : '' ?>" onclick="changeView('hari')">
                📋 Tampilan Harian
            </button>
        </div>
    </div>

        <?php if ($tampilan == 'pekan') { ?>
            <!-- Grid Jadwal Per Pekan -->
            <div class="schedule-card fade-in">
                <h3 class="text-center text-white mb-4">📊 Jadwal Mengajar Mingguan</h3>
                <div class="schedule-grid weekly-grid">
                    <!-- Header kosong untuk pojok kiri atas -->
                    <div></div>
                    <!-- Header hari -->
                    <?php foreach ($hari as $h) { ?>
                        <div class="day-header"><?= $h ?></div>
                    <?php } ?>
                    
                    <!-- Baris untuk setiap jam -->
                    <?php for ($jam = 1; $jam <= 6; $jam++) { ?>
                        <!-- Header jam -->
                        <div class="time-header">
                            <div>Jam <?= $jam ?></div>
                            <small><?= $jam_labels[$jam] ?></small>
                        </div>
                        
                        <!-- Sel untuk setiap hari -->
                        <?php foreach ($hari as $h) { ?>
                            <div class="schedule-cell <?= empty($jadwal_per_pekan[$jam][$h]) ? 'empty' : '' ?>" 
                                 <?php if (!empty($jadwal_per_pekan[$jam][$h])) { ?>
                                     onclick="showScheduleDetail(<?= $jadwal_per_pekan[$jam][$h]['id'] ?>, '<?= $h ?>', <?= $jam ?>)"
                                 <?php } ?>>
                                <?php if (!empty($jadwal_per_pekan[$jam][$h])) { ?>
                                    <div class="subject-name"><?= $jadwal_per_pekan[$jam][$h]['mata_pelajaran'] ?></div>
                                    <div class="class-name">Kelas <?= $jadwal_per_pekan[$jam][$h]['kelas'] ?></div>
                                <?php } else { ?>
                                    <div class="text-center">Tidak ada kelas</div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
                </div>
            </div>
        <?php } else { ?>
            <!-- Filter untuk memilih hari -->
            <div class="view-toggle text-center mb-4 fade-in">
                <h3 class="mb-3" style="color: #2c3e50;">Pilih Hari untuk Melihat Detail</h3>
                <div class="d-flex justify-content-center flex-wrap gap-2">
                    <?php foreach ($hari as $h) { ?>
                        <button class="toggle-btn <?= $selected_hari == $h ? 'active' : '' ?>" 
                                onclick="changeDay('<?= $h ?>')">
                            <?= $h ?>
                        </button>
                    <?php } ?>
                </div>
            </div>

            <!-- Cards Jadwal Per Hari -->
            <div class="schedule-card fade-in">
                <h3 class="text-center text-white mb-4">📋 Jadwal <?= $selected_hari ?></h3>
                <div class="daily-cards">
                    <?php for ($jam = 1; $jam <= 6; $jam++) { ?>
                        <div class="day-card <?= empty($jadwal_per_hari[$selected_hari][$jam]) ? 'empty-schedule' : '' ?>">
                            <div class="day-title">
                                <i class="fas fa-clock"></i> Jam <?= $jam ?>
                                <br><small><?= $jam_labels[$jam] ?></small>
                            </div>
                            
                            <?php if (!empty($jadwal_per_hari[$selected_hari][$jam])) { ?>
                                <div class="schedule-content" 
                                     onclick="showScheduleDetail(<?= $jadwal_per_hari[$selected_hari][$jam]['id'] ?>, '<?= $selected_hari ?>', <?= $jam ?>)"
                                     style="cursor: pointer;">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <h5 class="text-primary mb-2">
                                                <i class="fas fa-book"></i> 
                                                <?= $jadwal_per_hari[$selected_hari][$jam]['mata_pelajaran'] ?>
                                            </h5>
                                        </div>
                                        <div class="col-md-6">
                                            <h6 class="text-secondary">
                                                <i class="fas fa-users"></i> 
                                                Kelas <?= $jadwal_per_hari[$selected_hari][$jam]['kelas'] ?>
                                            </h6>
                                        </div>
                                    </div>
                                    <div class="mt-3 p-3 bg-light rounded">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Klik untuk melihat detail lengkap
                                        </small>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Tidak Ada Kelas</h5>
                                    <p class="text-muted">Waktu istirahat atau tidak ada jadwal mengajar</p>
                                </div>
                            <?php } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>
        <?php } ?>
    </div>

    <!-- Modal Detail Jadwal -->
    <div class="modal fade" id="scheduleModal" tabindex="-1" aria-labelledby="scheduleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scheduleModalLabel">
                        <i class="fas fa-calendar-alt"></i> Detail Jadwal Mengajar
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" id="scheduleModalBody">
                    <!-- Content akan diisi oleh JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Tutup
                    </button>
                    <button type="button" class="btn btn-primary" onclick="editSchedule()">
                        <i class="fas fa-edit"></i> Edit Jadwal
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer yang lebih menarik -->
    <footer class="text-center py-4" style="background: linear-gradient(45deg, #667eea, #764ba2); color: white; margin-top: 50px;">
        <div class="container">
            <div class="row">
                <div class="col-md-4">
                    <h6><i class="fas fa-graduation-cap"></i> Sistem KBM</h6>
                    <p class="small mb-0">Mengelola jadwal mengajar dengan mudah</p>
                </div>
                <div class="col-md-4">
                    <h6><i class="fas fa-clock"></i> Jam Operasional</h6>
                    <p class="small mb-0">Senin - Sabtu: 08:00 - 12:30</p>
                </div>
                <div class="col-md-4">
                    <h6><i class="fas fa-heart"></i> Dibuat dengan</h6>
                    <p class="small mb-0">PHP & Bootstrap</p>
                </div>
            </div>
            <hr class="my-3" style="border-color: rgba(255,255,255,0.3);">
            <small>&copy; <?= date('Y') ?> Menuntut Ilmu. Semua Hak Dilindungi.</small>
        </div>
    </footer>

    <!-- JavaScript untuk interaktivitas -->
    <script>
        // Data jadwal untuk modal
        const jadwalData = <?= json_encode($jadwal_detail) ?>;
        
        // Fungsi untuk mengubah tampilan
        function changeView(view) {
            window.location.href = `?tampilan=${view}`;
        }
        
        // Fungsi untuk mengubah hari
        function changeDay(hari) {
            window.location.href = `?tampilan=hari&hari=${hari}`;
        }
        
        // Fungsi untuk menampilkan detail jadwal
        function showScheduleDetail(id, hari, jam) {
            const jadwal = jadwalData[id];
            if (!jadwal) return;
            
            const jamLabels = {
                1: '08:00 - 08:40',
                2: '08:41 - 09:20',
                3: '09:21 - 10:00',
                4: '10:31 - 11:10',
                5: '11:11 - 11:50',
                6: '11:51 - 12:30'
            };
            
            const modalBody = document.getElementById('scheduleModalBody');
            modalBody.innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-primary">
                                    <i class="fas fa-calendar"></i> Informasi Waktu
                                </h6>
                                <p class="mb-1"><strong>Hari:</strong> ${hari}</p>
                                <p class="mb-1"><strong>Jam:</strong> ${jam} (${jamLabels[jam]})</p>
                                <p class="mb-0"><strong>Durasi:</strong> 40 menit</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card border-0 bg-light">
                            <div class="card-body">
                                <h6 class="card-title text-success">
                                    <i class="fas fa-book"></i> Informasi Pelajaran
                                </h6>
                                <p class="mb-1"><strong>Mata Pelajaran:</strong> ${jadwal.mata_pelajaran}</p>
                                <p class="mb-1"><strong>Kelas:</strong> ${jadwal.kelas}</p>
                                <p class="mb-0"><strong>ID Jadwal:</strong> #${jadwal.id}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-12">
                        <div class="card border-0" style="background: linear-gradient(45deg, #f093fb, #f5576c); color: white;">
                            <div class="card-body text-center">
                                <h5 class="card-title">
                                    <i class="fas fa-chalkboard-teacher"></i> 
                                    ${jadwal.mata_pelajaran}
                                </h5>
                                <p class="card-text">Kelas ${jadwal.kelas} • ${hari} • Jam ${jam}</p>
                                <small>${jamLabels[jam]}</small>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Simpan ID untuk edit
            window.currentScheduleId = id;
            
            // Tampilkan modal
            const modal = new bootstrap.Modal(document.getElementById('scheduleModal'));
            modal.show();
        }
        
        // Fungsi untuk edit jadwal
        function editSchedule() {
            if (window.currentScheduleId) {
                window.location.href = `edit.php?id=${window.currentScheduleId}`;
            }
        }
        
        // Animasi saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            // Tambahkan efek hover pada schedule cells
            const scheduleCells = document.querySelectorAll('.schedule-cell:not(.empty)');
            scheduleCells.forEach(cell => {
                cell.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.05) rotate(1deg)';
                });
                cell.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) rotate(0deg)';
                });
            });
            
            // Tambahkan efek pada day cards
            const dayCards = document.querySelectorAll('.day-card');
            dayCards.forEach((card, index) => {
                card.style.animationDelay = `${index * 0.1}s`;
                card.classList.add('fade-in');
            });
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = bootstrap.Modal.getInstance(document.getElementById('scheduleModal'));
                if (modal) modal.hide();
            }
        });
    </script>
    
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</body>
</html>