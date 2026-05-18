USE `tattoo_studio`;

-- ── estilos ──────────────────────────────────────────────────
INSERT IGNORE INTO `estilos` (`nombre`) VALUES
    ('Blackwork'),
    ('Realismo'),
    ('Fine Line'),
    ('Traditional'),
    ('Neo Traditional'),
    ('Watercolor'),
    ('Geometric'),
    ('Japanese'),
    ('Tribal'),
    ('Lettering');

-- ── usuario admin ─────────────────────────────────────────────
-- Contraseña por defecto: admin123  (cambiala después!)
INSERT IGNORE INTO `usuarios` (`nombre`, `email`, `password_hash`) VALUES (
    'Admin',
    'admin@inkmanager.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'
    -- ^ password_hash('admin123', PASSWORD_BCRYPT) — hash real abajo:
);

-- Si el hash de arriba no funciona, correlo en PHP:
-- echo password_hash('admin123', PASSWORD_BCRYPT);
-- y pegalo acá con UPDATE usuarios SET password_hash='...' WHERE email='admin@inkmanager.com';

-- ── clientes de prueba ───────────────────────────────────────
INSERT IGNORE INTO `clientes` (`nombre`, `instagram`, `telefono`, `primera_visita`, `notas`) VALUES
    ('Valentina Ruiz',    '@vale.ruiz',    '+54 11 1234-5678', '2024-03-15', 'Prefiere turnos de mañana.'),
    ('Marcos Gutiérrez',  '@marcosgt',     '+54 11 2345-6789', '2024-05-20', NULL),
    ('Lucía Fernández',   '@lu.fernandez', NULL,               '2024-08-10', 'Alergia a ciertos tintas — verificar.'),
    ('Rodrigo Sánchez',   NULL,            '+54 11 3456-7890', '2025-01-05', NULL),
    ('Camila Torres',     '@cami.ink',     '+54 11 4567-8901', '2025-02-14', 'Cliente frecuente.');
