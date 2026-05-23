<?php
/**
 * Root entry point para XAMPP / Apache.
 * Cuando se accede a /Tatoo/ sin URL específica, Apache encuentra este archivo
 * como DirectoryIndex y delega al front controller real en public/.
 *
 * En Railway esto no se usa (doc-root ya es public/).
 */
require __DIR__ . '/public/index.php';
