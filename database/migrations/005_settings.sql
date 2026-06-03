-- ============================================================
-- 005_settings.sql
-- Studio contact info columns — run once in phpMyAdmin
-- ============================================================

ALTER TABLE `studios`
    ADD COLUMN `telefono`  VARCHAR(20)  NULL AFTER `nombre`,
    ADD COLUMN `instagram` VARCHAR(100) NULL AFTER `telefono`,
    ADD COLUMN `website`   VARCHAR(255) NULL AFTER `instagram`,
    ADD COLUMN `direccion` VARCHAR(255) NULL AFTER `website`;
