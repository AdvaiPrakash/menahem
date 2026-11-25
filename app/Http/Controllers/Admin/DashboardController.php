<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CustomerLead;
use App\Models\Product;
use App\Models\User;
use App\Services\AdminLiveDataService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(protected AdminLiveDataService $liveDataService) {}

    /**
     * Display the Admin overview dashboard with summary analytics.
     */
    public function index(): View
    {
        $totalProducts = Product::count();
        $inStockProducts = Product::where('stock_quantity', '>', 0)->count();
        $lowStockCount = Product::where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        $totalCategories = Category::count();
        $totalLeads = CustomerLead::count();
        $totalUsers = User::count();
        $recentProducts = Product::latest()->take(5)->get();

        $liveData = $this->liveDataService->getDashboardData();

        return view('admin.dashboard', [
            'totalProducts' => $totalProducts,
            'inStockProducts' => $inStockProducts,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'totalCategories' => $totalCategories,
            'paidOrdersCount' => $liveData['paidOrdersCount'],
            'totalRevenue' => $liveData['totalRevenue'],
            'totalLeads' => $totalLeads,
            'totalInquiries' => $liveData['totalInquiries'],
            'newInquiriesCount' => $liveData['newInquiriesCount'],
            'totalUsers' => $totalUsers,
            'recentOrders' => $liveData['recentOrders'],
            'recentInquiries' => $liveData['recentInquiries'],
            'recentProducts' => $recentProducts,
        ]);
    }
}
