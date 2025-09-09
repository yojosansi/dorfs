-- Migración para añadir la tabla de configuración de apariencia
-- Versión 1.2

CREATE TABLE `configuracion` (
  `config_key` VARCHAR(50) NOT NULL PRIMARY KEY,
  `config_value` TEXT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar valores por defecto
INSERT INTO `configuracion` (`config_key`, `config_value`) VALUES
('app_title', 'Leyes Consolidadas'),
('navbar_color', 'bg-dark'),
('navbar_text_color', 'navbar-dark'),
('custom_css', '');
