<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\CustomerLead;
use App\Models\Inquiry;
use App\Models\Order;
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
        $inStockProducts = Product::where('stock_quantity', '>', 0)->count();
        $lowStockCount = Product::where('stock_quantity', '>', 0)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        $totalCategories = Category::count();
        $totalOrders = Order::count();
        $paidOrdersCount = Order::where('payment_status', 'paid')->count();
        $totalRevenue = (float) Order::where('payment_status', 'paid')->sum('grand_total');
        $totalLeads = CustomerLead::count();

        $totalInquiries = Inquiry::count();
        $newInquiriesCount = Inquiry::where('status', 'new')->count();
        $totalUsers = User::count();

        $recentOrders = Order::with('invoice')->latest()->take(5)->get();
        $recentInquiries = Inquiry::latest()->take(5)->get();
        $recentProducts = Product::latest()->take(5)->get();

        return view('admin.dashboard', [
            'totalProducts' => $totalProducts,
            'inStockProducts' => $inStockProducts,
            'lowStockCount' => $lowStockCount,
            'outOfStockCount' => $outOfStockCount,
            'totalCategories' => $totalCategories,
            'totalOrders' => $totalOrders,
            'paidOrdersCount' => $paidOrdersCount,
            'totalRevenue' => $totalRevenue,
            'totalLeads' => $totalLeads,
            'totalInquiries' => $totalInquiries,
            'newInquiriesCount' => $newInquiriesCount,
            'totalUsers' => $totalUsers,
            'recentOrders' => $recentOrders,
            'recentInquiries' => $recentInquiries,
            'recentProducts' => $recentProducts,
        ]);
    }
}
