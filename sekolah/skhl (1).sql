-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 30, 2026 at 08:05 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `skhl`
--

-- --------------------------------------------------------

--
-- Table structure for table `ganteng`
--

CREATE TABLE `ganteng` (
  `id` int(11) NOT NULL,
  `no_anggota` varchar(20) NOT NULL,
  `nama_anggota` varchar(100) NOT NULL,
  `alamat` text DEFAULT NULL,
  `no_telp` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kegiatan`
--

CREATE TABLE `kegiatan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `jenis_kegiatan` enum('it','diniyyah','inggris') NOT NULL,
  `nama_kegiatan` varchar(255) NOT NULL,
  `deskripsi` text DEFAULT NULL,
  `tanggal` date NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kegiatan`
--

INSERT INTO `kegiatan` (`id`, `user_id`, `jenis_kegiatan`, `nama_kegiatan`, `deskripsi`, `tanggal`, `created_at`, `updated_at`) VALUES
(1, 4, 'it', 'it', 'dia bisa membuat website', '2025-08-30', '2025-08-30 04:16:13', '2025-08-30 04:16:13'),
(2, 5, 'diniyyah', 'Hafalan Jus 30', 'dia dapat membaca dengan menggunakan makhorijul huruf denga baik', '2025-09-03', '2025-09-03 03:48:39', '2025-09-03 03:48:39');

-- --------------------------------------------------------

--
-- Table structure for table `tabungan`
--

CREATE TABLE `tabungan` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `saldo` decimal(15,2) DEFAULT 0.00,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tabungan`
--

INSERT INTO `tabungan` (`id`, `user_id`, `nama`, `kelas`, `saldo`, `created_at`, `updated_at`) VALUES
(6, 3, NULL, NULL, 10000.00, '2025-08-30 02:50:07', '2025-08-30 02:50:07'),
(8, 4, NULL, NULL, 100000.00, '2025-08-30 04:14:52', '2025-08-30 04:14:52'),
(9, 3, NULL, NULL, 100000.00, '2025-08-30 07:12:02', '2025-08-30 07:12:02'),
(12, 4, NULL, NULL, 20000.00, '2025-09-02 13:19:20', '2025-09-02 13:19:20'),
(13, 5, NULL, NULL, 20000.00, '2025-09-03 03:46:56', '2025-09-03 03:46:56'),
(14, 4, NULL, NULL, 100000.00, '2025-09-04 08:53:06', '2025-09-04 08:53:06'),
(15, 3, NULL, NULL, 20000.00, '2025-09-04 08:59:47', '2025-09-04 08:59:47'),
(16, 6, 'banipildun', '11', 0.00, '2025-12-20 10:41:33', '2025-12-20 10:41:33'),
(17, 6, NULL, NULL, 20000000.00, '2025-12-20 10:42:22', '2025-12-20 10:42:22');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `nama` varchar(100) DEFAULT NULL,
  `kelas` varchar(50) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','user') NOT NULL DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `nama`, `kelas`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'ariiq', NULL, NULL, 'ariiq@example.com', 'Admin1234', 'admin', '2025-08-28 02:08:41'),
(3, 'azzam', NULL, NULL, 'slebew@gmail.com', 'Awas23', 'user', '2025-08-29 09:57:51'),
(4, 'faiq', NULL, NULL, 'jawir@gmail.com', 'Faiq11', 'user', '2025-08-30 04:14:12'),
(5, 'soleh', NULL, NULL, 'cihuy@gmail.com', 'Soleh3', 'user', '2025-09-01 03:52:31'),
(6, 'bani', NULL, NULL, 'bani@gmail.com', 'Bani123', 'user', '2025-12-20 10:41:33');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ganteng`
--
ALTER TABLE `ganteng`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tabungan`
--
ALTER TABLE `tabungan`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_user_tabungan` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ganteng`
--
ALTER TABLE `ganteng`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kegiatan`
--
ALTER TABLE `kegiatan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tabungan`
--
ALTER TABLE `tabungan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `kegiatan`
--
ALTER TABLE `kegiatan`
  ADD CONSTRAINT `kegiatan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tabungan`
--
ALTER TABLE `tabungan`
  ADD CONSTRAINT `fk_tabungan_user_id` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_user_tabungan` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tabungan_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
