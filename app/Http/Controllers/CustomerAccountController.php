<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class CustomerAccountController extends Controller
{
    /**
     * Display customer order history.
     */
    public function orders(Request $request): View
    {
        $content = require resource_path('content.php');
        $user = Auth::user();

        $orders = Order::where(function ($q) use ($user) {
            $q->where('user_id', $user->id)
                ->orWhere('customer_email', $user->email);
        })
            ->with(['items', 'invoice'])
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('account.orders', [
            'content' => $content,
            'user' => $user,
            'orders' => $orders,
        ]);
    }

    /**
     * Display details of a specific order.
     */
    public function showOrder(string $orderNumber): View
    {
        $content = require resource_path('content.php');
        $user = Auth::user();

        $order = Order::where('order_number', $orderNumber)
            ->where(function ($q) use ($user) {
                $q->where('user_id', $user->id)
                    ->orWhere('customer_email', $user->email);
            })
            ->with(['items', 'invoice'])
            ->firstOrFail();

        return view('account.order-detail', [
            'content' => $content,
            'user' => $user,
            'order' => $order,
        ]);
    }
}
