-- 008_clientes_nacimiento.sql
-- Agrega fecha_nacimiento a clientes para notificaciones de cumpleaños

ALTER TABLE clientes
  ADD COLUMN fecha_nacimiento DATE NULL AFTER primera_visita;

ALTER TABLE clientes
  ADD KEY idx_clientes_nacimiento (fecha_nacimiento);
