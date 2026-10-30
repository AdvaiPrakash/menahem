<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    /**
     * Display the Menahem Product List Page.
     * Matches the reference e-commerce catalog layout.
     */
    public function index(Request $request): View
    {
        $content = require resource_path('content.php');
        $catalog = $content['CATALOG'] ?? [];
        $selectedCategory = $request->query('category', 'all');

        return view('products.index', [
            'content' => $content,
            'catalog' => $catalog,
            'selectedCategory' => $selectedCategory,
        ]);
    }
}
