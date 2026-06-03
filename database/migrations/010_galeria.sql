CREATE TABLE IF NOT EXISTS galeria (
    id         INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    studio_id  INT UNSIGNED NOT NULL DEFAULT 1,
    cliente_id INT UNSIGNED NULL,
    turno_id   INT UNSIGNED NULL,
    titulo     VARCHAR(150) NULL,
    estilo     ENUM('tradicional','neo-tradicional','realismo','blackwork',
                   'watercolor','linework','geometric','japonés','chicano','otro') NULL,
    archivo    VARCHAR(255) NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_galeria_studio  (studio_id),
    KEY idx_galeria_cliente (cliente_id),
    KEY idx_galeria_turno   (turno_id),
    CONSTRAINT fk_galeria_studio  FOREIGN KEY (studio_id)  REFERENCES studios(id)  ON DELETE CASCADE,
    CONSTRAINT fk_galeria_cliente FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    CONSTRAINT fk_galeria_turno   FOREIGN KEY (turno_id)   REFERENCES turnos(id)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
