-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 16, 2026 at 09:45 PM
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
-- Database: `numismatica`
--

-- --------------------------------------------------------

--
-- Table structure for table `coleccion`
--

CREATE TABLE `coleccion` (
  `id` int(11) NOT NULL,
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `denominacion` varchar(100) NOT NULL,
  `anio` smallint(6) NOT NULL,
  `pais` varchar(80) NOT NULL,
  `metal` varchar(50) DEFAULT NULL,
  `estado` varchar(30) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_archivado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `coleccion`
--

INSERT INTO `coleccion` (`id`, `usuario_id`, `denominacion`, `anio`, `pais`, `metal`, `estado`, `descripcion`, `fecha_creacion`, `fecha_archivado`) VALUES
(3, 10, '10', 1960, 'Argentina', 'plata', 'mal', '', '2026-11-14 19:03:12', '2026-11-14 19:21:36'),
(5, 7, '50 reales', 1960, 'Brasil', 'plata', 'bien', '', '2026-11-14 20:08:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `apellido` varchar(80) NOT NULL,
  `dni` varchar(20) NOT NULL,
  `email` varchar(254) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL DEFAULT 'usuario',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_archivado` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `dni`, `email`, `password`, `rol`, `fecha_creacion`, `fecha_archivado`) VALUES
(7, 'Thomas', 'Mulvihill', '11111111', 'thomulvihill.sgcj@gmail.com', '$2y$10$/K6.XwUtf3bZV2MWqsSj7.g1EGA60cLcRhc/Xbe1iXRmGdxZqeUze', 'admin', '2026-11-09 15:11:50', NULL),
(8, 'Catalina', 'Mulvihill', '44444444', 'cata@gmail.com', '$2y$10$dXoM8I4eEXQoYyv8b4tcfecQJeOqHxyBM5QYTu.vS4OIFAnEzju8a', 'usuario', '2026-11-10 19:35:48', NULL),
(9, 'Pedro', 'Keto', '22222221', 'pedroketo@gmail.com', '$2y$10$OBYaoLC3EZ3eWirOXmzFpeHl4mbGTC8HQN7QxCyTJnlQ12zoS9u5O', 'usuario', '2026-11-11 19:58:59', '2026-11-14 19:29:44'),
(10, 'Cintia Sofia', 'Scardino', '25364782', 'cintia@gmail.com', '$2y$10$jUUQ/vgllCXKHpH0VAr65O383KspcLdcXU7ROt0ZSOWlQiIdZHliK', 'usuario', '2026-11-14 19:01:31', NULL),
(11, 'Guillermo', 'Mulvihill', '26170407', 'gpn@mail.com', '$2y$10$jIw53Nf65aWSRhvONEVRs.CiYeBOuyhE/SY48USVaOAqK6BNxF4fi', 'usuario', '2026-11-14 19:23:23', '2026-11-14 19:30:18');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `coleccion`
--
ALTER TABLE `coleccion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_coleccion_usuario` (`usuario_id`);

--
-- Indexes for table `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `dni` (`dni`),
  ADD UNIQUE KEY `password` (`password`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `coleccion`
--
ALTER TABLE `coleccion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `coleccion`
--
ALTER TABLE `coleccion`
  ADD CONSTRAINT `fk_coleccion_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
