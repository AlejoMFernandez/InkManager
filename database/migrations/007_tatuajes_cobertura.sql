-- ============================================================
-- 007_tatuajes_cobertura.sql
-- Tamaño, zona corporal y tipo de tinta en tatuajes
-- Run once in phpMyAdmin
-- ============================================================

ALTER TABLE `tatuajes`
    ADD COLUMN `tamano` ENUM('xs','s','m','l','xl')      NOT NULL DEFAULT 'm'     AFTER `notas`,
    ADD COLUMN `zona`   VARCHAR(30)                       NULL                    AFTER `tamano`,
    ADD COLUMN `tinta`  ENUM('negro','gris','color')      NOT NULL DEFAULT 'negro' AFTER `zona`;

-- Índice útil para filtrar por zona (ej: todos los clientes con back piece)
ALTER TABLE `tatuajes`
    ADD KEY `idx_tatuaje_zona` (`zona`);
