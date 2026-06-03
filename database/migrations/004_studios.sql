-- ============================================================
--  Migration 004 — Multi-tenant Studios
--  InkManager · MySQL 8 · Run ONCE in phpMyAdmin
--  against the `tattoo_studio` database
-- ============================================================

-- ── Step 1: Studios table ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `studios` (
    `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `nombre`     VARCHAR(100) NOT NULL,
    `slug`       VARCHAR(50)  NOT NULL UNIQUE,
    `plan`       ENUM('free','pro') NOT NULL DEFAULT 'free',
    `owner_id`   INT UNSIGNED NULL,
    `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Step 2: Default studio for all existing data ──────────
INSERT IGNORE INTO `studios` (`id`, `nombre`, `slug`, `plan`)
VALUES (1, 'Mi Estudio', 'mi-estudio', 'free');

-- ── Step 3: Add studio_id + rol to usuarios ───────────────
ALTER TABLE `usuarios`
    ADD COLUMN `studio_id` INT UNSIGNED NULL             AFTER `id`,
    ADD COLUMN `rol`       ENUM('owner','admin','staff')
                           NOT NULL DEFAULT 'owner'      AFTER `studio_id`;

UPDATE `usuarios` SET `studio_id` = 1 WHERE `studio_id` IS NULL;

-- ── Step 4: Add studio_id to clientes ────────────────────
ALTER TABLE `clientes`
    ADD COLUMN `studio_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD INDEX  `idx_clientes_studio` (`studio_id`),
    ADD CONSTRAINT `fk_clientes_studio`
        FOREIGN KEY (`studio_id`) REFERENCES `studios`(`id`) ON DELETE CASCADE;

-- ── Step 5: Add studio_id to turnos ──────────────────────
ALTER TABLE `turnos`
    ADD COLUMN `studio_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD INDEX  `idx_turnos_studio` (`studio_id`),
    ADD CONSTRAINT `fk_turnos_studio`
        FOREIGN KEY (`studio_id`) REFERENCES `studios`(`id`) ON DELETE CASCADE;

-- ── Step 6: Add studio_id to tatuajes ────────────────────
ALTER TABLE `tatuajes`
    ADD COLUMN `studio_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`,
    ADD INDEX  `idx_tatuajes_studio` (`studio_id`),
    ADD CONSTRAINT `fk_tatuajes_studio`
        FOREIGN KEY (`studio_id`) REFERENCES `studios`(`id`) ON DELETE CASCADE;

-- ── Step 7: studio_id to etiquetas + fix unique ───────────
ALTER TABLE `etiquetas`
    ADD COLUMN `studio_id` INT UNSIGNED NOT NULL DEFAULT 1 AFTER `id`;

-- Drop the global unique on nombre, replace with per-studio unique
ALTER TABLE `etiquetas` DROP INDEX `nombre`;
ALTER TABLE `etiquetas`
    ADD UNIQUE KEY `uq_etiqueta_studio_nombre` (`studio_id`, `nombre`),
    ADD INDEX      `idx_etiquetas_studio`      (`studio_id`),
    ADD CONSTRAINT `fk_etiquetas_studio`
        FOREIGN KEY (`studio_id`) REFERENCES `studios`(`id`) ON DELETE CASCADE;

-- ── Step 8: FK from studios to owner ─────────────────────
ALTER TABLE `studios`
    ADD CONSTRAINT `fk_studios_owner`
        FOREIGN KEY (`owner_id`) REFERENCES `usuarios`(`id`) ON DELETE SET NULL;

-- ── Step 9: Set studio owner_id to first user of that studio
UPDATE `studios` s
SET s.`owner_id` = (
    SELECT u.`id` FROM `usuarios` u
    WHERE u.`studio_id` = s.`id`
    ORDER BY u.`id` ASC
    LIMIT 1
)
WHERE s.`owner_id` IS NULL;
