<?php

declare(strict_types=1);

// ── Lang class lives in App\Core namespace ────────────────────────────────────
namespace App\Core {

    /**
     * Minimal i18n helper.
     * Strings live in lang/{locale}.php returning an associative array.
     */
    class Lang
    {
        private static array  $strings = [];
        private static string $locale  = 'es';

        public const AVAILABLE = ['es', 'en'];

        /**
         * Load the language file. Call once during bootstrap.
         */
        public static function load(?string $locale = null): void
        {
            if ($locale === null) {
                $locale = $_SESSION['lang'] ?? 'es';
            }

            if (!in_array($locale, self::AVAILABLE, true)) {
                $locale = 'es';
            }

            self::$locale  = $locale;
            $file = ROOT_PATH . '/lang/' . $locale . '.php';
            self::$strings = file_exists($file) ? (require $file) : [];
        }

        /**
         * Get a translated string.
         * Supports :placeholder replacement: __('client.delete', ['name' => $n])
         */
        public static function get(string $key, array $replace = []): string
        {
            $str = self::$strings[$key] ?? $key;

            foreach ($replace as $k => $v) {
                $str = str_replace(':' . $k, (string) $v, $str);
            }

            return $str;
        }

        public static function current(): string
        {
            return self::$locale;
        }

        public static function is(string $locale): bool
        {
            return self::$locale === $locale;
        }
    }

} // end namespace App\Core

// ── Global helper __() — must be in the ROOT namespace ───────────────────────
namespace {

    if (!function_exists('__')) {
        function __(string $key, array $replace = []): string
        {
            return \App\Core\Lang::get($key, $replace);
        }
    }

} // end global namespace
