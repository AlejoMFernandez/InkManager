<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class LandingController extends Controller
{
    /** GET / — Public landing page. Redirects to dashboard if already logged in. */
    public function index(array $params = []): void
    {
        if (Auth::check()) {
            $this->redirect('dashboard');
        }

        $this->render('landing', [], null);
    }
}
