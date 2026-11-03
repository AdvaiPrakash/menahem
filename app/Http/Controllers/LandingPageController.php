<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingPageController extends Controller
{
    /**
     * Display the Menahem landing homepage.
     * Content is read from the single-source-of-truth content file.
     */
    public function index(): View
    {
        $content = require resource_path('content.php');

        return view('landing.index', [
            'content' => $content,
        ]);
    }
}
