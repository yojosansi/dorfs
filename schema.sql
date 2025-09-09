-- Esquema de Base de Datos para el Gestor de Leyes Consolidadas
-- Versión 1.0

-- Tabla para almacenar la información principal de las leyes.
CREATE TABLE `leyes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(255) NOT NULL,
  `numero_ley` VARCHAR(50) NULL,
  `fecha_publicacion_inicial` DATE NOT NULL,
  `organismo` VARCHAR(150) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para gestionar las diferentes versiones de una ley.
-- Cada vez que una ley es modificada, se crea una nueva versión.
CREATE TABLE `versiones` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `ley_id` INT NOT NULL,
  `titulo_version` VARCHAR(255) NOT NULL,
  `fecha_version` DATE NOT NULL,
  `descripcion_cambios` TEXT NULL,
  `enlace_publicacion` VARCHAR(255) NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`ley_id`) REFERENCES `leyes`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para los artículos de cada versión de una ley.
-- Un artículo está ligado a una versión específica, permitiendo
-- tener el texto completo de la ley en cualquier punto del tiempo.
CREATE TABLE `articulos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version_id` INT NOT NULL,
  `numero_articulo` VARCHAR(20) NOT NULL,
  `titulo_articulo` VARCHAR(255) NULL,
  `orden` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`version_id`) REFERENCES `versiones`(`id`) ON DELETE CASCADE,
  UNIQUE KEY `idx_version_articulo` (`version_id`, `numero_articulo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla para las secciones (apartados, puntos, etc.) dentro de un artículo.
-- Esto permite una granularidad fina para mostrar modificaciones.
CREATE TABLE `secciones_articulo` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `articulo_id` INT NOT NULL,
  `tipo_seccion` VARCHAR(50) NOT NULL COMMENT 'Ej: Apartado, Punto, Letra',
  `identificador_seccion` VARCHAR(20) NOT NULL COMMENT 'Ej: 1, a), Primero',
  `contenido` TEXT NOT NULL,
  `orden` INT DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`articulo_id`) REFERENCES `articulos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- A futuro, se podría añadir una tabla de modificaciones explícitas
-- para facilitar la visualización de cambios entre versiones.
-- CREATE TABLE `modificaciones` ( ... );
