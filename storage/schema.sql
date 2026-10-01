-- Esquema Maestro Consolidado para Starlink Control (PCtronic)
-- Base de Datos: Starlink_PCtronic
-- Charset: utf8mb4 / Collation: utf8mb4_unicode_ci

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Tabla de Niveles / Roles de Usuario
CREATE TABLE IF NOT EXISTS `level` (
    `id_level` INT NOT NULL AUTO_INCREMENT,
    `user_role` VARCHAR(50) NOT NULL,
    PRIMARY KEY (`id_level`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO `level` (`id_level`, `user_role`) VALUES
(1, 'Administrador'),
(2, 'Moderador'),
(3, 'Expectador');

-- 2. Tabla de Usuarios
CREATE TABLE IF NOT EXISTS `user` (
    `id_user` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `surname` VARCHAR(100) NOT NULL,
    `ci` VARCHAR(20) NOT NULL,
    `birth` DATE NULL,
    `pass` VARCHAR(255) NOT NULL,
    `id_level` INT NOT NULL DEFAULT 3,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_user`),
    UNIQUE KEY `uq_user_ci` (`ci`),
    KEY `idx_user_level` (`id_level`),
    CONSTRAINT `fk_user_level` FOREIGN KEY (`id_level`) REFERENCES `level` (`id_level`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla de Sesiones Concurrentes de Usuario
CREATE TABLE IF NOT EXISTS `user_sessions` (
    `id_session` INT NOT NULL AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `session_id` VARCHAR(128) NOT NULL,
    `justification` VARCHAR(500) NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` VARCHAR(500) NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_seen` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `ended_at` DATETIME NULL,
    `active` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id_session`),
    UNIQUE KEY `uq_user_session` (`session_id`),
    KEY `idx_user_active_session` (`user_id`, `active`),
    CONSTRAINT `fk_user_sessions_user` FOREIGN KEY (`user_id`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla de Preguntas de Seguridad
CREATE TABLE IF NOT EXISTS `secquestion` (
    `id_user` INT NOT NULL,
    `question1` VARCHAR(255) NOT NULL,
    `answer1` VARCHAR(255) NOT NULL,
    `question2` VARCHAR(255) NOT NULL,
    `answer2` VARCHAR(255) NOT NULL,
    `question3` VARCHAR(255) NOT NULL,
    `answer3` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id_user`),
    CONSTRAINT `fk_secquestion_user` FOREIGN KEY (`id_user`) REFERENCES `user` (`id_user`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla de Clientes
CREATE TABLE IF NOT EXISTS `client` (
    `id_client` INT NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(100) NOT NULL,
    `surname` VARCHAR(100) NOT NULL,
    `ci` VARCHAR(20) NULL,
    `phone` VARCHAR(50) NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_client`),
    UNIQUE KEY `uq_client_ci` (`ci`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla de Países / Regiones
CREATE TABLE IF NOT EXISTS `country` (
    `id_country` INT NOT NULL AUTO_INCREMENT,
    `country` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`id_country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabla de Planes de Servicio
CREATE TABLE IF NOT EXISTS `plan` (
    `id_plan` INT NOT NULL AUTO_INCREMENT,
    `plan` VARCHAR(100) NOT NULL,
    `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    PRIMARY KEY (`id_plan`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabla de Cuentas Administrativas Starlink
CREATE TABLE IF NOT EXISTS `accounts` (
    `id_accounts` INT NOT NULL AUTO_INCREMENT,
    `owner` VARCHAR(150) NOT NULL,
    `acc` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `create_date` DATE NOT NULL,
    `countries` INT NULL,
    PRIMARY KEY (`id_accounts`),
    KEY `idx_accounts_country` (`countries`),
    CONSTRAINT `fk_accounts_country` FOREIGN KEY (`countries`) REFERENCES `country` (`id_country`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 9. Tabla de Antenas Starlink
CREATE TABLE IF NOT EXISTS `antenas` (
    `id_starlink` INT NOT NULL AUTO_INCREMENT,
    `client` INT NULL,
    `account_id` INT NULL,
    `serial` VARCHAR(100) NOT NULL,
    `nickname` VARCHAR(100) NULL,
    `kit` VARCHAR(100) NOT NULL,
    `date` DATE NOT NULL,
    `pay` TINYINT NULL,
    `plan` INT NOT NULL,
    `country` INT NOT NULL,
    PRIMARY KEY (`id_starlink`),
    UNIQUE KEY `uq_antenna_serial` (`serial`),
    KEY `idx_antenna_client` (`client`),
    KEY `idx_antenna_account` (`account_id`),
    KEY `idx_antenna_plan` (`plan`),
    KEY `idx_antenna_country` (`country`),
    CONSTRAINT `fk_antenas_client` FOREIGN KEY (`client`) REFERENCES `client` (`id_client`) ON DELETE SET NULL,
    CONSTRAINT `fk_antenas_account` FOREIGN KEY (`account_id`) REFERENCES `accounts` (`id_accounts`) ON DELETE SET NULL,
    CONSTRAINT `fk_antenas_plan` FOREIGN KEY (`plan`) REFERENCES `plan` (`id_plan`),
    CONSTRAINT `fk_antenas_country` FOREIGN KEY (`country`) REFERENCES `country` (`id_country`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 10. Tabla de Pagos
CREATE TABLE IF NOT EXISTS `payments` (
    `id_payment` INT NOT NULL AUTO_INCREMENT,
    `antenna_id` INT NULL,
    `client_id` INT NULL,
    `amount` DECIMAL(10,2) NOT NULL,
    `currency` VARCHAR(10) NOT NULL DEFAULT 'USD',
    `payment_date` DATE NOT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `status` ENUM('Pendiente', 'Aprobado', 'Rechazado') NOT NULL DEFAULT 'Pendiente',
    `receipt_path` VARCHAR(255) NULL,
    `submitted_by` INT NULL,
    `reviewed_by` INT NULL,
    `reviewed_at` DATETIME NULL,
    PRIMARY KEY (`id_payment`),
    KEY `idx_payment_antenna` (`antenna_id`),
    KEY `idx_payment_client` (`client_id`),
    KEY `idx_payment_status` (`status`),
    KEY `idx_payment_submitted` (`submitted_by`),
    KEY `idx_payment_reviewed` (`reviewed_by`),
    CONSTRAINT `fk_payments_antenna` FOREIGN KEY (`antenna_id`) REFERENCES `antenas` (`id_starlink`) ON DELETE SET NULL,
    CONSTRAINT `fk_payments_client` FOREIGN KEY (`client_id`) REFERENCES `client` (`id_client`) ON DELETE SET NULL,
    CONSTRAINT `fk_payments_submitted_by` FOREIGN KEY (`submitted_by`) REFERENCES `user` (`id_user`) ON DELETE SET NULL,
    CONSTRAINT `fk_payments_reviewed_by` FOREIGN KEY (`reviewed_by`) REFERENCES `user` (`id_user`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 11. Tabla de Antenas Asociadas a Pagos Grupales
CREATE TABLE IF NOT EXISTS `payment_antennas` (
    `payment_id` INT NOT NULL,
    `antenna_id` INT NOT NULL,
    PRIMARY KEY (`payment_id`, `antenna_id`),
    CONSTRAINT `fk_payment_antennas_payment` FOREIGN KEY (`payment_id`) REFERENCES `payments` (`id_payment`) ON DELETE CASCADE,
    CONSTRAINT `fk_payment_antennas_antenna` FOREIGN KEY (`antenna_id`) REFERENCES `antenas` (`id_starlink`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
