-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 18-03-2026 a las 20:31:34
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET FOREIGN_KEY_CHECKS = 0;


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `delicias_bakery`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `rol` enum('admin','super_admin') NOT NULL DEFAULT 'admin',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id`, `nombre`, `email`, `password`, `rol`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Administrador', 'admin@delicias.com', '$2a$10$Rvqxxvu028dfjydvtBOfqusnyuA2tNBfNiZ6kiE7IRkj3qplWMPKO', 'super_admin', 1, '2025-10-10 22:39:16.000', '2025-10-10 22:39:16.000');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `calificaciones`
--

CREATE TABLE `calificaciones` (
  `id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `estrellas` int(11) NOT NULL,
  `comentario` text DEFAULT NULL,
  `fecha` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catalogo_distritos_huancayo`
--

CREATE TABLE `catalogo_distritos_huancayo` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `orden_lista` int(11) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `catalogo_distritos_huancayo`
--

INSERT INTO `catalogo_distritos_huancayo` (`id`, `nombre`, `orden_lista`, `activo`, `created_at`) VALUES
(1, 'Huancayo', 1, 1, '2026-03-18 19:03:08'),
(2, 'El Tambo', 2, 1, '2026-03-18 19:03:08'),
(3, 'Chilca', 3, 1, '2026-03-18 19:03:08'),
(4, 'Carhuacallanga', 4, 1, '2026-03-18 19:03:08'),
(5, 'Cullhuas', 5, 1, '2026-03-18 19:03:08'),
(6, 'Chacapampa', 6, 1, '2026-03-18 19:03:08'),
(7, 'Chicche', 7, 1, '2026-03-18 19:03:08'),
(8, 'Chongos Alto', 8, 1, '2026-03-18 19:03:08'),
(9, 'Chupuro', 9, 1, '2026-03-18 19:03:08'),
(10, 'Colca', 10, 1, '2026-03-18 19:03:08'),
(11, 'Huacrapuquio', 11, 1, '2026-03-18 19:03:08'),
(12, 'Hualhuas', 12, 1, '2026-03-18 19:03:08'),
(13, 'Huancán', 13, 1, '2026-03-18 19:03:08'),
(14, 'Huasicancha', 14, 1, '2026-03-18 19:03:08'),
(15, 'Huayucachi', 15, 1, '2026-03-18 19:03:08'),
(16, 'Ingenio', 16, 1, '2026-03-18 19:03:08'),
(17, 'Pariahuanca', 17, 1, '2026-03-18 19:03:08'),
(18, 'Pilcomayo', 18, 1, '2026-03-18 19:03:08'),
(19, 'Pucará', 19, 1, '2026-03-18 19:03:08'),
(20, 'Quichuay', 20, 1, '2026-03-18 19:03:08'),
(21, 'Quilcas', 21, 1, '2026-03-18 19:03:08'),
(22, 'Santo Domingo de Acobamba', 22, 1, '2026-03-18 19:03:08'),
(23, 'Saño', 23, 1, '2026-03-18 19:03:08'),
(24, 'Sapallanga', 24, 1, '2026-03-18 19:03:08'),
(25, 'Sicaya', 25, 1, '2026-03-18 19:03:08'),
(26, 'Viques', 26, 1, '2026-03-18 19:03:08'),
(27, 'San Agustín de Cajas', 27, 1, '2026-03-18 19:03:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `descripcion` varchar(191) DEFAULT NULL,
  `imagen` varchar(191) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`, `imagen`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Panes', 'Variedad de panes frescos y artesanales', 'categorias/categoria-1760734219985-415066369.png', 1, '2025-10-10 22:39:16.000', '2025-10-17 19:50:19.000'),
(3, 'Galletas', 'TOSTADA ', 'categorias/categoria-1771647772239-919254697.png', 1, '2025-10-10 22:39:16.000', '2026-02-21 04:22:52.000'),
(4, 'Postres', 'Postres especiales y dulces', NULL, 1, '2025-10-10 22:39:16.000', '2025-10-10 22:39:16.000'),
(5, 'Tortas', 'Tortas personalizadas para celebraciones', NULL, 1, '2025-10-10 22:39:16.000', '2025-10-10 22:39:16.000');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `categorias_app`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `categorias_app` (
`id_categoria` int(11)
,`nombre` varchar(191)
,`estado` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `comprobantes_app`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `comprobantes_app` (
`id` int(11)
,`pedido_id` int(11)
,`comprobante_serie_id` int(11)
,`tipo` varchar(20)
,`serie` varchar(10)
,`numero` int(11)
,`numero_formateado` varchar(30)
,`archivo_nombre` varchar(255)
,`archivo_ruta` varchar(500)
,`mime` varchar(100)
,`size_bytes` int(11)
,`created_at` datetime(3)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobantes`
--

CREATE TABLE `comprobantes` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `comprobante_serie_id` int(11) DEFAULT NULL,
  `tipo` varchar(20) NOT NULL,
  `serie` varchar(10) NOT NULL,
  `numero` int(11) NOT NULL,
  `numero_formateado` varchar(30) NOT NULL,
  `archivo_nombre` varchar(255) NOT NULL,
  `archivo_ruta` varchar(500) NOT NULL,
  `mime` varchar(100) DEFAULT NULL,
  `size_bytes` int(11) DEFAULT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `comprobantes`
--

INSERT INTO `comprobantes` (`id`, `pedido_id`, `comprobante_serie_id`, `tipo`, `serie`, `numero`, `numero_formateado`, `archivo_nombre`, `archivo_ruta`, `mime`, `size_bytes`, `created_at`) VALUES
(1, 21, 1, 'boleta', 'B001', 1, 'B001-00000001', 'pedido-21-B001-00000001.pdf', 'comprobantes/pedido-21-B001-00000001.pdf', 'application/pdf', 1704, '2026-03-18 19:20:48.365');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `comprobante_series`
--

CREATE TABLE `comprobante_series` (
  `id` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `serie` varchar(10) NOT NULL,
  `correlativo` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish2_ci;

--
-- Volcado de datos para la tabla `comprobante_series`
--

INSERT INTO `comprobante_series` (`id`, `tipo`, `serie`, `correlativo`) VALUES
(1, 'boleta', 'B001', 1),
(2, 'factura', 'F001', 0);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `detalle_pedido_app`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `detalle_pedido_app` (
`id_detalle` int(11)
,`id_pedido` int(11)
,`id_producto` int(11)
,`cantidad` int(11)
,`subtotal` decimal(10,2)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `direcciones_app`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `direcciones_app` (
`id` int(11)
,`usuario_id` int(11)
,`distrito_id` int(11)
,`direccion` text
,`distrito` varchar(120)
,`numero_casa` varchar(20)
,`referencia` text
,`latitud` decimal(10,8)
,`longitud` decimal(11,8)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `distrito_id` int(11) DEFAULT NULL,
  `direccion` text NOT NULL,
  `distrito` varchar(120) DEFAULT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `referencia` text DEFAULT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `login_logs`
--

CREATE TABLE `login_logs` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `tipo_usuario` enum('usuario','admin') NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `exitoso` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `login_logs`
--

INSERT INTO `login_logs` (`id`, `usuario_id`, `admin_id`, `tipo_usuario`, `ip_address`, `user_agent`, `exitoso`, `created_at`) VALUES
(115, NULL, 1, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/145.0.0.0 Safari/537.36', 1, '2026-03-18 19:18:53.591'),
(116, NULL, 1, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 1, '2026-03-18 19:19:27.570'),
(117, NULL, 1, 'admin', '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 Safari/537.36 Edg/144.0.0.0', 1, '2026-03-18 19:19:56.322');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `titulo` varchar(120) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `leido` tinyint(4) DEFAULT 0,
  `fecha` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `metodo` enum('yape','tarjeta','contra_entrega') NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','pagado') DEFAULT 'pendiente',
  `fecha` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `direccion_id` int(11) DEFAULT NULL,
  `distrito_entrega_id` int(11) DEFAULT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','confirmado','en_preparacion','listo','entregado','cancelado') NOT NULL DEFAULT 'pendiente',
  `fecha_entrega` date DEFAULT NULL,
  `direccion_entrega` text DEFAULT NULL,
  `distrito_entrega` varchar(120) DEFAULT NULL,
  `numero_casa_entrega` varchar(20) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `usuario_id`, `direccion_id`, `distrito_entrega_id`, `total`, `estado`, `fecha_entrega`, `direccion_entrega`, `distrito_entrega`, `numero_casa_entrega`, `telefono_contacto`, `notas`, `created_at`, `updated_at`) VALUES
(1, 11, NULL, 1, 492.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:01.000', '2026-03-18 19:00:01.000'),
(2, 11, NULL, 1, 24.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:02.000', '2026-03-18 19:00:02.000'),
(3, 11, NULL, 1, 246.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:03.000', '2026-03-18 19:00:03.000'),
(4, 11, NULL, 1, 36.03, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:04.000', '2026-03-18 19:00:04.000'),
(5, 11, NULL, 1, 1599.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:05.000', '2026-03-18 19:00:05.000'),
(6, 11, NULL, 1, 48.04, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:06.000', '2026-03-18 19:00:06.000'),
(7, 11, NULL, 1, 48.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:07.000', '2026-03-18 19:00:07.000'),
(8, 11, NULL, 1, 48.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:08.000', '2026-03-18 19:00:08.000'),
(9, 11, NULL, 1, 48.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:09.000', '2026-03-18 19:00:09.000'),
(10, 11, NULL, 1, 36.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:10.000', '2026-03-18 19:00:10.000'),
(11, 11, NULL, 1, 36.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:11.000', '2026-03-18 19:00:11.000'),
(12, 11, NULL, 1, 41.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:12.000', '2026-03-18 19:00:12.000'),
(13, 11, NULL, 1, 24.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:13.000', '2026-03-18 19:00:13.000'),
(14, 11, NULL, 1, 26.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:14.000', '2026-03-18 19:00:14.000'),
(15, 11, NULL, 1, 3.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:15.000', '2026-03-18 19:00:15.000'),
(16, 11, NULL, 1, 13.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:16.000', '2026-03-18 19:00:16.000'),
(17, 11, NULL, 1, 13.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:17.000', '2026-03-18 19:00:17.000'),
(18, 11, NULL, 1, 12.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:18.000', '2026-03-18 19:00:18.000'),
(19, 11, NULL, 1, 6.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:19.000', '2026-03-18 19:00:19.000'),
(20, 11, NULL, 1, 13.00, 'pendiente', '2026-03-18', 'Jr. Lima', 'Huancayo', '350', '974268690', 'Pedido reconstruido desde pedido_detalles para mantener integridad referencial.', '2026-03-18 19:00:20.000', '2026-03-18 19:00:20.000'),
(21, 11, NULL, 1, 19.00, 'pendiente', '2026-03-19', 'Jr. Lima', 'Huancayo', '350', '974268690', 'hol', '2026-03-18 19:20:47.783', '2026-03-18 19:20:47.783');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `pedidos_app`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `pedidos_app` (
`id_pedido` int(11)
,`id_usuario` int(11)
,`id_direccion` int(11)
,`total` decimal(10,2)
,`estado` enum('pendiente','confirmado','en_preparacion','listo','entregado','cancelado')
,`fecha` datetime(3)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `pagos_app`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `pagos_app` (
`id` int(11)
,`pedido_id` int(11)
,`metodo` enum('yape','tarjeta','contra_entrega')
,`monto` decimal(10,2)
,`estado` enum('pendiente','pagado')
,`fecha` datetime(3)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_detalles`
--

CREATE TABLE `pedido_detalles` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pedido_detalles`
--

INSERT INTO `pedido_detalles` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 3, 4, 123.00, 492.00),
(2, 2, 2, 1, 12.00, 12.00),
(3, 2, 1, 1, 12.00, 12.00),
(4, 3, 3, 2, 123.00, 246.00),
(5, 4, 4, 3, 12.01, 36.03),
(6, 5, 3, 13, 123.00, 1599.00),
(7, 6, 4, 4, 12.01, 48.04),
(8, 7, 9, 4, 12.00, 48.00),
(9, 8, 9, 4, 12.00, 48.00),
(10, 9, 9, 4, 12.00, 48.00),
(11, 10, 9, 3, 12.00, 36.00),
(12, 11, 9, 3, 12.00, 36.00),
(13, 12, 11, 2, 13.00, 26.00),
(14, 12, 9, 1, 12.00, 12.00),
(15, 12, 10, 1, 3.00, 3.00),
(16, 13, 9, 2, 12.00, 24.00),
(17, 14, 11, 2, 13.00, 26.00),
(18, 15, 10, 1, 3.00, 3.00),
(19, 16, 11, 1, 13.00, 13.00),
(20, 17, 11, 1, 13.00, 13.00),
(21, 18, 9, 1, 12.00, 12.00),
(22, 19, 10, 2, 3.00, 6.00),
(23, 20, 11, 1, 13.00, 13.00),
(24, 21, 13, 2, 6.00, 12.00),
(25, 21, 16, 1, 7.00, 7.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `descripcion` varchar(191) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `categoria_id` int(11) DEFAULT NULL,
  `imagen` varchar(191) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `destacado` tinyint(1) NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `descripcion`, `precio`, `categoria_id`, `imagen`, `stock`, `destacado`, `activo`, `created_at`, `updated_at`) VALUES
(1, 'Producto legado 1', 'Producto reconstruido para mantener integridad referencial con pedido_detalles.', 12.00, 1, NULL, 100, 0, 1, '2026-03-18 18:50:01.000', '2026-03-18 18:50:01.000'),
(2, 'Producto legado 2', 'Producto reconstruido para mantener integridad referencial con pedido_detalles.', 12.00, 1, NULL, 100, 0, 1, '2026-03-18 18:50:02.000', '2026-03-18 18:50:02.000'),
(3, 'Producto legado 3', 'Producto reconstruido para mantener integridad referencial con pedido_detalles.', 123.00, 4, NULL, 100, 0, 1, '2026-03-18 18:50:03.000', '2026-03-18 18:50:03.000'),
(4, 'Producto legado 4', 'Producto reconstruido para mantener integridad referencial con pedido_detalles.', 12.01, 4, NULL, 100, 0, 1, '2026-03-18 18:50:04.000', '2026-03-18 18:50:04.000'),
(9, 'Producto legado 9', 'Producto reconstruido para mantener integridad referencial con pedido_detalles.', 12.00, 1, NULL, 100, 0, 1, '2026-03-18 18:50:09.000', '2026-03-18 18:50:09.000'),
(10, 'Producto legado 10', 'Producto reconstruido para mantener integridad referencial con pedido_detalles.', 3.00, 1, NULL, 100, 0, 1, '2026-03-18 18:50:10.000', '2026-03-18 18:50:10.000'),
(11, 'Producto legado 11', 'Producto reconstruido para mantener integridad referencial con pedido_detalles.', 13.00, 1, NULL, 100, 0, 1, '2026-03-18 18:50:11.000', '2026-03-18 18:50:11.000'),
(13, 'ALFAJOR', NULL, 6.00, 4, 'productos/producto-1773861080819-513132247.jpg', 10, 0, 1, '2026-03-18 19:11:20.830', '2026-03-18 19:11:20.830'),
(14, 'CHANCAY', NULL, 4.00, 1, 'productos/producto-1773861110947-897813601.png', 10, 1, 1, '2026-03-18 19:11:50.953', '2026-03-18 19:11:50.953'),
(15, 'PAN INTEGRAL', NULL, 7.00, 1, 'productos/producto-1773861170202-860380929.png', 5, 0, 1, '2026-03-18 19:12:19.875', '2026-03-18 19:12:50.207'),
(16, 'KARAMANDUKAS', NULL, 7.00, 4, 'productos/producto-1773861224236-371990791.png', 24, 0, 1, '2026-03-18 19:13:44.240', '2026-03-18 19:13:44.240'),
(17, 'PAN DE MOLDE', NULL, 12.00, 1, 'productos/producto-1773861273034-460152199.png', 12, 0, 1, '2026-03-18 19:14:18.714', '2026-03-18 19:14:33.041'),
(18, 'PAN INTEGRAL BLANCO', NULL, 9.00, 1, 'productos/producto-1773861324089-292209327.png', 9, 0, 1, '2026-03-18 19:15:24.093', '2026-03-18 19:15:24.093'),
(19, 'TOSTADAS', NULL, 7.50, 3, 'productos/producto-1773861365971-396169824.png', 11, 0, 1, '2026-03-18 19:16:05.975', '2026-03-18 19:16:05.975'),
(20, 'torta de chocolate', NULL, 55.00, 5, 'productos/producto-1773862190855-798742758.jpg', 5, 0, 1, '2026-03-18 19:29:50.862', '2026-03-18 19:29:50.862');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `productos_app`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `productos_app` (
`id_producto` int(11)
,`id_categoria` int(11)
,`nombre` varchar(191)
,`descripcion` varchar(191)
,`precio` decimal(10,2)
,`imagen` varchar(191)
,`stock` int(11)
,`estado` tinyint(1)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `seguimiento_app`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `seguimiento_app` (
`id` int(11)
,`pedido_id` int(11)
,`estado` enum('Preparando','En camino','Entregado')
,`fecha` datetime(3)
);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimiento`
--

CREATE TABLE `seguimiento` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `estado` enum('Preparando','En camino','Entregado') DEFAULT NULL,
  `fecha` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token` varchar(255) NOT NULL,
  `dispositivo` varchar(100) DEFAULT NULL,
  `fecha` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `soporte`
--

CREATE TABLE `soporte` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `asunto` varchar(200) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `respuesta` text DEFAULT NULL,
  `estado` enum('pendiente','respondido') DEFAULT 'pendiente',
  `fecha` datetime(3) NOT NULL DEFAULT current_timestamp(3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(191) NOT NULL,
  `apellido` varchar(191) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password` varchar(191) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `distrito_id` int(11) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `distrito` varchar(120) DEFAULT NULL,
  `numero_casa` varchar(20) DEFAULT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime(3) NOT NULL DEFAULT current_timestamp(3),
  `updated_at` datetime(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `email`, `password`, `telefono`, `distrito_id`, `direccion`, `distrito`, `numero_casa`, `activo`, `created_at`, `updated_at`) VALUES
(11, 'Nida', 'Telloizarra', 'nidatelloizarra3@gmail.com', '$2b$10$/ULCAPdMxeGnE1yHRqXuVOmXlNNZ5SKU0XGF2qcXzeyCJNdW3Iw8m', '974268690', 1, 'Jr. Lima', 'Huancayo', '350', 1, '2026-03-18 19:17:10.435', '2026-03-18 19:17:10.435');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `usuarios_app`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `usuarios_app` (
`id_usuario` int(11)
,`nombre` varchar(191)
,`correo` varchar(191)
,`password` varchar(191)
,`telefono` varchar(20)
,`rol` varchar(7)
,`estado` tinyint(1)
,`fecha_registro` datetime(3)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `categorias_app`
--
DROP TABLE IF EXISTS `categorias_app`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `categorias_app`  AS SELECT `c`.`id` AS `id_categoria`, `c`.`nombre` AS `nombre`, `c`.`activo` AS `estado` FROM `categorias` AS `c` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `comprobantes_app`
--
DROP TABLE IF EXISTS `comprobantes_app`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `comprobantes_app`  AS SELECT `c`.`id` AS `id`, `c`.`pedido_id` AS `pedido_id`, `c`.`comprobante_serie_id` AS `comprobante_serie_id`, `c`.`tipo` AS `tipo`, `c`.`serie` AS `serie`, `c`.`numero` AS `numero`, `c`.`numero_formateado` AS `numero_formateado`, `c`.`archivo_nombre` AS `archivo_nombre`, `c`.`archivo_ruta` AS `archivo_ruta`, `c`.`mime` AS `mime`, `c`.`size_bytes` AS `size_bytes`, `c`.`created_at` AS `created_at` FROM `comprobantes` AS `c` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `detalle_pedido_app`
--
DROP TABLE IF EXISTS `detalle_pedido_app`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `detalle_pedido_app`  AS SELECT `d`.`id` AS `id_detalle`, `d`.`pedido_id` AS `id_pedido`, `d`.`producto_id` AS `id_producto`, `d`.`cantidad` AS `cantidad`, `d`.`subtotal` AS `subtotal` FROM `pedido_detalles` AS `d` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `direcciones_app`
--
DROP TABLE IF EXISTS `direcciones_app`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `direcciones_app`  AS SELECT `d`.`id` AS `id`, `d`.`usuario_id` AS `usuario_id`, `d`.`distrito_id` AS `distrito_id`, `d`.`direccion` AS `direccion`, `d`.`distrito` AS `distrito`, `d`.`numero_casa` AS `numero_casa`, `d`.`referencia` AS `referencia`, `d`.`latitud` AS `latitud`, `d`.`longitud` AS `longitud` FROM `direcciones` AS `d` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `pedidos_app`
--
DROP TABLE IF EXISTS `pedidos_app`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `pedidos_app`  AS SELECT `pe`.`id` AS `id_pedido`, `pe`.`usuario_id` AS `id_usuario`, `pe`.`direccion_id` AS `id_direccion`, `pe`.`total` AS `total`, `pe`.`estado` AS `estado`, `pe`.`created_at` AS `fecha` FROM `pedidos` AS `pe` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `pagos_app`
--
DROP TABLE IF EXISTS `pagos_app`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `pagos_app`  AS SELECT `p`.`id` AS `id`, `p`.`pedido_id` AS `pedido_id`, `p`.`metodo` AS `metodo`, `p`.`monto` AS `monto`, `p`.`estado` AS `estado`, `p`.`fecha` AS `fecha` FROM `pagos` AS `p` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `productos_app`
--
DROP TABLE IF EXISTS `productos_app`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `productos_app`  AS SELECT `p`.`id` AS `id_producto`, `p`.`categoria_id` AS `id_categoria`, `p`.`nombre` AS `nombre`, `p`.`descripcion` AS `descripcion`, `p`.`precio` AS `precio`, `p`.`imagen` AS `imagen`, `p`.`stock` AS `stock`, `p`.`activo` AS `estado` FROM `productos` AS `p` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `seguimiento_app`
--
DROP TABLE IF EXISTS `seguimiento_app`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `seguimiento_app`  AS SELECT `s`.`id` AS `id`, `s`.`pedido_id` AS `pedido_id`, `s`.`estado` AS `estado`, `s`.`fecha` AS `fecha` FROM `seguimiento` AS `s` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `usuarios_app`
--
DROP TABLE IF EXISTS `usuarios_app`;

CREATE ALGORITHM=UNDEFINED SQL SECURITY DEFINER VIEW `usuarios_app`  AS SELECT `u`.`id` AS `id_usuario`, `u`.`nombre` AS `nombre`, `u`.`email` AS `correo`, `u`.`password` AS `password`, `u`.`telefono` AS `telefono`, 'cliente' AS `rol`, `u`.`activo` AS `estado`, `u`.`created_at` AS `fecha_registro` FROM `usuarios` AS `u` ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `administradores_email_key` (`email`);

--
-- Indices de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_calif_producto` (`producto_id`),
  ADD KEY `idx_calif_usuario` (`usuario_id`);

--
-- Indices de la tabla `catalogo_distritos_huancayo`
--
ALTER TABLE `catalogo_distritos_huancayo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_catalogo_distritos_huancayo_nombre` (`nombre`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `categorias_nombre_key` (`nombre`);

--
-- Indices de la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `comprobantes_tipo_serie_numero_key` (`tipo`,`serie`,`numero`),
  ADD KEY `idx_pedido_id` (`pedido_id`),
  ADD KEY `idx_comprobantes_serie_id` (`comprobante_serie_id`);

--
-- Indices de la tabla `comprobante_series`
--
ALTER TABLE `comprobante_series`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `comprobante_series_tipo_serie_key` (`tipo`,`serie`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_dir_usuario` (`usuario_id`),
  ADD KEY `idx_direcciones_distrito` (`distrito_id`);

--
-- Indices de la tabla `login_logs`
--
ALTER TABLE `login_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `login_logs_usuario_id_fkey` (`usuario_id`),
  ADD KEY `login_logs_admin_id_fkey` (`admin_id`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_notif_usuario` (`usuario_id`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pago_pedido` (`pedido_id`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedidos_usuario_id_fkey` (`usuario_id`),
  ADD KEY `idx_pedido_direccion` (`direccion_id`),
  ADD KEY `idx_pedidos_distrito_entrega` (`distrito_entrega_id`);

--
-- Indices de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pedido_detalles_pedido_id_fkey` (`pedido_id`),
  ADD KEY `pedido_detalles_producto_id_fkey` (`producto_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `productos_categoria_id_fkey` (`categoria_id`);

--
-- Indices de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_seguimiento_pedido` (`pedido_id`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_sesion_usuario` (`usuario_id`);

--
-- Indices de la tabla `soporte`
--
ALTER TABLE `soporte`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_soporte_usuario` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuarios_email_key` (`email`),
  ADD KEY `idx_usuarios_distrito` (`distrito_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `catalogo_distritos_huancayo`
--
ALTER TABLE `catalogo_distritos_huancayo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=392;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `comprobante_series`
--
ALTER TABLE `comprobante_series`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `login_logs`
--
ALTER TABLE `login_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `soporte`
--
ALTER TABLE `soporte`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `calificaciones`
--
ALTER TABLE `calificaciones`
  ADD CONSTRAINT `fk_calificaciones_producto` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_calificaciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `comprobantes`
--
ALTER TABLE `comprobantes`
  ADD CONSTRAINT `comprobantes_pedido_id_fkey` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_comprobantes_serie` FOREIGN KEY (`comprobante_serie_id`) REFERENCES `comprobante_series` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `fk_direcciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_direcciones_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `catalogo_distritos_huancayo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `login_logs`
--
ALTER TABLE `login_logs`
  ADD CONSTRAINT `login_logs_admin_id_fkey` FOREIGN KEY (`admin_id`) REFERENCES `administradores` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `login_logs_usuario_id_fkey` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificaciones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_usuario_id_fkey` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedidos_direccion` FOREIGN KEY (`direccion_id`) REFERENCES `direcciones` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedidos_distrito_entrega` FOREIGN KEY (`distrito_entrega_id`) REFERENCES `catalogo_distritos_huancayo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido_detalles`
--
ALTER TABLE `pedido_detalles`
  ADD CONSTRAINT `pedido_detalles_pedido_id_fkey` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `pedido_detalles_producto_id_fkey` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_categoria_id_fkey` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `seguimiento`
--
ALTER TABLE `seguimiento`
  ADD CONSTRAINT `fk_seguimiento_pedido` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `fk_sesiones_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `soporte`
--
ALTER TABLE `soporte`
  ADD CONSTRAINT `fk_soporte_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_distrito` FOREIGN KEY (`distrito_id`) REFERENCES `catalogo_distritos_huancayo` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;
SET FOREIGN_KEY_CHECKS = 1;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;