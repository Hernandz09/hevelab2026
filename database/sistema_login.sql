-- Base de datos mínima para HeveLab-MVC (auth + OTP + face descriptor + admin usuarios)

CREATE DATABASE IF NOT EXISTS `sistema_login`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `sistema_login`;

CREATE TABLE IF NOT EXISTS `usuarios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `usuario` VARCHAR(120) NOT NULL,
  `email` VARCHAR(190) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `verificado` TINYINT(1) NOT NULL DEFAULT 0,
  `registro_etapa` VARCHAR(80) NOT NULL DEFAULT 'Registro inicial',
  `otp_code` INT UNSIGNED NULL,
  `otp_expiracion` DATETIME NULL,
  `face_descriptor` LONGTEXT NULL,
  `ultimo_login` DATETIME NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_usuarios_email` (`email`),
  UNIQUE KEY `uq_usuarios_usuario` (`usuario`),
  KEY `idx_usuarios_verificado` (`verificado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `usuarios` (`usuario`, `email`, `password`, `verificado`, `registro_etapa`)
VALUES ('admin', 'admin@hevelab.local', '$2y$10$it7H9e0QppEBfyP7SBrGYeeAIV0YNZOWdZ3xoeTHiqeogY29HRrS.', 1, 'Completado')
ON DUPLICATE KEY UPDATE `usuario` = VALUES(`usuario`);
