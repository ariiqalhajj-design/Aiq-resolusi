<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Pastikan user login
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: ../public/login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Ambil data tabungan user
$stmt = $pdo->prepare("SELECT tanggal, keterangan, jumlah FROM tabungan WHERE user_id = ? ORDER BY tanggal DESC");
$stmt->execute([$user_id]);
$transaksi = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Hitung total saldo
$total_stmt = $pdo->prepare("SELECT SUM(jumlah) AS saldo FROM tabungan WHERE user_id = ?");
$total_stmt->execute([$user_id]);
$saldo = $total_stmt->fetch(PDO::FETCH_ASSOC)['saldo'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tabungan Saya</title>
</head>
<body>
  <h1>Tabungan Saya</h1>
  <p>Saldo saat ini: <strong>Rp <?= number_format($saldo, 0, ',', '.') ?></strong></p>

  <h2>Riwayat Transaksi</h2>
  <table border="1" cellpadding="5" cellspacing="0">
    <tr>
      <th>Tanggal</th>
      <th>Keterangan</th>
      <th>Jumlah</th>
    </tr>
    <?php if (count($transaksi) > 0): ?>
      <?php foreach ($transaksi as $row): ?>
      <tr>
        <td><?= htmlspecialchars($row['tanggal']) ?></td>
        <td><?= htmlspecialchars($row['keterangan']) ?></td>
        <td><?= number_format($row['jumlah'], 0, ',', '.') ?></td>
      </tr>
      <?php endforeach; ?>
    <?php else: ?>
      <tr><td colspan="3">Belum ada transaksi</td></tr>
    <?php endif; ?>
  </table>

  <p><a href="../dashboard.php">Kembali ke Dashboard</a></p>
</body>
</html>
