-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-12-2025 a las 21:32:17
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `basedatos_barberiapro`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ausencias`
--

CREATE TABLE `ausencias` (
  `id` int(11) NOT NULL,
  `id_barbero` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date DEFAULT NULL,
  `motivo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `barberos`
--

CREATE TABLE `barberos` (
  `id_barbero` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_inicio` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `avatar` varchar(50) DEFAULT '?'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `barberos`
--

INSERT INTO `barberos` (`id_barbero`, `nombre`, `activo`, `fecha_inicio`, `fecha_fin`, `avatar`) VALUES
(1, 'Victor', 1, NULL, NULL, '😎'),
(2, 'Pedro', 1, NULL, NULL, '😎'),
(3, 'Leo', 1, NULL, NULL, '😎'),
(5, 'Pedro', 1, NULL, NULL, '💈'),
(6, 'MARTIN', 1, NULL, NULL, '❤️');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloqueos`
--

CREATE TABLE `bloqueos` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bloqueos_barberos`
--

CREATE TABLE `bloqueos_barberos` (
  `id` int(11) NOT NULL,
  `id_barbero` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `motivo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuracion_emails`
--

CREATE TABLE `configuracion_emails` (
  `id` int(11) NOT NULL COMMENT 'ID único (siempre debe ser 1)',
  `horas_recordatorio` int(11) DEFAULT 24 COMMENT 'Horas antes del turno para enviar recordatorio (ej: 24 = un día antes)',
  `multa_cancelacion_tardia` decimal(10,2) DEFAULT 5000.00 COMMENT 'Monto en pesos que se cobra si cancela con menos de 24hs',
  `link_google_maps` varchar(500) DEFAULT 'https://g.page/r/tu-negocio/review' COMMENT 'URL para que los clientes dejen reseñas en Google Maps'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Configuración del sistema de emails automatizados';

--
-- Volcado de datos para la tabla `configuracion_emails`
--

INSERT INTO `configuracion_emails` (`id`, `horas_recordatorio`, `multa_cancelacion_tardia`, `link_google_maps`) VALUES
(1, 24, 5000.00, 'https://www.google.com/maps/search/?api=1&query=Victor+Barber+Club+Río+Grande+Tierra+del+Fuego\r\n');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `feriados`
--

CREATE TABLE `feriados` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `fecha_hora` datetime NOT NULL,
  `cliente` varchar(100) NOT NULL,
  `id_barbero` int(11) NOT NULL,
  `servicio` varchar(100) NOT NULL,
  `pago` varchar(50) NOT NULL,
  `id_cliente` varchar(20) NOT NULL,
  `telefono` varchar(50) NOT NULL,
  `token_cancelacion` varchar(64) DEFAULT NULL,
  `token_resena` varchar(64) DEFAULT NULL COMMENT 'Token único para tracking de reseñas de Google',
  `recordatorio_enviado` tinyint(1) DEFAULT 0 COMMENT '0 = No enviado, 1 = Email de recordatorio enviado',
  `resena_enviada` tinyint(1) DEFAULT 0 COMMENT '0 = No enviada, 1 = Email de reseña enviado',
  `estado` varchar(20) DEFAULT 'activa',
  `fecha_cancelacion` datetime DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `fecha_hora`, `cliente`, `id_barbero`, `servicio`, `pago`, `id_cliente`, `telefono`, `token_cancelacion`, `token_resena`, `recordatorio_enviado`, `resena_enviada`, `estado`, `fecha_cancelacion`, `email`) VALUES
(1, '2025-12-25 20:00:00', 'dasdsa', 2, 'Corte y Barba', 'Efectivo', '44330181', '29641571', 'c8868cb83fa751811bd9a14c08e93ff14ad7d0ae2d697b4079d8db97d54370af', 'fc3c0cfb4f69a451712d8abb430e6082', 0, 0, 'completada', NULL, NULL),
(2, '2025-12-25 12:00:00', 'martin', 2, 'Corte y Barba', 'Pago en Local', '33440', '2391239', '8f6f95cd1e2fa1e3b265ac7eb3124717e6d8c89e3be6986fe197287627efc5b3', 'daad238e39e47bdce0e4ab472266e488', 0, 0, 'completada', NULL, NULL),
(3, '2025-12-25 13:00:00', 'martinnn', 2, 'Corte y Barba', 'Pago en Local', '4421341', '239283', 'ff8d0a12b8bbb2ba0c161ae5e23841876c8abcb3d80973015a1b2a8707229137', '6534114385f8521fd8450e97744936b8', 0, 0, 'completada', NULL, NULL),
(4, '2025-12-26 15:00:00', 'masdmasd', 2, 'Asesoría', 'Transferencia', '4214214', '5435345', '91bb358096ee5bf0bebe2e6ac50df5d4c12650d54149ab212fc3f06266c843a7', '4adbf542bc358c73fd520b73b8e3af34', 0, 0, 'ausente', NULL, NULL),
(5, '2025-12-23 15:00:00', 'fgdgfdg', 3, 'Corte Clásico', 'Transferencia', '44330181', '2964621571', 'dd3cd768f9475ca9b4157246cd7e1eeed462467dcc56d163290a6bea8cad55c5', '9a6fd14cb7a2fad759b1b2c329944191', 0, 0, 'ausente', NULL, 'martincortez2003@gmail.com'),
(6, '2025-12-24 11:00:00', 'TRDTGFD', 2, 'Corte Clásico', 'Pago en Local', '4335435', '543543543', '7c1af34d6396b564c7f6af5fefb567d082981f07f4653aa01e80e3d5f62eda71', 'c9ae11b35afc8d3c341e1bbdbb7640cf', 0, 0, 'completada', NULL, 'RTRETRE@GMAIL.COM'),
(7, '2025-12-24 20:00:00', 'fdsfsd', 1, 'Corte y Barba', 'Transferencia', '432143', '3432432', '3815e12b58cdd6b70ac429af08f3630e228b1b019e60cce2d6c25dde999d577f', '79617e4ee7c8a55b7371d1f3550cf948', 0, 0, 'completada', NULL, 'dasdasd@gmail.com'),
(8, '2025-12-30 13:00:00', 'fdsfdsf', 1, 'Corte Clásico', 'Efectivo', '3424', '4534543', '2940d26fb778b982dce290157f44ae606dfee7a0d2bb3920637a1677348ec55c', '890302efd24492bbda39a87df88310e0', 0, 0, 'ausente', NULL, 'dsadasd@gmail.com'),
(9, '2025-12-23 13:00:00', '432432', 2, 'Corte y Barba', 'Transferencia', '535443', '543435', 'd1fd312a68d5c08ef211ef53d30b07cdab42beff9993712fda4cbfc4757a7ab6', 'b4f69f29cc13eec898225f89fab15742', 0, 0, 'ausente', NULL, 'martincortez2003@gmail.com'),
(10, '2025-12-23 13:00:00', 'juian', 1, 'Corte Clásico', 'Efectivo', '412412', '34324', 'b971a8f6c40f0a3e754c01ca4aec24132ade067fcb855de01451d01da10e2a06', '1ace9346603e03728c42c0f20a90f4e7', 0, 0, 'completada', NULL, 'jasduasd@gmail.com'),
(11, '2025-12-24 12:00:00', '4324234', 2, 'Corte Clásico', 'Efectivo', 'iiibgbygygy', 'nnhjnhjbn', 'a66433be11b6bd410006975ab5bd523a58a4d3d89f355ccf629b1badb86b36d1', '9e4507fc760f399df00ecbb375d5cd21', 0, 0, 'ausente', NULL, 'martincortez2003@gmail.com'),
(12, '2025-12-24 13:00:00', 'dardo cortez', 2, 'Corte y Barba', 'Pago en Local', '432432343243', '432432', '4b0e8b3858e060d3d9bab8435ca3d880507dc6ac6abf67040c0e9c3bef4119a7', 'b09bb5cf1c7927007422ccf025f98299', 0, 0, 'activa', NULL, '432432@gmail.com'),
(14, '2025-12-26 15:00:00', '', 6, 'Corte y Barba', '', '', '', '67197e71dc09e71bec82b28254ab2eb0b8e6e68e946a272d31737e79d0c6457b', 'cc811814dd962bf0052fc5ec42115168', 0, 0, 'activa', NULL, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servicios`
--

CREATE TABLE `servicios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servicios`
--

INSERT INTO `servicios` (`id`, `nombre`, `precio`, `activo`) VALUES
(1, 'Corte Clásico', 10000.00, 1),
(2, 'Corte Clásico + Barba', 15000.00, 1),
(3, 'Perfilado de Barba', 15000.00, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT 'admin@barberia.com',
  `token_recovery` varchar(100) DEFAULT NULL,
  `token_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `password`, `email`, `token_recovery`, `token_expira`) VALUES
(1, 'admin', '$2y$10$j8.v.d/..p.u.k.l/..q.u.e.r.t.y/..u.i.o.p.a.s.d.f.g.h.j', 'admin@barberia.com', NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `ausencias`
--
ALTER TABLE `ausencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_barbero` (`id_barbero`);

--
-- Indices de la tabla `barberos`
--
ALTER TABLE `barberos`
  ADD PRIMARY KEY (`id_barbero`);

--
-- Indices de la tabla `bloqueos`
--
ALTER TABLE `bloqueos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fecha` (`fecha`);

--
-- Indices de la tabla `bloqueos_barberos`
--
ALTER TABLE `bloqueos_barberos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_barbero` (`id_barbero`);

--
-- Indices de la tabla `configuracion_emails`
--
ALTER TABLE `configuracion_emails`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `feriados`
--
ALTER TABLE `feriados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fecha` (`fecha`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_reserva` (`fecha_hora`,`id_barbero`),
  ADD UNIQUE KEY `token_cancelacion` (`token_cancelacion`),
  ADD UNIQUE KEY `token_resena` (`token_resena`),
  ADD KEY `fk_barbero` (`id_barbero`),
  ADD KEY `idx_token_cancelacion` (`token_cancelacion`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_email_flags` (`email`,`recordatorio_enviado`,`resena_enviada`);

--
-- Indices de la tabla `servicios`
--
ALTER TABLE `servicios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `ausencias`
--
ALTER TABLE `ausencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `barberos`
--
ALTER TABLE `barberos`
  MODIFY `id_barbero` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `bloqueos`
--
ALTER TABLE `bloqueos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `bloqueos_barberos`
--
ALTER TABLE `bloqueos_barberos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `configuracion_emails`
--
ALTER TABLE `configuracion_emails`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'ID único (siempre debe ser 1)', AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `feriados`
--
ALTER TABLE `feriados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `servicios`
--
ALTER TABLE `servicios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `ausencias`
--
ALTER TABLE `ausencias`
  ADD CONSTRAINT `ausencias_ibfk_1` FOREIGN KEY (`id_barbero`) REFERENCES `barberos` (`id_barbero`) ON DELETE CASCADE;

--
-- Filtros para la tabla `bloqueos_barberos`
--
ALTER TABLE `bloqueos_barberos`
  ADD CONSTRAINT `bloqueos_barberos_ibfk_1` FOREIGN KEY (`id_barbero`) REFERENCES `barberos` (`id_barbero`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `fk_barbero` FOREIGN KEY (`id_barbero`) REFERENCES `barberos` (`id_barbero`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
