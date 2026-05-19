<?php
/**
 * Wrapper para que el setup sea accesible cuando el doc-root es public/
 * (Railway / php -S -t public). El archivo real vive en la raíz del proyecto.
 */
require dirname(__DIR__) . '/setup.php';
