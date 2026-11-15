-- Script de inicialización para PostgreSQL
--
-- Migrado desde el volcado original de MySQL/MariaDB (phpMyAdmin).
-- Es idempotente: se puede ejecutar más de una vez sin duplicar datos
-- (CREATE TABLE IF NOT EXISTS + INSERT ... ON CONFLICT DO NOTHING).
--
-- Se usa en tres lugares:
--   * docker-compose (se monta en /docker-entrypoint-initdb.d)
--   * los tests de integración (tests/Integration/bootstrap.php)
--   * la base de Render (se carga a mano con psql, ver README)

-- --------------------------------------------------------
-- Tabla genero
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS genero (
  id SERIAL PRIMARY KEY,
  nombre TEXT NOT NULL,
  descripcion TEXT
);

INSERT INTO genero (id, nombre, descripcion) VALUES
(2, 'Aventura', 'Historias que implican viajes o exploraciones, a menudo en lugares exóticos o desconocidos. Se centra en la emoción de lo desconocido'),
(3, 'Comedia', 'Su objetivo principal es hacer reír. Utiliza el humor a través de situaciones absurdas, diálogos graciosos o personajes extravagantes.'),
(4, 'Drama', 'Se enfoca en el desarrollo emocional de los personajes, tratando temas profundos o realistas. Suele tener un tono serio.'),
(5, 'Terror', 'Diseñado para provocar miedo, tensión o inquietud. Puede incluir elementos sobrenaturales, monstruos, asesinos, o situaciones psicológicas.'),
(6, 'Ciencia ficción', 'Explora mundos futuros, tecnología avanzada, viajes espaciales, inteligencias artificiales o vida extraterrestre.'),
(7, 'Fantasía', 'Incluye elementos mágicos o sobrenaturales que no existen en la realidad. Puede desarrollarse en mundos imaginarios.'),
(8, 'Romance', 'Centrada en las relaciones amorosas, conflictos emocionales y el viaje sentimental de los personajes.'),
(9, 'Documental', 'Películas que muestran hechos reales con fines informativos, educativos o de reflexión.'),
(10, 'Suspenso / Thriller', 'Mantiene al espectador en tensión, con giros inesperados, misterio o peligro constante.'),
(11, 'Anime', 'El anime es un estilo de animación de origen japonés que se caracteriza por su arte visual único, sus personajes expresivos y narrativas diversas que abarcan desde la fantasía hasta la ciencia ficción y el romance')
ON CONFLICT (id) DO NOTHING;

-- --------------------------------------------------------
-- Tabla pelicula
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS pelicula (
  id_pelicula SERIAL PRIMARY KEY,
  nombre TEXT NOT NULL,
  estudio TEXT NOT NULL,
  id_genero INTEGER NOT NULL REFERENCES genero (id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE INDEX IF NOT EXISTS pelicula_id_genero_idx ON pelicula (id_genero);

INSERT INTO pelicula (id_pelicula, nombre, estudio, id_genero) VALUES
(1, 'Dune: Parte Dos', 'Legendary Pictures', 6),
(2, 'Oppenheimer', 'Universal Pictures', 4),
(3, 'Barbie', 'Warner Bros', 3),
(5, 'Nope', 'Universal Pictures', 5)
ON CONFLICT (id_pelicula) DO NOTHING;

-- --------------------------------------------------------
-- Tabla usuarios
--
-- app/models/user.model.php consulta la tabla usuarios con las columnas
-- usuario/contrasena, y AuthApiController usa el campo rol para el JWT.
--
-- Contraseñas de prueba (bcrypt, verificables con password_verify de PHP):
--   admin / admin123  (rol ADMIN, puede crear categorías)
--   demo  / demo123   (rol USER)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS usuarios (
  id SERIAL PRIMARY KEY,
  usuario VARCHAR(100) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  rol VARCHAR(20) NOT NULL DEFAULT 'USER'
);

INSERT INTO usuarios (id, usuario, contrasena, rol) VALUES
(1, 'admin', '$2b$10$eBnBBKdRUQScNez/XlXq3uQexjoUAqP2XUlMankHlN8eZxD7b52oa', 'ADMIN'),
(2, 'demo', '$2b$10$a3UbnmBs/fsssQZUcwQdZ.rHCnyBuDt6/fi.jG9DmnWDNrkCDuNSC', 'USER')
ON CONFLICT (id) DO NOTHING;

-- --------------------------------------------------------
-- Secuencias
--
-- Los INSERT de arriba usan ids explícitos, y en PostgreSQL eso NO avanza el
-- contador del SERIAL: sin esto, el primer INSERT sin id chocaría con un id ya
-- existente. Se deja el contador en el valor que tenía en MySQL
-- (AUTO_INCREMENT 23, 9 y 3), o más arriba si ya hay filas con ids mayores.
-- --------------------------------------------------------

SELECT setval(pg_get_serial_sequence('genero', 'id'), GREATEST(22, (SELECT MAX(id) FROM genero)));

SELECT setval(pg_get_serial_sequence('pelicula', 'id_pelicula'), GREATEST(8, (SELECT MAX(id_pelicula) FROM pelicula)));

SELECT setval(pg_get_serial_sequence('usuarios', 'id'), GREATEST(2, (SELECT MAX(id) FROM usuarios)));

