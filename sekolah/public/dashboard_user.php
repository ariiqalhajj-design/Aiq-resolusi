<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Cek login (boleh user atau admin, jika mau hanya user: ganti jadi 'user' saja)
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['user', 'admin'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id']; // Ambil ID user dari session
$username = $_SESSION['username']; // Ambil username dari session

// Ambil data tabungan user berdasarkan user_id
$stmt = $pdo->prepare("SELECT * FROM tabungan WHERE user_id=? ORDER BY id DESC");
$stmt->execute([$user_id]);
$tabungan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil hanya kegiatan milik user yang login
$stmt = $pdo->prepare("
    SELECT * 
    FROM kegiatan 
    WHERE user_id = ? 
    ORDER BY id DESC
");
$stmt->execute([$user_id]);
$kegiatan = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil data nilai user untuk setiap kegiatan
$nilai = [];
try {
    $stmt = $pdo->prepare("SELECT nk.id_kegiatan, nk.nilai, k.nama_kegiatan 
                          FROM nilai_kegiatan nk 
                          JOIN kegiatan k ON nk.id_kegiatan = k.id 
                          WHERE nk.user_id = ?");
    $stmt->execute([$user_id]);
    $nilai = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Tabel nilai_kegiatan tidak ditemukan: " . $e->getMessage());
}

// Hitung total saldo
$total_saldo = 0;
foreach ($tabungan as $row) {
    $total_saldo += $row['saldo']; // pastikan kolomnya benar
}

// Hitung statistik untuk chart
$stat_kegiatan = [];
foreach ($kegiatan as $k) {
    $jenis = $k['jenis_kegiatan'];
    if (!isset($stat_kegiatan[$jenis])) {
        $stat_kegiatan[$jenis] = 0;
    }
    $stat_kegiatan[$jenis]++;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard User - Sistem Sekolah</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        :root {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --success: #2ecc71;
            --info: #3498db;
            --warning: #f39c12;
            --light: #f8f9fa;
            --dark: #212529;
            --background: #f5fcf8;
            --card-bg: #ffffff;
            --text-primary: #2c3e50;
            --text-secondary: #7f8c8d;
            --border-radius: 16px;
            --box-shadow: 0 10px 30px rgba(46, 204, 113, 0.15);
            --transition: all 0.3s ease;
        }

        .dark-mode {
            --primary: #2ecc71;
            --primary-dark: #27ae60;
            --secondary: #3498db;
            --background: #121e17;
            --card-bg: #1a2a21;
            --text-primary: #ecf0f1;
            --text-secondary: #bdc3c7;
            --box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', 'Segoe UI', sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--background) 0%, #e8f5e9 100%);
            color: var(--text-primary);
            line-height: 1.6;
            min-height: 100vh;
            transition: var(--transition);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header Styles */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            margin-bottom: 20px;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            text-decoration: none;
        }

        .logo i {
            font-size: 28px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .home-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: var(--transition);
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .home-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
            gap: 12px;
        }

        .theme-toggle {
            background: var(--card-bg);
            border: none;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .theme-toggle:hover {
            transform: rotate(15deg);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            background: var(--card-bg);
            padding: 8px 16px;
            border-radius: 50px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .user-profile:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(46, 204, 113, 0.2);
        }

        .avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
        }

        .user-info {
            display: flex;
            flex-direction: column;
        }

        .user-name {
            font-weight: 600;
            color: var(--text-primary);
            font-size: 14px;
        }

        .user-role {
            font-size: 11px;
            color: var(--text-secondary);
        }

        .btn-logout {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--card-bg);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            color: var(--text-primary);
        }

        .btn-logout:hover {
            background: #ffeaea;
            color: #e74c3c;
            transform: translateY(-3px);
        }

        /* Dashboard Grid */
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
            position: relative;
            overflow: hidden;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(46, 204, 113, 0.2);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--primary-dark));
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .stat-title {
            font-size: 16px;
            font-weight: 600;
            color: var(--text-secondary);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
        }

        .stat-value {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 5px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .stat-desc {
            font-size: 14px;
            color: var(--text-secondary);
        }

        /* Main Content Area */
        .content-area {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 25px;
            margin-bottom: 30px;
        }

        .main-content {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 25px;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--border-radius);
            padding: 25px;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(46, 204, 113, 0.15);
        }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--text-primary);
        }

        .card-action {
            color: var(--primary);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: var(--transition);
        }

        .card-action:hover {
            gap: 8px;
            color: var(--primary-dark);
        }

        /* Tabs */
        .tabs {
            display: flex;
            background: var(--card-bg);
            border-radius: 12px;
            padding: 5px;
            margin-bottom: 20px;
            box-shadow: var(--box-shadow);
        }

        .tab-btn {
            flex: 1;
            padding: 12px 20px;
            text-align: center;
            background: none;
            border: none;
            border-radius: 10px;
            font-weight: 500;
            color: var(--text-secondary);
            cursor: pointer;
            transition: var(--transition);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .tab-btn:hover {
            color: var(--primary);
        }

        .tab-btn.active {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            box-shadow: 0 5px 15px rgba(46, 204, 113, 0.3);
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: block;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Tables */
        .table-responsive {
            overflow-x: auto;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            background-color: rgba(46, 204, 113, 0.1);
            color: var(--primary);
            font-weight: 600;
            text-align: left;
            padding: 15px;
            border-bottom: 2px solid rgba(46, 204, 113, 0.2);
        }

        .data-table td {
            padding: 15px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
        }

        .data-table tr {
            transition: var(--transition);
        }

        .data-table tr:hover {
            background-color: rgba(46, 204, 113, 0.03);
        }

        /* Badges */
        .badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-primary {
            background: rgba(46, 204, 113, 0.1);
            color: var(--primary);
        }

        .badge-success {
            background: rgba(46, 204, 113, 0.1);
            color: var(--success);
        }

        .badge-warning {
            background: rgba(243, 156, 18, 0.1);
            color: var(--warning);
        }

        /* Charts */
        .chart-container {
            position: relative;
            height: 250px;
            margin-top: 20px;
        }

        /* Activity List */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            background: var(--card-bg);
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
        }

        .activity-item:hover {
            transform: translateX(5px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.15);
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
        }

        .activity-content {
            flex: 1;
        }

        .activity-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .activity-desc {
            font-size: 13px;
            color: var(--text-secondary);
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .content-area {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .header-actions {
                width: 100%;
                justify-content: space-between;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .tabs {
                flex-direction: column;
            }
            
            .home-btn span {
                display: none;
            }
            
            .home-btn {
                padding: 10px;
            }
        }

        /* Animations */
        .animate-float {
            animation: float 3s ease-in-out infinite;
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }

        /* Notification */
        .notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: var(--card-bg);
            padding: 15px 25px;
            border-radius: 10px;
            box-shadow: var(--box-shadow);
            display: flex;
            align-items: center;
            gap: 10px;
            transform: translateX(150%);
            transition: transform 0.5s ease;
            z-index: 1000;
            border-left: 4px solid var(--primary);
        }

        .notification.show {
            transform: translateX(0);
        }

        .notification-icon {
            width: 24px;
            height: 24px;
            border-radius: 50%;
            background: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }

        /* Background shapes */
        .background-shapes {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            overflow: hidden;
        }

        .shape {
            position: absolute;
            border-radius: 50%;
            opacity: 0.1;
        }

        .shape-1 {
            width: 500px;
            height: 500px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            top: -250px;
            left: -250px;
            animation: floatShape 20s infinite ease-in-out;
        }

        .shape-2 {
            width: 400px;
            height: 400px;
            background: linear-gradient(135deg, var(--info), var(--success));
            bottom: -200px;
            right: -200px;
            animation: floatShape 25s infinite ease-in-out reverse;
        }

        @keyframes floatShape {
            0%, 100% { transform: translate(0, 0) rotate(0deg); }
            25% { transform: translate(-20px, 20px) rotate(5deg); }
            50% { transform: translate(10px, -10px) rotate(-5deg); }
            75% { transform: translate(15px, 15px) rotate(3deg); }
        }

        /* Leaf animation */
        .leaf {
            position: absolute;
            font-size: 24px;
            color: rgba(46, 204, 113, 0.2);
            animation: falling 15s infinite linear;
            z-index: -1;
        }

        @keyframes falling {
            0% {
                top: -10%;
                transform: translateX(0) rotate(0deg);
                opacity: 0.7;
            }
            100% {
                top: 110%;
                transform: translateX(100px) rotate(360deg);
                opacity: 0;
            }
        }
    </style>
</head>
<body>
    <div class="background-shapes">
        <div class="shape shape-1"></div>
        <div class="shape shape-2"></div>
        <!-- Animated leaves -->
        <div class="leaf" style="top: 10%; left: 5%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 20%; left: 15%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 5%; left: 25%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 15%; left: 85%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 25%; left: 75%;"><i class="fas fa-leaf"></i></div>
        <div class="leaf" style="top: 8%; left: 65%;"><i class="fas fa-leaf"></i></div>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="http://localhost/sekolah/index.php" class="logo">
                <i class="fas fa-graduation-cap"></i>
                <span>Sistem Sekolah</span>
            </a>
            
            <div class="header-actions">
                <a href="http://localhost/sekolah/index.php" class="home-btn">
                    <i class="fas fa-home"></i>
                    <span>Beranda</span>
                </a>
                
                <button class="theme-toggle" id="themeToggle">
                    <i class="fas fa-moon"></i>
                </button>
                
                <div class="user-profile">
                    <div class="avatar">
                        <?= strtoupper(substr($username, 0, 1)) ?>
                    </div>
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($username) ?></span>
                        <span class="user-role">User</span>
                    </div>
                </div>
                
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>
        </div>
        
        <!-- Stat Cards -->
        <div class="dashboard-grid">
            <div class="stat-card animate__animated animate__fadeInLeft">
                <div class="stat-header">
                    <div class="stat-title">Total Saldo Tabungan</div>
                    <div class="stat-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                </div>
                <div class="stat-value">Rp <?= number_format($total_saldo, 0, ',', '.') ?></div>
                <div class="stat-desc">Saldo terkini dari tabungan Anda</div>
            </div>
            
            <div class="stat-card animate__animated animate__fadeInUp">
                <div class="stat-header">
                    <div class="stat-title">Jumlah Kegiatan</div>
                    <div class="stat-icon">
                        <i class="fas fa-tasks"></i>
                    </div>
                </div>
                <div class="stat-value"><?= count($kegiatan) ?></div>
                <div class="stat-desc">Total kegiatan yang diikuti</div>
            </div>
            
            <div class="stat-card animate__animated animate__fadeInRight">
                <div class="stat-header">
                    <div class="stat-title">Nilai Rata-rata</div>
                    <div class="stat-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                </div>
                <div class="stat-value">
                    <?php
                    if (!empty($nilai)) {
                        $total_nilai = 0;
                        foreach ($nilai as $n) {
                            $total_nilai += $n['nilai'];
                        }
                        echo number_format($total_nilai / count($nilai), 1);
                    } else {
                        echo "N/A";
                    }
                    ?>
                </div>
                <div class="stat-desc">Rata-rata nilai kegiatan</div>
            </div>
        </div>
        
        <!-- Main Content Area -->
        <div class="content-area">
            <div class="main-content">
                <!-- Tabs Navigation -->
                <div class="tabs">
                    <button class="tab-btn active" data-tab="tabungan">
                        <i class="fas fa-piggy-bank"></i> Tabungan
                    </button>
                    <button class="tab-btn" data-tab="kegiatan">
                        <i class="fas fa-tasks"></i> Kegiatan
                    </button>
                    <button class="tab-btn" data-tab="nilai">
                        <i class="fas fa-star"></i> Nilai
                    </button>
                </div>
                
                <!-- Tabungan Content -->
                <div id="tabungan" class="tab-content active">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Riwayat Tabungan</h2>
                            <a href="#" class="card-action">
                                Lihat Semua <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Kelas</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($tabungan as $row): ?>
                                    <tr>
                                        <td><?= $row['id'] ?></td>
                                        <td><?= htmlspecialchars($row['user_id']) ?></td>
                                        <td><?= htmlspecialchars($row['kelas']) ?></td>
                                        <td>Rp <?= number_format($row['saldo'], 0, ',', '.') ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Kegiatan Content -->
                <div id="kegiatan" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Daftar Kegiatan</h2>
                            <a href="#" class="card-action">
                                Lihat Semua <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        <div class="table-responsive">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th>Nama Kegiatan</th>
                                        <th>Jenis</th>
                                        <th>Tanggal</th>
                                        <th>Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($kegiatan as $index => $row): 
                                        $user_nilai = "Belum dinilai";
                                        foreach ($nilai as $n) {
                                            if ($n['id_kegiatan'] == $row['id']) {
                                                $user_nilai = $n['nilai'];
                                                break;
                                            }
                                        }
                                        
                                        $nilai_class = "badge-warning";
                                        if (is_numeric($user_nilai)) {
                                            if ($user_nilai >= 85) $nilai_class = "badge-success";
                                            else if ($user_nilai >= 70) $nilai_class = "badge-primary";
                                            else if ($user_nilai >= 60) $nilai_class = "badge-warning";
                                        }
                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($row['nama_kegiatan']) ?></td>
                                        <td><span class="badge badge-primary"><?= ucfirst($row['jenis_kegiatan']) ?></span></td>
                                        <td><?= date('d M Y', strtotime($row['tanggal'])) ?></td>
                                        <td><span class="badge <?= $nilai_class ?>"><?= $user_nilai ?></span></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Nilai Content -->
                <div id="nilai" class="tab-content">
                    <div class="card">
                        <div class="card-header">
                            <h2 class="card-title">Detail Nilai Kegiatan</h2>
                            <a href="#" class="card-action">
                                Lihat Semua <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                        
                        <?php if (!empty($nilai)): ?>
                        <div class="chart-container">
                            <canvas id="nilaiChart"></canvas>
                        </div>
                        <?php else: ?>
                        <div style="text-align: center; padding: 30px; color: var(--text-secondary);">
                            <i class="fas fa-info-circle" style="font-size: 48px; margin-bottom: 15px;"></i>
                            <p>Belum ada nilai yang tercatat untuk kegiatan.</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <div class="sidebar">
                <!-- Progress Chart -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Distribusi Kegiatan</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="kegiatanChart"></canvas>
                    </div>
                </div>
                
                <!-- Recent Activity -->
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Aktivitas Terbaru</h2>
                        <a href="#" class="card-action">
                            Lihat Semua <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                    <div class="activity-list">
                        <?php 
                        $recent_kegiatan = array_slice($kegiatan, 0, 5);
                        foreach ($recent_kegiatan as $activity): 
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon">
                                <i class="fas fa-tasks"></i>
                            </div>
                            <div class="activity-content">
                                <div class="activity-title"><?= htmlspecialchars($activity['nama_kegiatan']) ?></div>
                                <div class="activity-desc"><?= date('d M Y', strtotime($activity['tanggal'])) ?></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="notification" id="welcomeNotification">
        <div class="notification-icon">
            <i class="fas fa-check"></i>
        </div>
        <span>Selamat datang di dashboard Anda, <?= htmlspecialchars($username) ?>!</span>
    </div>

    <script>
        // Theme Toggle
        const themeToggle = document.getElementById('themeToggle');
        const body = document.body;
        
        themeToggle.addEventListener('click', () => {
            body.classList.toggle('dark-mode');
            if (body.classList.contains('dark-mode')) {
                themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
                localStorage.setItem('theme', 'dark');
            } else {
                themeToggle.innerHTML = '<i class="fas fa-moon"></i>';
                localStorage.setItem('theme', 'light');
            }
        });
        
        // Load saved theme
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            body.classList.add('dark-mode');
            themeToggle.innerHTML = '<i class="fas fa-sun"></i>';
        }
        
        // Tab Switching
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabContents = document.querySelectorAll('.tab-content');
        
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const tabId = button.getAttribute('data-tab');
                
                // Deactivate all tabs
                tabButtons.forEach(btn => btn.classList.remove('active'));
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Activate current tab
                button.classList.add('active');
                document.getElementById(ttabId).classList.add('active');
            });
        });
        
        // Notification
        setTimeout(() => {
            document.getElementById('welcomeNotification').classList.add('show');
        }, 1000);
        
        setTimeout(() => {
            document.getElementById('welcomeNotification').classList.remove('show');
        }, 5000);
        
        // Charts
        <?php if (!empty($nilai)): ?>
        // Nilai Chart
        const nilaiCtx = document.getElementById('nilaiChart').getContext('2d');
        const nilaiChart = new Chart(nilaiCtx, {
            type: 'bar',
            data: {
                labels: [<?php foreach ($nilai as $n): ?>'<?= $n['nama_kegiatan'] ?>', <?php endforeach; ?>],
                datasets: [{
                    label: 'Nilai',
                    data: [<?php foreach ($nilai as $n): ?><?= $n['nilai'] ?>, <?php endforeach; ?>],
                    backgroundColor: 'rgba(46, 204, 113, 0.5)',
                    borderColor: 'rgba(46, 204, 113, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100
                    }
                }
            }
        });
        <?php endif; ?>
        
        // Kegiatan Chart
        const kegiatanCtx = document.getElementById('kegiatanChart').getContext('2d');
        const kegiatanChart = new Chart(kegiatanCtx, {
            type: 'doughnut',
            data: {
                labels: [<?php foreach ($stat_kegiatan as $key => $value): ?>'<?= ucfirst($key) ?>', <?php endforeach; ?>],
                datasets: [{
                    data: [<?php foreach ($stat_kegiatan as $value): ?><?= $value ?>, <?php endforeach; ?>],
                    backgroundColor: [
                        'rgba(46, 204, 113, 0.7)',
                        'rgba(52, 152, 219, 0.7)',
                        'rgba(243, 156, 18, 0.7)',
                        'rgba(155, 89, 182, 0.7)',
                        'rgba(231, 76, 60, 0.7)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Create additional falling leaves
        function createLeaves() {
            const background = document.querySelector('.background-shapes');
            for (let i = 0; i < 10; i++) {
                const leaf = document.createElement('div');
                leaf.className = 'leaf';
                leaf.innerHTML = '<i class="fas fa-leaf"></i>';
                leaf.style.left = Math.random() * 100 + '%';
                leaf.style.animationDelay = Math.random() * 15 + 's';
                leaf.style.fontSize = (Math.random() * 10 + 16) + 'px';
                background.appendChild(leaf);
            }
        }

        createLeaves();
    </script>
</body>
</html>