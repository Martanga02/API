-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 21-11-2025 a las 03:37:14
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
-- Base de datos: `peliculas_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `genero`
--

CREATE TABLE `genero` (
  `id` int(11) NOT NULL,
  `nombre` text NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `genero`
--

INSERT INTO `genero` (`id`, `nombre`, `descripcion`) VALUES
(2, 'Aventura', 'Historias que implican viajes o exploraciones, a menudo en lugares exóticos o desconocidos. Se centra en la emoción de lo desconocido'),
(3, 'Comedia', 'Su objetivo principal es hacer reír. Utiliza el humor a través de situaciones absurdas, diálogos graciosos o personajes extravagantes.'),
(4, 'Drama', 'Se enfoca en el desarrollo emocional de los personajes, tratando temas profundos o realistas. Suele tener un tono serio.'),
(5, 'Terror', 'Diseñado para provocar miedo, tensión o inquietud. Puede incluir elementos sobrenaturales, monstruos, asesinos, o situaciones psicológicas.'),
(6, 'Ciencia ficción', 'Explora mundos futuros, tecnología avanzada, viajes espaciales, inteligencias artificiales o vida extraterrestre.'),
(7, 'Fantasía', 'Incluye elementos mágicos o sobrenaturales que no existen en la realidad. Puede desarrollarse en mundos imaginarios.'),
(8, 'Romance', 'Centrada en las relaciones amorosas, conflictos emocionales y el viaje sentimental de los personajes.'),
(9, 'Documental', 'Películas que muestran hechos reales con fines informativos, educativos o de reflexión.'),
(10, 'Suspenso / Thriller', 'Mantiene al espectador en tensión, con giros inesperados, misterio o peligro constante.'),
(11, 'Anime', 'El anime es un estilo de animación de origen japonés que se caracteriza por su arte visual único, sus personajes expresivos y narrativas diversas que abarcan desde la fantasía hasta la ciencia ficción y el romance');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pelicula`
--

CREATE TABLE `pelicula` (
  `id_pelicula` int(11) NOT NULL,
  `nombre` text NOT NULL,
  `estudio` text NOT NULL,
  `id_genero` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pelicula`
--

INSERT INTO `pelicula` (`id_pelicula`, `nombre`, `estudio`, `id_genero`) VALUES
(1, 'Dune: Parte Dos', 'Legendary Pictures', 6),
(2, 'Oppenheimer', 'Universal Pictures', 4),
(3, 'Barbie', 'Warner Bros', 3),
(5, 'Nope', 'Universal Pictures', 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--
-- Nota: la tabla original se llamaba `user` (columnas `email`/`password`),
-- pero app/models/user.model.php consulta una tabla `usuarios` con columnas
-- `usuario`/`contrasena` (y AuthApiController usa $userFromDB->rol para el
-- JWT). Se renombra acá para que el login funcione, y se agrega `rol` para
-- que el endpoint POST /categoria (que exige ADMIN) sea alcanzable.
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `rol` varchar(20) NOT NULL DEFAULT 'USER'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--
-- Contraseñas de prueba (bcrypt, verificables con password_verify de PHP):
--   admin / admin123  (rol ADMIN, puede crear categorías)
--   demo  / demo123   (rol USER)

INSERT INTO `usuarios` (`id`, `usuario`, `contrasena`, `rol`) VALUES
(1, 'admin', '$2b$10$eBnBBKdRUQScNez/XlXq3uQexjoUAqP2XUlMankHlN8eZxD7b52oa', 'ADMIN'),
(2, 'demo', '$2b$10$a3UbnmBs/fsssQZUcwQdZ.rHCnyBuDt6/fi.jG9DmnWDNrkCDuNSC', 'USER');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `genero`
--
ALTER TABLE `genero`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pelicula`
--
ALTER TABLE `pelicula`
  ADD PRIMARY KEY (`id_pelicula`),
  ADD KEY `id_genero` (`id_genero`);

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
-- AUTO_INCREMENT de la tabla `genero`
--
ALTER TABLE `genero`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `pelicula`
--
ALTER TABLE `pelicula`
  MODIFY `id_pelicula` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pelicula`
--
ALTER TABLE `pelicula`
  ADD CONSTRAINT `pelicula_ibfk_1` FOREIGN KEY (`id_genero`) REFERENCES `genero` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
