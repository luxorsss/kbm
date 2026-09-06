<?php
include 'db.php';
include 'header.php';
?>

<!-- Tambahkan Chart.js dan ApexCharts -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<?php

// Fungsi untuk mendapatkan statistik berdasarkan parameter
function getStatistik($conn, $groupBy) {
    $validColumns = ['hari', 'kelas', 'mata_pelajaran', 'kombinasi'];
    
    if (!in_array($groupBy, $validColumns)) {
        return [];
    }
    
    if ($groupBy === 'kombinasi') {
        $query = "SELECT hari, kelas, COUNT(*) as jumlah_jam FROM jadwal GROUP BY hari, kelas ORDER BY hari, kelas";
    } else {
        $query = "SELECT $groupBy, COUNT(*) as jumlah_jam FROM jadwal GROUP BY $groupBy ORDER BY jumlah_jam DESC";
    }
    
    $result = $conn->query($query);
    
    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }
    
    return $data;
}

// Fungsi untuk mendapatkan data untuk chart
function getChartData($conn, $groupBy) {
    $data = getStatistik($conn, $groupBy);
    $labels = [];
    $values = [];
    
    foreach ($data as $item) {
        if ($groupBy === 'kombinasi') {
            $labels[] = $item['hari'] . ' - ' . $item['kelas'];
        } else {
            $labels[] = $item[$groupBy];
        }
        $values[] = $item['jumlah_jam'];
    }
    
    return [
        'labels' => $labels,
        'values' => $values
    ];
}

// Mendapatkan parameter filter dari URL atau default ke 'hari'
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'hari';

// Mendapatkan data statistik
$statistikData = getStatistik($conn, $filter);

// Mendapatkan data untuk chart
$chartData = getChartData($conn, $filter);

// Data heatmap dihapus sesuai permintaan
?>

<div class="container my-5">
    <div class="header">
        <h1>Statistik Jadwal</h1>
        <p>"Analisis data untuk perencanaan yang lebih baik"</p>
    </div>
    
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="filter-container">
                <form method="get" class="d-flex align-items-center">
                    <label for="filter" class="me-2">Tampilkan berdasarkan:</label>
                    <select name="filter" id="filter" class="form-select" onchange="this.form.submit()">
                        <option value="hari" <?= $filter == 'hari' ? 'selected' : '' ?>>Hari</option>
                        <option value="kelas" <?= $filter == 'kelas' ? 'selected' : '' ?>>Kelas</option>
                        <option value="mata_pelajaran" <?= $filter == 'mata_pelajaran' ? 'selected' : '' ?>>Mata Pelajaran</option>
                        <option value="kombinasi" <?= $filter == 'kombinasi' ? 'selected' : '' ?>>Kombinasi Hari & Kelas</option>
                    </select>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bagian heatmap dihapus sesuai permintaan -->
    
    <div class="row">
        <div class="col-md-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Visualisasi Jumlah Jam Berdasarkan <?= ucfirst($filter) ?></h5>
                </div>
                <div class="card-body">
                    <canvas id="statistikChart" width="400" height="200"></canvas>
                </div>
            </div>
            
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Jumlah Jam Berdasarkan <?= ucfirst($filter) ?></h5>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-hover">
                        <thead class="table-primary">
                            <tr>
                                <th>No</th>
                                <?php if ($filter === 'kombinasi'): ?>
                                    <th>Hari</th>
                                    <th>Kelas</th>
                                <?php else: ?>
                                    <th><?= ucfirst($filter) ?></th>
                                <?php endif; ?>
                                <th>Jumlah Jam</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($statistikData) > 0): ?>
                                <?php $no = 1; foreach ($statistikData as $data): ?>
                                    <tr>
                                        <td><?= $no++ ?></td>
                                        <?php if ($filter === 'kombinasi'): ?>
                                            <td><?= $data['hari'] ?></td>
                                            <td><?= $data['kelas'] ?></td>
                                        <?php else: ?>
                                            <td><?= $data[$filter] ?></td>
                                        <?php endif; ?>
                                        <td><?= $data['jumlah_jam'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="<?= ($filter === 'kombinasi') ? 4 : 3 ?>" class="text-center">Tidak ada data</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Ringkasan</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Menghitung total jam
                    $totalQuery = "SELECT COUNT(*) as total FROM jadwal";
                    $totalResult = $conn->query($totalQuery);
                    $totalRow = $totalResult->fetch_assoc();
                    $totalJam = $totalRow['total'];
                    
                    // Menghitung jumlah hari yang digunakan
                    $hariQuery = "SELECT COUNT(DISTINCT hari) as total_hari FROM jadwal";
                    $hariResult = $conn->query($hariQuery);
                    $hariRow = $hariResult->fetch_assoc();
                    $totalHari = $hariRow['total_hari'];
                    
                    // Menghitung jumlah kelas
                    $kelasQuery = "SELECT COUNT(DISTINCT kelas) as total_kelas FROM jadwal";
                    $kelasResult = $conn->query($kelasQuery);
                    $kelasRow = $kelasResult->fetch_assoc();
                    $totalKelas = $kelasRow['total_kelas'];
                    
                    // Menghitung jumlah mata pelajaran
                    $mapelQuery = "SELECT COUNT(DISTINCT mata_pelajaran) as total_mapel FROM jadwal";
                    $mapelResult = $conn->query($mapelQuery);
                    $mapelRow = $mapelResult->fetch_assoc();
                    $totalMapel = $mapelRow['total_mapel'];
                    ?>
                    
                    <ul class="list-group list-group-flush">
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Total Jam Pelajaran
                            <span class="badge bg-primary rounded-pill"><?= $totalJam ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Jumlah Hari
                            <span class="badge bg-primary rounded-pill"><?= $totalHari ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Jumlah Kelas
                            <span class="badge bg-primary rounded-pill"><?= $totalKelas ?></span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            Jumlah Mata Pelajaran
                            <span class="badge bg-primary rounded-pill"><?= $totalMapel ?></span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <!-- Tambahan informasi statistik lainnya -->
            <div class="card shadow-sm mt-3">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">Distribusi Jam</h5>
                </div>
                <div class="card-body">
                    <?php
                    // Menghitung distribusi jam
                    $jamQuery = "SELECT jam, COUNT(*) as jumlah FROM jadwal GROUP BY jam ORDER BY jam";
                    $jamResult = $conn->query($jamQuery);
                    $jamData = [];
                    while ($jamRow = $jamResult->fetch_assoc()) {
                        $jamData[] = $jamRow;
                    }
                    ?>
                    
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Jam ke-</th>
                                    <th>Jumlah</th>
                                    <th>Persentase</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($jamData as $jam): ?>
                                    <tr>
                                        <td><?= $jam['jam'] ?></td>
                                        <td><?= $jam['jumlah'] ?></td>
                                        <td>
                                            <?php $persentase = ($jam['jumlah'] / $totalJam) * 100; ?>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" style="width: <?= $persentase ?>%" 
                                                     aria-valuenow="<?= $persentase ?>" aria-valuemin="0" aria-valuemax="100">
                                                    <?= number_format($persentase, 1) ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<footer class="text-center py-3">
    <small>&copy; <?= date('Y') ?> Menuntut Ilmu. Semua Hak Dilindungi.</small>
</footer>

<script>
// Data dari PHP untuk chart
const labels = <?= json_encode($chartData['labels']) ?>;
const values = <?= json_encode($chartData['values']) ?>;

// Data heatmap dihapus sesuai permintaan

// Membuat warna acak untuk chart
function generateColors(count) {
    const colors = [];
    const backgroundColors = [];
    
    for (let i = 0; i < count; i++) {
        const r = Math.floor(Math.random() * 200);
        const g = Math.floor(Math.random() * 200);
        const b = Math.floor(Math.random() * 200);
        
        colors.push(`rgba(${r}, ${g}, ${b}, 0.8)`);
        backgroundColors.push(`rgba(${r}, ${g}, ${b}, 0.2)`);
    }
    
    return { colors, backgroundColors };
}

const colorSet = generateColors(labels.length);




// Inisialisasi Chart
const ctx = document.getElementById('statistikChart').getContext('2d');
const myChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: labels,
        datasets: [{
            label: 'Jumlah Jam',
            data: values,
            backgroundColor: colorSet.backgroundColors,
            borderColor: colorSet.colors,
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Distribusi Jam Berdasarkan <?= ucfirst($filter) ?>'
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Tambahkan chart pie untuk distribusi jam
const jamLabels = [];
const jamValues = [];

<?php foreach ($jamData as $jam): ?>
    jamLabels.push('Jam <?= $jam['jam'] ?>');
    jamValues.push(<?= $jam['jumlah'] ?>);
<?php endforeach; ?>

const jamColorSet = generateColors(jamLabels.length);

// Cari elemen untuk menempatkan grafik pie
const pieContainer = document.querySelector('.card:last-child .card-body');

// Buat elemen canvas untuk grafik pie
const pieCtx = document.createElement('canvas');
pieCtx.id = 'jamDistribusiChart';
pieContainer.appendChild(pieCtx);

const pieChart = new Chart(pieCtx, {
    type: 'pie',
    data: {
        labels: jamLabels,
        datasets: [{
            label: 'Distribusi Jam',
            data: jamValues,
            backgroundColor: jamColorSet.colors,
            borderColor: 'white',
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'right',
            },
            title: {
                display: true,
                text: 'Distribusi Berdasarkan Jam Pelajaran'
            }
        }
    }
});

// Kode inisialisasi heatmap dihapus sesuai permintaan
</script>
</body>
</html>