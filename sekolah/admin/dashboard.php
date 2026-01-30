<?php
session_start();
require_once __DIR__ . '/../includes/db.php';

// Pastikan yang mengakses admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../public/login.php");
    exit;
}

$username = $_SESSION['username'];

// Ambil data statistik dari database
// Jumlah user
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role = 'user'");
$total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Total tabungan
$stmt = $pdo->query("SELECT SUM(saldo) as total FROM tabungan");
$total_tabungan = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// Jumlah kegiatan
$stmt = $pdo->query("SELECT COUNT(*) as total FROM kegiatan");
$total_kegiatan = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Aktivitas terbaru
$stmt = $pdo->query("
    SELECT 'user' as type, username, 'menambah user baru' as action, created_at as waktu 
    FROM users 
    WHERE role = 'user'
    UNION ALL
    SELECT 'tabungan' as type, u.username, CONCAT('mengupdate tabungan - Rp ', FORMAT(t.saldo, 0)) as action, t.updated_at as waktu
    FROM tabungan t
    JOIN users u ON t.user_id = u.id
    UNION ALL
    SELECT 'kegiatan' as type, u.username, CONCAT('menambah kegiatan - ', k.nama_kegiatan) as action, k.created_at as waktu
    FROM kegiatan k
    JOIN users u ON k.user_id = u.id
    ORDER BY waktu DESC 
    LIMIT 4
");
$recent_activities = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fungsi untuk mendapatkan icon dan class berdasarkan type aktivitas
function getActivityInfo($type) {
    switch ($type) {
        case 'user':
            return ['icon' => 'fas fa-user-plus', 'class' => 'activity-add'];
        case 'tabungan':
            return ['icon' => 'fas fa-edit', 'class' => 'activity-edit'];
        case 'kegiatan':
            return ['icon' => 'fas fa-tasks', 'class' => 'activity-add'];
        default:
            return ['icon' => 'fas fa-info-circle', 'class' => 'activity-add'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
  <style>
    :root {
      --primary: #0a5c36;
      --secondary: #1c7d4a;
      --accent: #2ecc71;
      --light: #eafaf1;
      --dark: #0a5c36;
      --success: #27ae60;
      --warning: #f39c12;
      --info: #3498db;
      --danger: #e74c3c;
      --leaf-1: #2ecc71;
      --leaf-2: #27ae60;
      --leaf-3: #219653;
    }
    
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Poppins', 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
    
    body {
      background: linear-gradient(135deg, #eafaf1 0%, #d4efdf 100%);
      color: #1c7d4a;
      display: flex;
      min-height: 100vh;
      overflow-x: hidden;
    }
    
    /* Sidebar Styles */
    .sidebar {
      width: 280px;
      background: linear-gradient(180deg, var(--primary) 0%, var(--secondary) 100%);
      color: white;
      padding: 20px 0;
      height: 100vh;
      position: fixed;
      overflow-y: auto;
      transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
      box-shadow: 0 0 25px rgba(10, 92, 54, 0.3);
      z-index: 1000;
      border-right: 1px solid rgba(255, 255, 255, 0.15);
    }
    
    .sidebar-header {
      padding: 0 20px 20px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.15);
      text-align: center;
      animation: fadeIn 1s ease;
    }
    
    .logo-container {
      display: flex;
      flex-direction: column;
      align-items: center;
      margin-bottom: 15px;
    }
    
    .logo {
      width: 80px;
      height: 80px;
      background: white;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-bottom: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.25);
      animation: pulse-green 2s infinite;
      border: 3px solid var(--accent);
      transition: all 0.5s ease;
    }
    
    .logo:hover {
      transform: rotate(360deg);
      box-shadow: 0 0 25px var(--accent);
    }
    
    .logo i {
      font-size: 36px;
      color: var(--primary);
      transition: all 0.5s ease;
    }
    
    .logo:hover i {
      transform: scale(1.2);
    }
    
    .sidebar-header h2 {
      font-size: 22px;
      margin-bottom: 5px;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
    }
    
    .sidebar-header p {
      font-size: 14px;
      opacity: 0.8;
    }
    
    .sidebar-menu {
      list-style: none;
      padding: 20px 0;
    }
    
    .sidebar-menu li {
      margin-bottom: 5px;
    }
    
    .sidebar-menu a {
      display: flex;
      align-items: center;
      padding: 15px 25px;
      color: white;
      text-decoration: none;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
      border-left: 4px solid transparent;
      position: relative;
      overflow: hidden;
    }
    
    .sidebar-menu a:before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: all 0.8s ease;
    }
    
    .sidebar-menu a:hover:before {
      left: 100%;
    }
    
    .sidebar-menu a:hover, .sidebar-menu a.active {
      background-color: rgba(255, 255, 255, 0.15);
      border-left-color: var(--accent);
      transform: translateX(10px);
      box-shadow: -5px 5px 15px rgba(0, 0, 0, 0.2);
    }
    
    .sidebar-menu i {
      margin-right: 15px;
      width: 20px;
      text-align: center;
      transition: all 0.3s ease;
    }
    
    .sidebar-menu a:hover i {
      transform: scale(1.3) rotate(10deg);
      color: var(--accent);
    }
    
    /* Main Content */
    .main-content {
      flex: 1;
      margin-left: 280px;
      padding: 25px;
      transition: all 0.5s ease;
    }
    
    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding: 25px;
      background: white;
      border-radius: 20px;
      box-shadow: 0 15px 35px rgba(46, 204, 113, 0.15);
      animation: slideDown 0.7s ease;
      border-left: 5px solid var(--accent);
      position: relative;
      overflow: hidden;
    }
    
    .header:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(46, 204, 113, 0.1), transparent);
      transform: translateX(-100%);
      animation: shimmer 3s infinite;
    }
    
    .welcome h1 {
      font-size: 28px;
      color: var(--primary);
      margin-bottom: 5px;
      background: linear-gradient(45deg, var(--primary), var(--accent));
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      animation: textShine 5s infinite alternate;
    }
    
    .welcome p {
      color: #27ae60;
      font-size: 16px;
    }
    
    .user-info {
      display: flex;
      align-items: center;
    }
    
    .user-info .admin-badge {
      background: linear-gradient(45deg, var(--accent), var(--success));
      color: white;
      padding: 10px 20px;
      border-radius: 25px;
      font-size: 14px;
      margin-right: 20px;
      animation: pulse-green 2s infinite;
      box-shadow: 0 5px 20px rgba(46, 204, 113, 0.4);
      transition: all 0.3s ease;
    }
    
    .admin-badge:hover {
      transform: translateY(-5px) scale(1.05);
    }
    
    .logout-btn {
      background: linear-gradient(45deg, var(--primary), var(--secondary));
      color: white;
      border: none;
      padding: 12px 25px;
      border-radius: 10px;
      cursor: pointer;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
      display: flex;
      align-items: center;
      box-shadow: 0 5px 20px rgba(10, 92, 54, 0.3);
      position: relative;
      overflow: hidden;
    }
    
    .logout-btn:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
      transform: translateX(-100%);
      transition: all 0.6s ease;
    }
    
    .logout-btn:hover:before {
      transform: translateX(100%);
    }
    
    .logout-btn:hover {
      transform: translateY(-5px);
      box-shadow: 0 10px 30px rgba(10, 92, 54, 0.5);
    }
    
    .logout-btn i {
      margin-right: 10px;
      transition: all 0.3s ease;
    }
    
    .logout-btn:hover i {
      transform: rotate(180deg);
    }
    
    /* Dashboard Cards */
    .dashboard-cards {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
      gap: 25px;
      margin-bottom: 35px;
    }
    
    .card {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 15px 35px rgba(46, 204, 113, 0.15);
      transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
      animation: fadeInUp 0.8s ease;
      opacity: 0;
      animation-fill-mode: forwards;
      position: relative;
      overflow: hidden;
      border-top: 4px solid var(--accent);
    }
    
    .card:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 5px;
      background: linear-gradient(to right, var(--primary), var(--accent));
    }
    
    .card:nth-child(1) { animation-delay: 0.1s; }
    .card:nth-child(2) { animation-delay: 0.2s; }
    .card:nth-child(3) { animation-delay: 0.3s; }
    
    .card:hover {
      transform: translateY(-15px) scale(1.03) rotate(1deg);
      box-shadow: 0 25px 50px rgba(46, 204, 113, 0.2);
    }
    
    .card-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .card-title {
      font-size: 18px;
      color: var(--dark);
      font-weight: 600;
    }
    
    .card-icon {
      width: 60px;
      height: 60px;
      border-radius: 18px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
    }
    
    .card:hover .card-icon {
      transform: rotate(15deg) scale(1.2);
      border-radius: 50%;
    }
    
    .icon-user {
      background: linear-gradient(45deg, var(--secondary), var(--success));
    }
    
    .icon-money {
      background: linear-gradient(45deg, var(--accent), var(--success));
    }
    
    .icon-activity {
      background: linear-gradient(45deg, var(--leaf-2), var(--leaf-3));
    }
    
    .card-value {
      font-size: 36px;
      font-weight: 800;
      color: var(--primary);
      margin: 20px 0;
      transition: all 0.3s ease;
      text-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .card:hover .card-value {
      color: var(--accent);
      transform: scale(1.1);
    }
    
    .card-info {
      color: var(--success);
      font-size: 15px;
    }
    
    /* Quick Actions */
    .quick-actions {
      margin-bottom: 35px;
    }
    
    .section-title {
      font-size: 24px;
      color: var(--primary);
      margin-bottom: 25px;
      padding-bottom: 15px;
      position: relative;
      animation: fadeIn 1s ease;
    }
    
    .section-title:after {
      content: '';
      position: absolute;
      bottom: -1px;
      left: 0;
      width: 60px;
      height: 4px;
      background: linear-gradient(to right, var(--primary), var(--accent));
      transition: all 0.5s ease;
      border-radius: 2px;
    }
    
    .section-title:hover:after {
      width: 120px;
    }
    
    .action-buttons {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
      gap: 25px;
    }
    
    .action-btn {
      background: white;
      border: none;
      border-radius: 15px;
      padding: 30px;
      text-align: center;
      cursor: pointer;
      transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
      text-decoration: none;
      color: var(--dark);
      position: relative;
      overflow: hidden;
      animation: fadeInUp 0.8s ease;
      opacity: 0;
      animation-fill-mode: forwards;
      box-shadow: 0 10px 25px rgba(46, 204, 113, 0.1);
      border: 2px solid #eafaf1;
    }
    
    .action-btn:nth-child(1) { animation-delay: 0.4s; }
    .action-btn:nth-child(2) { animation-delay: 0.5s; }
    .action-btn:nth-child(3) { animation-delay: 0.6s; }
    
    .action-btn:before {
      content: '';
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(to right, transparent, rgba(46, 204, 113, 0.1), transparent);
      transform: translateX(-100%);
      transition: all 0.8s ease;
    }
    
    .action-btn:hover:before {
      transform: translateX(100%);
    }
    
    .action-btn:hover {
      transform: translateY(-10px) scale(1.05);
      box-shadow: 0 20px 40px rgba(46, 204, 113, 0.2);
      border-color: var(--accent);
    }
    
    .action-btn i {
      font-size: 40px;
      margin-bottom: 20px;
      color: var(--primary);
      transition: all 0.5s ease;
    }
    
    .action-btn:hover i {
      color: var(--accent);
      transform: scale(1.3) rotate(10deg);
    }
    
    .action-btn span {
      display: block;
      font-weight: 700;
      transition: all 0.3s ease;
      font-size: 18px;
    }
    
    .action-btn:hover span {
      color: var(--accent);
      letter-spacing: 1px;
    }
    
    /* Recent Activity */
    .recent-activity {
      background: white;
      border-radius: 20px;
      padding: 30px;
      box-shadow: 0 15px 35px rgba(46, 204, 113, 0.15);
      animation: fadeIn 1.2s ease;
      border: 2px solid #eafaf1;
    }
    
    .activity-list {
      list-style: none;
    }
    
    .activity-item {
      display: flex;
      align-items: flex-start;
      padding: 25px 0;
      border-bottom: 2px solid #eafaf1;
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
      animation: slideInRight 0.6s ease;
      opacity: 0;
      animation-fill-mode: forwards;
    }
    
    .activity-item:nth-child(1) { animation-delay: 0.1s; }
    .activity-item:nth-child(2) { animation-delay: 0.2s; }
    .activity-item:nth-child(3) { animation-delay: 0.3s; }
    .activity-item:nth-child(4) { animation-delay: 0.4s; }
    
    .activity-item:hover {
      background-color: #f7fdf9;
      transform: translateX(15px);
      border-radius: 12px;
      box-shadow: 0 5px 15px rgba(46, 204, 113, 0.1);
    }
    
    .activity-item:last-child {
      border-bottom: none;
    }
    
    .activity-icon {
      width: 50px;
      height: 50px;
      border-radius: 14px;
      display: flex;
      align-items: center;
      justify-content: center;
      margin-right: 20px;
      flex-shrink: 0;
      transition: all 0.4s ease;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.15);
    }
    
    .activity-item:hover .activity-icon {
      transform: scale(1.2) rotate(5deg);
      border-radius: 50%;
    }
    
    .activity-add {
      background: linear-gradient(45deg, var(--accent), var(--success));
      color: white;
    }
    
    .activity-edit {
      background: linear-gradient(45deg, var(--leaf-2), var(--leaf-3));
      color: white;
    }
    
    .activity-delete {
      background: linear-gradient(45deg, var(--danger), #c0392b);
      color: white;
    }
    
    .activity-content {
      flex: 1;
    }
    
    .activity-content p {
      margin-bottom: 8px;
      font-weight: 500;
      color: var(--dark);
    }
    
    .activity-time {
      font-size: 14px;
      color: #7f8c8d;
    }
    
    /* Animations */
    @keyframes fadeIn {
      from { opacity: 0; }
      to { opacity: 1; }
    }
    
    @keyframes fadeInUp {
      from { 
        opacity: 0;
        transform: translateY(40px);
      }
      to { 
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes slideInRight {
      from {
        opacity: 0;
        transform: translateX(40px);
      }
      to {
        opacity: 1;
        transform: translateX(0);
      }
    }
    
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-40px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    
    @keyframes pulse-green {
      0% { 
        box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.4);
        transform: scale(1);
      }
      70% { 
        box-shadow: 0 0 0 15px rgba(46, 204, 113, 0);
        transform: scale(1.05);
      }
      100% { 
        box-shadow: 0 0 0 0 rgba(46, 204, 113, 0);
        transform: scale(1);
      }
    }
    
    @keyframes float {
      0% {
        transform: translateY(0px);
      }
      50% {
        transform: translateY(-15px);
      }
      100% {
        transform: translateY(0px);
      }
    }
    
    @keyframes shimmer {
      0% { transform: translateX(-100%); }
      100% { transform: translateX(100%); }
    }
    
    @keyframes textShine {
      0% {
        background-position: 0% 50%;
      }
      100% {
        background-position: 100% 50%;
      }
    }
    
    @keyframes leafFall {
      0% {
        transform: translateY(-100px) rotate(0deg);
        opacity: 0;
      }
      10% {
        opacity: 1;
      }
      90% {
        opacity: 0.8;
      }
      100% {
        transform: translateY(100vh) rotate(360deg);
        opacity: 0;
      }
    }
    
    /* Particle background */
    .particles {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      z-index: -1;
      overflow: hidden;
    }
    
    .particle {
      position: absolute;
      border-radius: 50%;
      animation: floatParticle 20s infinite linear;
      opacity: 0.6;
    }
    
    @keyframes floatParticle {
      0% {
        transform: translateY(0) translateX(0) rotate(0deg) scale(0.5);
        opacity: 0;
      }
      10% {
        opacity: 0.6;
      }
      90% {
        opacity: 0.6;
      }
      100% {
        transform: translateY(-100vh) translateX(100vw) rotate(360deg) scale(1.5);
        opacity: 0;
      }
    }

    /* Leaf particles */
    .leaf {
      position: absolute;
      background: transparent;
      opacity: 0.7;
      animation: leafFall 15s infinite linear;
      z-index: -1;
    }
    
    .leaf:before {
      content: '❦';
      color: var(--leaf-1);
      font-size: 24px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.2);
    }
    
    .leaf:nth-child(3n+1):before {
      color: var(--leaf-2);
    }
    
    .leaf:nth-child(3n+2):before {
      color: var(--leaf-3);
    }

    /* Glowing elements */
    .glow {
      position: relative;
    }
    
    .glow:after {
      content: '';
      position: absolute;
      top: -2px;
      left: -2px;
      right: -2px;
      bottom: -2px;
      z-index: -1;
      background: linear-gradient(45deg, var(--accent), var(--success), var(--secondary), var(--primary));
      background-size: 400%;
      border-radius: 20px;
      animation: glowing 3s ease-in-out infinite;
      filter: blur(10px);
      opacity: 0.7;
    }
    
    @keyframes glowing {
      0% { background-position: 0 0; }
      50% { background-position: 100% 0; }
      100% { background-position: 0 0; }
    }

    /* Hover effects for cards */
    .card-hover-effect {
      transition: all 0.4s cubic-bezier(0.68, -0.55, 0.27, 1.55);
    }
    
    .card-hover-effect:hover {
      transform: translateY(-8px) rotate(2deg);
      box-shadow: 0 20px 40px rgba(46, 204, 113, 0.25);
    }

    /* Responsive Styles */
    @media (max-width: 1200px) {
      .sidebar {
        width: 230px;
      }
      
      .main-content {
        margin-left: 230px;
      }
    }
    
    @media (max-width: 992px) {
      .sidebar {
        width: 80px;
        overflow: visible;
      }
      
      .sidebar-header h2, .sidebar-header p, .sidebar-menu span {
        display: none;
      }
      
      .sidebar-menu a {
        justify-content: center;
        padding: 20px;
      }
      
      .sidebar-menu i {
        margin-right: 0;
        font-size: 24px;
      }
      
      .main-content {
        margin-left: 80px;
        padding: 20px;
      }
      
      .dashboard-cards {
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
      }
    }
    
    @media (max-width: 768px) {
      .dashboard-cards {
        grid-template-columns: 1fr;
      }
      
      .action-buttons {
        grid-template-columns: 1fr;
      }
      
      .header {
        flex-direction: column;
        align-items: flex-start;
      }
      
      .user-info {
        margin-top: 20px;
        width: 100%;
        justify-content: space-between;
      }
    }
    
    @media (max-width: 576px) {
      .sidebar {
        width: 0;
        padding: 0;
        transform: translateX(-100%);
      }
      
      .sidebar.open {
        width: 280px;
        transform: translateX(0);
      }
      
      .main-content {
        margin-left: 0;
        padding: 15px;
      }
      
      .menu-toggle {
        display: block;
        position: fixed;
        top: 20px;
        left: 20px;
        z-index: 1000;
        background: var(--primary);
        color: white;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55);
        box-shadow: 0 5px 15px rgba(10, 92, 54, 0.3);
        animation: pulse-green 2s infinite;
      }
      
      .menu-toggle:hover {
        transform: rotate(90deg) scale(1.1);
      }
      
      .header {
        padding: 20px;
      }
      
      .card {
        padding: 20px;
      }
      
      .action-btn {
        padding: 20px;
      }
    }
  </style>
</head>
<body>
  <!-- Particle background -->
  <div class="particles" id="particles"></div>
  
  <!-- Leaf background -->
  <div class="leaves" id="leaves"></div>
  
  <!-- Sidebar Navigation -->
  <div class="sidebar" id="sidebar">
    <div class="sidebar-header">
      <div class="logo-container">
        <div class="logo">
          <i class="fas fa-graduation-cap"></i>
        </div>
        <h2>SMK CIT Manahilul Ilmi</h2>
        <p>Admin Dashboard</p>
      </div>
    </div>
    
    <ul class="sidebar-menu">
      <li><a href="#" class="active"><i class="fas fa-home"></i> <span>Dashboard</span></a></li>
      <li><a href="manage_user.php"><i class="fas fa-users-cog"></i> <span>Kelola User</span></a></li>
      <li><a href="tabungan.php"><i class="fas fa-wallet"></i> <span>Kelola Tabungan</span></a></li>
      <li><a href="kegiatan.php"><i class="fas fa-tasks"></i> <span>Kegiatan Diniyah</span></a></li>
      <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> <span>Logout</span></a></li>
    </ul>
  </div>
  
  <!-- Main Content -->
  <div class="main-content">
    <div class="header">
      <div class="welcome">
        <h1>Selamat Datang, <?= htmlspecialchars($username); ?></h1>
        <p>Halaman Dashboard Administrator</p>
      </div>
      
      <div class="user-info">
        <div class="admin-badge">Admin</div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a>
      </div>
    </div>
    
    <!-- Dashboard Cards -->
    <div class="dashboard-cards">
      <div class="card card-hover-effect">
        <div class="card-header">
          <div class="card-title">Total User</div>
          <div class="card-icon icon-user">
            <i class="fas fa-users"></i>
          </div>
        </div>
        <div class="card-value"><?= number_format($total_users) ?></div>
        <div class="card-info">Jumlah user terdaftar</div>
      </div>
      
      <div class="card card-hover-effect">
        <div class="card-header">
          <div class="card-title">Total Tabungan</div>
          <div class="card-icon icon-money">
            <i class="fas fa-coins"></i>
          </div>
        </div>
        <div class="card-value">Rp <?= number_format($total_tabungan, 0, ',', '.') ?></div>
        <div class="card-info">Total saldo tabungan</div>
      </div>
      
      <div class="card card-hover-effect glow">
        <div class="card-header">
          <div class="card-title">Kegiatan</div>
          <div class="card-icon icon-activity">
            <i class="fas fa-calendar-check"></i>
          </div>
        </div>
        <div class="card-value"><?= number_format($total_kegiatan) ?></div>
        <div class="card-info">Kegiatan tersedia</div>
      </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="quick-actions">
      <h2 class="section-title">Aksi Cepat</h2>
      
      <div class="action-buttons">
        <a href="manage_user.php" class="action-btn card-hover-effect">
          <i class="fas fa-users-cog"></i>
          <span>Kelola User</span>
        </a>
        
        <a href="tabungan.php" class="action-btn card-hover-effect">
          <i class="fas fa-wallet"></i>
          <span>Kelola Tabungan</span>
        </a>
        
        <a href="kegiatan.php" class="action-btn card-hover-effect glow">
          <i class="fas fa-tasks"></i>
          <span>Kegiatan Diniyah</span>
        </a>
      </div>
    </div>
    
    <!-- Recent Activity -->
    <div class="recent-activity">
      <h2 class="section-title">Aktivitas Terbaru</h2>
      
      <ul class="activity-list">
        <?php foreach ($recent_activities as $activity): 
          $activityInfo = getActivityInfo($activity['type']);
          $waktu = date('d M Y, H:i', strtotime($activity['waktu']));
        ?>
        <li class="activity-item card-hover-effect">
          <div class="activity-icon <?= $activityInfo['class'] ?>">
            <i class="<?= $activityInfo['icon'] ?>"></i>
          </div>
          <div class="activity-content">
            <p><strong><?= htmlspecialchars($activity['username']) ?></strong> <?= htmlspecialchars($activity['action']) ?></p>
            <div class="activity-time"><?= $waktu ?></div>
          </div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>

  <div class="menu-toggle" id="menuToggle">
    <i class="fas fa-bars"></i>
  </div>

  <script>
    // Toggle sidebar on mobile
    document.getElementById('menuToggle').addEventListener('click', function() {
      document.getElementById('sidebar').classList.toggle('open');
      this.classList.toggle('open');
    });
    
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
      const sidebar = document.getElementById('sidebar');
      const menuToggle = document.getElementById('menuToggle');
      
      if (window.innerWidth <= 576 && 
          !sidebar.contains(event.target) && 
          !menuToggle.contains(event.target) &&
          sidebar.classList.contains('open')) {
        sidebar.classList.remove('open');
      }
    });

    // Create floating particles
    function createParticles() {
      const particlesContainer = document.getElementById('particles');
      const particleCount = 25;
      
      for (let i = 0; i < particleCount; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        
        // Random properties
        const size = Math.random() * 25 + 5;
        const posX = Math.random() * 100;
        const delay = Math.random() * 20;
        const duration = Math.random() * 15 + 20;
        
        particle.style.width = `${size}px`;
        particle.style.height = `${size}px`;
        particle.style.left = `${posX}vw`;
        particle.style.animationDelay = `${delay}s`;
        particle.style.animationDuration = `${duration}s`;
        
        // Random green shades
        const greenShades = ['#0A5C36', '#1C7D4A', '#2ECC71', '#27AE60', '#219653'];
        const randomGreen = greenShades[Math.floor(Math.random() * greenShades.length)];
        particle.style.backgroundColor = randomGreen;
        particle.style.opacity = Math.random() * 0.5 + 0.2;
        
        particlesContainer.appendChild(particle);
      }
    }

    // Create falling leaves
    function createLeaves() {
      const leavesContainer = document.getElementById('leaves');
      const leafCount = 15;
      
      for (let i = 0; i < leafCount; i++) {
        const leaf = document.createElement('div');
        leaf.classList.add('leaf');
        
        // Random properties
        const posX = Math.random() * 100;
        const delay = Math.random() * 15;
        const duration = Math.random() * 10 + 15;
        const size = Math.random() * 20 + 15;
        
        leaf.style.left = `${posX}vw`;
        leaf.style.fontSize = `${size}px`;
        leaf.style.animationDelay = `${delay}s`;
        leaf.style.animationDuration = `${duration}s`;
        
        leavesContainer.appendChild(leaf);
      }
    }

    // Animasi saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
      createParticles();
      createLeaves();
      
      // Menampilkan notifikasi selamat datang
      setTimeout(function() {
        Swal.fire({
          title: 'Selamat Datang!',
          text: 'Halo <?= htmlspecialchars($username); ?>, selamat datang di Dashboard Admin',
          icon: 'success',
          confirmButtonText: 'Mulai',
          background: 'var(--primary)',
          color: 'white',
          confirmButtonColor: 'var(--accent)',
          timer: 3000,
          timerProgressBar: true,
          showClass: {
            popup: 'animate__animated animate__fadeInDown'
          },
          hideClass: {
            popup: 'animate__animated animate__fadeOutUp'
          }
        });
      }, 1000);

      // Add hover effects to cards
      const cards = document.querySelectorAll('.card');
      cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
          card.style.transform = 'translateY(-15px) scale(1.03) rotate(1deg)';
        });
        card.addEventListener('mouseleave', () => {
          card.style.transform = 'translateY(0) scale(1) rotate(0)';
        });
      });

      // Add typing animation to welcome text
      const welcomeText = document.querySelector('.welcome h1');
      const originalText = welcomeText.textContent;
      welcomeText.textContent = '';
      
      let i = 0;
      const typeWriter = () => {
        if (i < originalText.length) {
          welcomeText.textContent += originalText.charAt(i);
          i++;
          setTimeout(typeWriter, 50);
        }
      };
      
      setTimeout(typeWriter, 500);

      // Add ripple effect to buttons
      const buttons = document.querySelectorAll('.logout-btn, .action-btn');
      buttons.forEach(button => {
        button.addEventListener('click', function(e) {
          const x = e.clientX - e.target.getBoundingClientRect().left;
          const y = e.clientY - e.target.getBoundingClientRect().top;
          
          const ripple = document.createElement('span');
          ripple.classList.add('ripple-effect');
          ripple.style.left = `${x}px`;
          ripple.style.top = `${y}px`;
          
          this.appendChild(ripple);
          
          setTimeout(() => {
            ripple.remove();
          }, 600);
        });
      });
    });
  </script>
</body>
</html>