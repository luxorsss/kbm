<?php
include 'db.php';
// Jangan sertakan header.php di sini karena akan menyebabkan duplikasi tag HTML

$current_time = date("H:i", strtotime('+7 hours')); // Sesuaikan jika server UTC;
$current_day = strtr(date("l"), [
    'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
    'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
]);

$jam_pelajaran = [
    '1' => ['start' => '08:00', 'end' => '08:40'],
    '2' => ['start' => '08:41', 'end' => '09:20'],
    '3' => ['start' => '09:21', 'end' => '10:00'],
    '4' => ['start' => '10:31', 'end' => '11:10'],
    '5' => ['start' => '11:11', 'end' => '11:50'],
    '6' => ['start' => '11:51', 'end' => '12:30']
];

$jadwal = [];
$result = $conn->query("SELECT * FROM jadwal WHERE hari = '$current_day'");
while ($row = $result->fetch_assoc()) $jadwal[$row['jam']] = $row;

// Ambil batas ajar untuk hari ini
$batas_ajar_hari_ini = [];
$today = date('Y-m-d');
$result_batas = $conn->query("SELECT * FROM batas_ajar WHERE tanggal = '$today' ORDER BY kelas, mata_pelajaran");
if ($result_batas) {
    while ($row = $result_batas->fetch_assoc()) {
        $batas_ajar_hari_ini[] = $row;
    }
}

// Fungsi untuk mencari batas ajar berdasarkan kelas dan mata pelajaran
function getBatasAjar($kelas, $mata_pelajaran, $batas_ajar_list) {
    foreach ($batas_ajar_list as $batas) {
        if ($batas['kelas'] == $kelas && $batas['mata_pelajaran'] == $mata_pelajaran) {
            return $batas;
        }
    }
    return null;
}

$jam_aktif = $jam_berikutnya = $jadwal_aktif = $jadwal_berikutnya = null;
foreach ($jam_pelajaran as $jam => $waktu) {
    if ($current_time >= $waktu['start'] && $current_time <= $waktu['end']) {
        $jam_aktif = $jam;
        $jadwal_aktif = $jadwal[$jam] ?? null;
    } elseif (!$jam_berikutnya && $current_time < $waktu['start']) {
        $jam_berikutnya = $jam;
        $jadwal_berikutnya = $jadwal[$jam] ?? null;
        break;
    }
}
?>

<?php include 'header.php'; ?>

<!-- Tambahkan Font Awesome untuk ikon -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Tambahkan AOS CSS untuk animasi -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-lg border-0 mb-5" data-aos="fade-down" data-aos-duration="1000">
                <div class="card-body text-center py-4" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color)); border-radius: 10px;">
                    <h1 class="text-white mb-2"><i class="fas fa-calendar-day me-2"></i>Jadwal Mengajar Hari Ini</h1>
                    <h3 class="text-white mb-0"><?= $current_day ?>, <?= date('d F Y') ?></h3>
                    <div class="d-flex justify-content-center mt-3">
                        <div class="bg-white px-3 py-2 rounded-pill">
                            <i class="fas fa-clock text-primary me-2"></i>
                            <span class="fw-bold"><?= $current_time ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <h4 class="text-center mb-4" data-aos="fade-up" data-aos-duration="800"><i class="fas fa-chalkboard-teacher me-2"></i>Jadwal Saat Ini</h4>
                <?php if ($jadwal_aktif): ?>
                <div class="card dashboard-card shadow border-0 mb-5 position-relative overflow-hidden" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    <div class="card-header bg-info text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Jam <?= $jam_aktif ?></h5>
                            <span class="badge bg-light text-dark rounded-pill px-3 py-2"><?= $jam_pelajaran[$jam_aktif]['start'] ?> - <?= $jam_pelajaran[$jam_aktif]['end'] ?></span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-info text-white rounded-circle p-3 me-3">
                                        <i class="fas fa-school fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Kelas</h6>
                                        <h4 class="mb-0"><?= $jadwal_aktif['kelas'] ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                                        <i class="fas fa-book fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Mata Pelajaran</h6>
                                        <h4 class="mb-0"><?= $jadwal_aktif['mata_pelajaran'] ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php 
                        $batas_ajar_aktif = getBatasAjar($jadwal_aktif['kelas'], $jadwal_aktif['mata_pelajaran'], $batas_ajar_hari_ini);
                        if ($batas_ajar_aktif): 
                        ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="border-top pt-3">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success text-white rounded-circle p-2 me-3 flex-shrink-0">
                                            <i class="fas fa-bookmark"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="text-success mb-2">
                                                <i class="fas fa-bookmark me-1"></i>Batas Ajar Hari Ini:
                                            </h6>
                                            <div class="bg-light p-3 rounded" style="border-left: 4px solid #28a745;">
                                                <p class="mb-0 small"><?= nl2br(htmlspecialchars($batas_ajar_aktif['batas_ajar'])) ?></p>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-clock me-1"></i>
                                                Dibuat: <?= date('d/m/Y H:i', strtotime($batas_ajar_aktif['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="position-absolute" style="top: 0; right: 0; width: 150px; height: 150px; background: radial-gradient(circle at top right, rgba(255,255,255,0.2) 0%, transparent 70%); border-radius: 0 0 0 100%;"></div>
                </div>
                <?php else: ?>
                <div class="card dashboard-card shadow border-0 mb-5 position-relative overflow-hidden" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="200">
                    <div class="card-body p-4 text-center">
                        <div class="py-4">
                            <i class="fas fa-coffee fa-4x text-muted mb-3"></i>
                            <h4>Tidak Ada Pelajaran Pada Waktu Ini</h4>
                            <p class="text-muted">Jadwal kosong untuk jam pelajaran yang sedang berlangsung. Cek waktu jam pelajaran lainnya!</p>
                        </div>
                    </div>
                    <div class="position-absolute" style="top: 0; right: 0; width: 150px; height: 150px; background: radial-gradient(circle at top right, rgba(0,0,0,0.05) 0%, transparent 70%); border-radius: 0 0 0 100%;"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-10">
                <h4 class="text-center mb-4" data-aos="fade-up" data-aos-duration="800" data-aos-delay="300"><i class="fas fa-arrow-right me-2"></i>Jadwal Berikutnya</h4>
                <?php if ($jadwal_berikutnya): ?>
                <div class="card dashboard-card shadow border-0 mb-5 position-relative overflow-hidden" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
                    <div class="card-header bg-success text-white py-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Jam <?= $jam_berikutnya ?></h5>
                            <span class="badge bg-light text-dark rounded-pill px-3 py-2"><?= $jam_pelajaran[$jam_berikutnya]['start'] ?> - <?= $jam_pelajaran[$jam_berikutnya]['end'] ?></span>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row">
                            <div class="col-md-6 mb-3 mb-md-0">
                                <div class="d-flex align-items-center mb-3">
                                    <div class="bg-success text-white rounded-circle p-3 me-3">
                                        <i class="fas fa-school fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Kelas</h6>
                                        <h4 class="mb-0"><?= $jadwal_berikutnya['kelas'] ?></h4>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="d-flex align-items-center">
                                    <div class="bg-primary text-white rounded-circle p-3 me-3">
                                        <i class="fas fa-book fa-2x"></i>
                                    </div>
                                    <div>
                                        <h6 class="text-muted mb-1">Mata Pelajaran</h6>
                                        <h4 class="mb-0"><?= $jadwal_berikutnya['mata_pelajaran'] ?></h4>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <?php 
                        $batas_ajar_aktif = getBatasAjar($jadwal_aktif['kelas'], $jadwal_aktif['mata_pelajaran'], $batas_ajar_hari_ini);
                        if ($batas_ajar_aktif): 
                        ?>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="border-top pt-3">
                                    <div class="d-flex align-items-start">
                                        <div class="bg-success text-white rounded-circle p-2 me-3 flex-shrink-0">
                                            <i class="fas fa-bookmark"></i>
                                        </div>
                                        <div class="flex-grow-1">
                                            <h6 class="text-success mb-2">
                                                <i class="fas fa-bookmark me-1"></i>Batas Ajar Hari Ini:
                                            </h6>
                                            <div class="bg-light p-3 rounded" style="border-left: 4px solid #28a745;">
                                                <p class="mb-0 small"><?= nl2br(htmlspecialchars($batas_ajar_aktif['batas_ajar'])) ?></p>
                                            </div>
                                            <small class="text-muted mt-2 d-block">
                                                <i class="fas fa-clock me-1"></i>
                                                Dibuat: <?= date('d/m/Y H:i', strtotime($batas_ajar_aktif['created_at'])) ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="position-absolute" style="top: 0; right: 0; width: 150px; height: 150px; background: radial-gradient(circle at top right, rgba(255,255,255,0.2) 0%, transparent 70%); border-radius: 0 0 0 100%;"></div>
                </div>
                <?php else: ?>
                <div class="card dashboard-card shadow border-0 mb-5 position-relative overflow-hidden" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="400">
                    <div class="card-body p-4 text-center">
                        <div class="py-4">
                            <i class="fas fa-check-circle fa-4x text-muted mb-3"></i>
                            <h4>Tidak Ada Pelajaran Berikutnya</h4>
                            <p class="text-muted">Tidak ada pelajaran yang dijadwalkan setelah waktu ini.</p>
                        </div>
                    </div>
                    <div class="position-absolute" style="top: 0; right: 0; width: 150px; height: 150px; background: radial-gradient(circle at top right, rgba(0,0,0,0.05) 0%, transparent 70%); border-radius: 0 0 0 100%;"></div>
                </div>
                <?php endif; ?>
            </div>
        </div>



        <!-- Ringkasan Jadwal Hari Ini -->
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card shadow border-0 mb-5" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="500">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-list-check me-2"></i>Ringkasan Jadwal Hari Ini</h5>
                    </div>
                    <div class="card-body p-0">
                        <?php
                        $jadwal_hari_ini = [];
                        $result = $conn->query("SELECT * FROM jadwal WHERE hari = '$current_day' ORDER BY jam ASC");
                        while ($row = $result->fetch_assoc()) {
                            $jadwal_hari_ini[] = $row;
                        }
                        ?>
                        
                        <?php if (count($jadwal_hari_ini) > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Jam</th>
                                        <th>Waktu</th>
                                        <th>Kelas</th>
                                        <th>Mata Pelajaran</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($jadwal_hari_ini as $jdwl): ?>
                                    <tr>
                                        <td><?= $jdwl['jam'] ?></td>
                                        <td><?= $jam_pelajaran[$jdwl['jam']]['start'] ?> - <?= $jam_pelajaran[$jdwl['jam']]['end'] ?></td>
                                        <td><?= $jdwl['kelas'] ?></td>
                                        <td><?= $jdwl['mata_pelajaran'] ?></td>
                                        <td>
                                            <?php if ($jdwl['jam'] == $jam_aktif): ?>
                                                <span class="badge bg-success rounded-pill">Sedang Berlangsung</span>
                                            <?php elseif ($jdwl['jam'] < $jam_aktif || ($jam_aktif === null && $current_time > $jam_pelajaran[$jdwl['jam']]['end'])): ?>
                                                <span class="badge bg-secondary rounded-pill">Selesai</span>
                                            <?php elseif ($jdwl['jam'] == $jam_berikutnya): ?>
                                                <span class="badge bg-warning text-dark rounded-pill">Berikutnya</span>
                                            <?php else: ?>
                                                <span class="badge bg-info text-white rounded-pill">Akan Datang</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="p-4 text-center">
                            <i class="fas fa-calendar-xmark fa-3x text-muted mb-3"></i>
                            <h5>Tidak Ada Jadwal Hari Ini</h5>
                            <p class="text-muted mb-0">Tidak ada jadwal mengajar yang terdaftar untuk hari <?= $current_day ?>.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row justify-content-center mb-5">
        <div class="col-md-10">
            <div class="card shadow border-0" data-aos="fade-up" data-aos-duration="1000" data-aos-delay="700">
                <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                    <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Aksi Cepat</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4 mb-3">
                            <a href="kelas.php" class="btn btn-outline-primary btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center py-4">
                                <i class="fas fa-calendar-week fa-2x mb-2"></i>
                                <span>Lihat Jadwal Lengkap</span>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="batas_ajar.php" class="btn btn-outline-success btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center py-4">
                                <i class="fas fa-bookmark fa-2x mb-2"></i>
                                <span>Kelola Batas Ajar</span>
                            </a>
                        </div>
                        <div class="col-md-4 mb-3">
                            <a href="#" class="btn btn-outline-info btn-lg w-100 h-100 d-flex flex-column justify-content-center align-items-center py-4" onclick="location.reload()">
                                <i class="fas fa-sync-alt fa-2x mb-2"></i>
                                <span>Refresh Halaman</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer yang lebih menarik -->
    <footer class="py-4 mt-5" style="background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));">
        <div class="container">
            <div class="row">
                <div class="col-md-6 text-center text-md-start">
                    <h5 class="text-white">Jadwal KBM</h5>
                    <p class="text-white-50 mb-0">Aplikasi manajemen jadwal mengajar untuk memudahkan proses belajar mengajar.</p>
                </div>
                <div class="col-md-6 text-center text-md-end">
                    <p class="text-white-50 mb-0">&copy; <?= date('Y') ?> Menuntut Ilmu. Semua Hak Dilindungi.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Tambahkan animasi dengan AOS library -->
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        // Inisialisasi AOS
        document.addEventListener('DOMContentLoaded', function() {
            AOS.init();
        });
    </script>
</body>
</html>