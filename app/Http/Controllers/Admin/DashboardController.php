<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Product;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the Admin overview dashboard with summary analytics.
     */
    public function index(): View
    {
        $totalProducts = Product::count();
        $inStockProducts = Product::where('in_stock', true)->count();
        $totalCategories = Category::count();
        $totalInquiries = Inquiry::count();
        $newInquiriesCount = Inquiry::where('status', 'new')->count();
        $totalUsers = User::count();

        $recentInquiries = Inquiry::latest()->take(5)->get();
        $recentProducts = Product::latest()->take(5)->get();

        return view('admin.dashboard', [
            'totalProducts' => $totalProducts,
            'inStockProducts' => $inStockProducts,
            'totalCategories' => $totalCategories,
            'totalInquiries' => $totalInquiries,
            'newInquiriesCount' => $newInquiriesCount,
            'totalUsers' => $totalUsers,
            'recentInquiries' => $recentInquiries,
            'recentProducts' => $recentProducts,
        ]);
    }
}
