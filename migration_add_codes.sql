-- Migración para añadir la funcionalidad de Códigos
-- Versión 1.1

-- Tabla para almacenar los códigos que agrupan leyes.
CREATE TABLE `codigos` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `titulo` VARCHAR(255) NOT NULL UNIQUE,
  `descripcion` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla pivote para la relación muchos a muchos entre leyes y códigos.
CREATE TABLE `ley_codigo` (
  `ley_id` INT NOT NULL,
  `codigo_id` INT NOT NULL,
  PRIMARY KEY (`ley_id`, `codigo_id`),
  FOREIGN KEY (`ley_id`) REFERENCES `leyes`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`codigo_id`) REFERENCES `codigos`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
