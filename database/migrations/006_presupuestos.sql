-- ============================================================
-- 006_presupuestos.sql
-- Quotes / estimates module — run once in phpMyAdmin
-- ============================================================

CREATE TABLE IF NOT EXISTS `presupuestos` (
    `id`           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    `studio_id`    INT UNSIGNED     NOT NULL,
    `cliente_id`   INT UNSIGNED     NOT NULL,
    `numero`       VARCHAR(20)      NOT NULL,
    `titulo`       VARCHAR(255)     NOT NULL,
    `descripcion`  TEXT             NULL,
    `monto`        DECIMAL(10,2)    NULL,
    `estado`       ENUM('borrador','enviado','aceptado','rechazado') NOT NULL DEFAULT 'borrador',
    `validez_dias` SMALLINT         NOT NULL DEFAULT 30,
    `fecha`        DATE             NOT NULL,
    `notas`        TEXT             NULL,
    `created_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`   TIMESTAMP        NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uq_presupuesto_numero_studio` (`studio_id`, `numero`),
    KEY `idx_presupuesto_cliente`  (`cliente_id`),
    KEY `idx_presupuesto_studio`   (`studio_id`),
    KEY `idx_presupuesto_estado`   (`estado`),
    CONSTRAINT `fk_presupuesto_studio`  FOREIGN KEY (`studio_id`)  REFERENCES `studios`  (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_presupuesto_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
