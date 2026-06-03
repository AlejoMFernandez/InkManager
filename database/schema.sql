-- ============================================================
--  InkManager — Tattoo Studio DB Schema
--  MySQL 8 / utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS `tattoo_studio`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `tattoo_studio`;

-- ── usuarios ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `usuarios` (
    `id`            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `nombre`        VARCHAR(100)      NOT NULL,
    `email`         VARCHAR(191)      NOT NULL UNIQUE,
    `password_hash` VARCHAR(255)      NOT NULL,
    `created_at`    DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── estilos ──────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `estilos` (
    `id`     TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(60)      NOT NULL UNIQUE,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── clientes ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `clientes` (
    `id`             INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    `nombre`         VARCHAR(120)  NOT NULL,
    `instagram`      VARCHAR(80)   DEFAULT NULL,
    `telefono`       VARCHAR(30)   DEFAULT NULL,
    `primera_visita` DATE          DEFAULT NULL,
    `notas`          TEXT          DEFAULT NULL,
    `genero`         ENUM('masculino','femenino','otro') NOT NULL DEFAULT 'masculino',
    `foto_perfil`    VARCHAR(255)  DEFAULT NULL,
    `created_at`     DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_nombre`    (`nombre`),
    KEY `idx_instagram` (`instagram`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── MIGRACIÓN para DBs existentes (correr una sola vez) ──────
-- ALTER TABLE `clientes`
--   ADD COLUMN `genero` ENUM('masculino','femenino','otro') NOT NULL DEFAULT 'masculino'
--   AFTER `notas`;
-- ALTER TABLE `clientes`
--   ADD COLUMN `foto_perfil` VARCHAR(255) DEFAULT NULL
--   AFTER `genero`;

-- ── tatuajes ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `tatuajes` (
    `id`               INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `cliente_id`       INT UNSIGNED      NOT NULL,
    `pos_x`            FLOAT             DEFAULT NULL COMMENT 'Three.js world coord X',
    `pos_y`            FLOAT             DEFAULT NULL COMMENT 'Three.js world coord Y',
    `pos_z`            FLOAT             DEFAULT NULL COMMENT 'Three.js world coord Z',
    `normal_x`         FLOAT             DEFAULT NULL COMMENT 'Surface normal X (for marker orientation)',
    `normal_y`         FLOAT             DEFAULT NULL,
    `normal_z`         FLOAT             DEFAULT NULL,
    `foto_path`        VARCHAR(255)      DEFAULT NULL,
    `estilo_id`        TINYINT UNSIGNED  DEFAULT NULL,
    `fecha`            DATE              DEFAULT NULL,
    `precio`           DECIMAL(10,2)     DEFAULT NULL,
    `sesiones_totales` TINYINT UNSIGNED  NOT NULL DEFAULT 1,
    `sesiones_hechas`  TINYINT UNSIGNED  NOT NULL DEFAULT 0,
    `notas`            TEXT              DEFAULT NULL,
    `created_at`       DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_cliente`  (`cliente_id`),
    KEY `idx_estilo`   (`estilo_id`),
    CONSTRAINT `fk_tatuaje_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes`(`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_tatuaje_estilo`  FOREIGN KEY (`estilo_id`)  REFERENCES `estilos`(`id`)  ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── turnos ───────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `turnos` (
    `id`           INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    `cliente_id`   INT UNSIGNED      NOT NULL,
    `tatuaje_id`   INT UNSIGNED      DEFAULT NULL,
    `fecha_inicio` DATETIME          NOT NULL,
    `duracion_min` SMALLINT UNSIGNED NOT NULL DEFAULT 60,
    `estado`       ENUM('agendado','confirmado','hecho','cancelado')
                                     NOT NULL DEFAULT 'agendado',
    `sena`         DECIMAL(10,2)     DEFAULT NULL,
    `notas`        TEXT              DEFAULT NULL,
    `reminder_sent` TINYINT(1)       NOT NULL DEFAULT 0 COMMENT 'WhatsApp 2h-before reminder sent',
    `created_at`   DATETIME          NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_turno_cliente`   (`cliente_id`),
    KEY `idx_turno_fecha`     (`fecha_inicio`),
    KEY `idx_turno_estado`    (`estado`),
    CONSTRAINT `fk_turno_cliente`  FOREIGN KEY (`cliente_id`)  REFERENCES `clientes`(`id`)  ON DELETE CASCADE,
    CONSTRAINT `fk_turno_tatuaje` FOREIGN KEY (`tatuaje_id`)  REFERENCES `tatuajes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── etiquetas ────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `etiquetas` (
    `id`         SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`     VARCHAR(40)       NOT NULL UNIQUE,
    `color`      VARCHAR(7)        NOT NULL DEFAULT '#ef4444',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── cliente_etiquetas (pivot) ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `cliente_etiquetas` (
    `cliente_id`   INT UNSIGNED      NOT NULL,
    `etiqueta_id`  SMALLINT UNSIGNED NOT NULL,
    PRIMARY KEY (`cliente_id`, `etiqueta_id`),
    CONSTRAINT `fk_ce_cliente`   FOREIGN KEY (`cliente_id`)  REFERENCES `clientes`(`id`)   ON DELETE CASCADE,
    CONSTRAINT `fk_ce_etiqueta`  FOREIGN KEY (`etiqueta_id`) REFERENCES `etiquetas`(`id`)  ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── MIGRACIÓN para DBs existentes (correr una sola vez) ──────
-- CREATE TABLE IF NOT EXISTS `etiquetas` ...  (ver arriba)
-- CREATE TABLE IF NOT EXISTS `cliente_etiquetas` ...  (ver arriba)
