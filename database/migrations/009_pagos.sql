-- 009_pagos.sql
-- Módulo de caja: registro de ingresos y egresos del estudio

CREATE TABLE pagos (
    id          INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    studio_id   INT UNSIGNED NOT NULL DEFAULT 1,
    cliente_id  INT UNSIGNED NULL,
    concepto    VARCHAR(200) NOT NULL,
    monto       DECIMAL(10,2) NOT NULL,
    metodo      ENUM('efectivo','transferencia','tarjeta','sena','otro') NOT NULL DEFAULT 'efectivo',
    tipo        ENUM('ingreso','egreso') NOT NULL DEFAULT 'ingreso',
    fecha       DATE NOT NULL,
    notas       TEXT NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

    KEY idx_pagos_studio_fecha (studio_id, fecha),
    KEY idx_pagos_cliente      (cliente_id),

    CONSTRAINT fk_pagos_studio  FOREIGN KEY (studio_id)  REFERENCES studios(id)  ON DELETE CASCADE,
    CONSTRAINT fk_pagos_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
