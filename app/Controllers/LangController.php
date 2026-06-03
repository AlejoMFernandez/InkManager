<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Lang;

class LangController extends Controller
{
    /** GET /lang/{locale} — switch language and redirect back */
    public function switch(array $params = []): void
    {
        $locale = $params['locale'] ?? 'es';

        if (in_array($locale, Lang::AVAILABLE, true)) {
            $_SESSION['lang'] = $locale;
        }

        $this->back();
    }
}
