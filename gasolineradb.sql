-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 04-06-2025 a las 23:01:22
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `gasolineradb`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admin`
--

CREATE TABLE `admin` (
  `documento` int(20) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `admin`
--

INSERT INTO `admin` (`documento`, `password`) VALUES
(123, '123');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id_cliente` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `documento_cliente` int(11) DEFAULT NULL,
  `placa_vehiculo` varchar(20) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `millas` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id_cliente`, `nombre`, `documento_cliente`, `placa_vehiculo`, `telefono`, `millas`) VALUES
(5, 'Juan Jauregui', 1094047462, 'JNV531 ', '3102001904', 26),
(6, 'Carlos Vega', 1045831725, 'HHG444 ', '3192364620', 29),
(7, 'Juan Ramirez', 1092487784, 'XQE940', '3139897709', 10),
(8, 'Luis Mejia', 1069422704, 'SQL123', '3117884532', 6),
(10, 'Karina Colmenarez', 1349777900, 'HJQ472', '3214748865', 62),
(11, 'Daniel Vega', 1095404417, 'JUX474', '3228839021', 6),
(13, 'Clara Muñoz', 1092938367, 'DIV338', '3155782062', 0),
(14, 'Juan Mantilla', 1009045337, 'XQB94G', '3024086399', 69),
(19, 'Cristiano Ronaldo', 1028492988, 'CRJ007', '3108294710', 26),
(20, 'Pipe Rojas', 1092341439, 'EEI151', '3176924883', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `combustibles`
--

CREATE TABLE `combustibles` (
  `id_combustible` int(11) NOT NULL,
  `tipo` varchar(50) DEFAULT NULL,
  `precio_galon` decimal(10,2) DEFAULT NULL,
  `stock_actual` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `combustibles`
--

INSERT INTO `combustibles` (`id_combustible`, `tipo`, `precio_galon`, `stock_actual`) VALUES
(29, 'Extra', 5.00, 107.66),
(30, 'Corriente', 7.00, 99.80),
(31, 'Diesel', 5.00, 91.00),
(32, 'Gas Natural', 7.50, 93.10);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleados`
--

CREATE TABLE `empleados` (
  `id_empleado` int(11) NOT NULL,
  `nombre` varchar(100) DEFAULT NULL,
  `documento` varchar(20) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `cargo` varchar(50) DEFAULT NULL,
  `turno_id` int(11) DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `fecha_ingreso` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `empleados`
--

INSERT INTO `empleados` (`id_empleado`, `nombre`, `documento`, `password`, `cargo`, `turno_id`, `telefono`, `fecha_ingreso`) VALUES
(1, 'James Quintero', '1094047461', '12345', 'Empleado', 2, '3107765334', '2025-05-10'),
(11, 'Vini Jr', '1094047464', '12345', 'Empleado', 1, '3245255558', '2025-05-13'),
(12, 'Kylian Mbappe', '1099832088', '9', 'Empleado', 2, '3109498426', '2025-06-03'),
(15, 'Gonzalo Ramos', '1096028033', '023', 'Empleado', 3, '3101801169', '2025-06-03'),
(16, 'Luis Diaz', '1007366033', '433', 'Empleado', 3, '3173108822', '2025-06-03'),
(17, 'Carlos Perez', '1094057710', '1972', 'Empleado', 2, '3106028914', '2025-06-04'),
(18, 'Ibai Llanos', '1389369910', 'llanos23', 'Empleado', 2, '3222807773', '2025-06-04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `suministros`
--

CREATE TABLE `suministros` (
  `id_suministro` int(11) NOT NULL,
  `id_combustible` int(11) DEFAULT NULL,
  `cantidad_recibida` decimal(10,2) DEFAULT NULL,
  `fecha_recepcion` datetime DEFAULT NULL,
  `proveedor` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `suministros`
--

INSERT INTO `suministros` (`id_suministro`, `id_combustible`, `cantidad_recibida`, `fecha_recepcion`, `proveedor`) VALUES
(1, 29, 56.00, '2025-05-23 06:03:00', 'SA'),
(2, 31, 120.00, '2025-05-23 12:50:00', 'TERPEL'),
(3, 30, 77.80, '2025-06-04 01:51:30', 'ECO'),
(4, 32, 83.60, '2025-06-04 01:51:47', 'SA');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `turnos`
--

CREATE TABLE `turnos` (
  `id_turno` int(11) NOT NULL,
  `descripcion` varchar(50) DEFAULT NULL,
  `hora_inicio` time DEFAULT NULL,
  `hora_fin` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `turnos`
--

INSERT INTO `turnos` (`id_turno`, `descripcion`, `hora_inicio`, `hora_fin`) VALUES
(1, 'Mañana', '06:00:00', '14:00:00'),
(2, 'Tarde', '14:00:00', '22:00:00'),
(3, 'Noche', '22:00:00', '06:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id_venta` int(11) NOT NULL,
  `fecha_hora` datetime DEFAULT NULL,
  `id_empleado` int(11) DEFAULT NULL,
  `id_combustible` int(11) DEFAULT NULL,
  `id_cliente` int(11) DEFAULT NULL,
  `galones_vendidos` decimal(10,2) DEFAULT NULL,
  `total_pagado` decimal(10,2) DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id_venta`, `fecha_hora`, `id_empleado`, `id_combustible`, `id_cliente`, `galones_vendidos`, `total_pagado`, `metodo_pago`) VALUES
(17, '2025-05-23 05:58:47', 1, 29, 6, 6.00, 30.00, 'Transferencia'),
(18, '2025-05-23 12:48:06', 1, 30, 7, 10.00, 60.00, 'Tarjeta de Crédito'),
(19, '2025-05-29 14:14:41', 1, 31, 10, 56.00, 280.00, 'Tarjeta de Débito'),
(23, '2025-05-29 14:43:42', 1, 30, 5, 5.00, 30.00, 'Tarjeta de Crédito'),
(28, '2025-06-03 01:22:50', 1, 30, 11, 3.00, 18.00, 'Tarjeta de Crédito'),
(29, '2025-06-03 01:24:44', 12, 30, 11, 3.00, 18.00, 'Tarjeta de Crédito'),
(30, '2025-06-04 03:36:39', 1, 32, 5, 18.00, 135.00, 'Tarjeta de Crédito'),
(38, '2025-06-04 06:07:58', 1, 30, 5, 2.00, 14.00, 'Tarjeta de Crédito'),
(39, '2025-06-04 06:10:01', 1, 32, 6, 23.00, 172.50, 'Efectivo'),
(42, '2025-06-04 06:45:05', 1, 31, 19, 3.00, 15.00, 'Tarjeta de Débito'),
(43, '2025-06-04 16:37:30', 1, 31, 20, 5.00, 25.00, 'Tarjeta de Débito'),
(44, '2025-06-04 22:44:17', 1, 31, 19, 23.00, 115.00, 'Tarjeta de Débito');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`documento`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `combustibles`
--
ALTER TABLE `combustibles`
  ADD PRIMARY KEY (`id_combustible`);

--
-- Indices de la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD PRIMARY KEY (`id_empleado`),
  ADD KEY `turno_id` (`turno_id`);

--
-- Indices de la tabla `suministros`
--
ALTER TABLE `suministros`
  ADD PRIMARY KEY (`id_suministro`),
  ADD KEY `suministros_ibfk_1` (`id_combustible`);

--
-- Indices de la tabla `turnos`
--
ALTER TABLE `turnos`
  ADD PRIMARY KEY (`id_turno`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id_venta`),
  ADD KEY `id_empleado` (`id_empleado`),
  ADD KEY `id_combustible` (`id_combustible`),
  ADD KEY `id_cliente` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id_cliente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `combustibles`
--
ALTER TABLE `combustibles`
  MODIFY `id_combustible` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- AUTO_INCREMENT de la tabla `empleados`
--
ALTER TABLE `empleados`
  MODIFY `id_empleado` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT de la tabla `suministros`
--
ALTER TABLE `suministros`
  MODIFY `id_suministro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `turnos`
--
ALTER TABLE `turnos`
  MODIFY `id_turno` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=45;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `empleados`
--
ALTER TABLE `empleados`
  ADD CONSTRAINT `empleados_ibfk_1` FOREIGN KEY (`turno_id`) REFERENCES `turnos` (`id_turno`);

--
-- Filtros para la tabla `suministros`
--
ALTER TABLE `suministros`
  ADD CONSTRAINT `suministros_ibfk_1` FOREIGN KEY (`id_combustible`) REFERENCES `combustibles` (`id_combustible`);

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `ventas_ibfk_1` FOREIGN KEY (`id_empleado`) REFERENCES `empleados` (`id_empleado`),
  ADD CONSTRAINT `ventas_ibfk_2` FOREIGN KEY (`id_combustible`) REFERENCES `combustibles` (`id_combustible`),
  ADD CONSTRAINT `ventas_ibfk_3` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id_cliente`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
