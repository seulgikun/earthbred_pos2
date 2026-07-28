<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use Carbon\Carbon;

class OrderController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        
        $orders = Order::with('items')
            ->whereDate('created_at', $today)
            ->orderBy('created_at', 'desc')
            ->get();
            
        // Calculate total sales for today (completed and pending, exclude void)
        $totalSales = $orders->whereIn('status', ['pending', 'completed'])->sum('total');

        return view('queue', compact('orders', 'totalSales'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.customer_name' => 'nullable|string|max:255',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.addons' => 'nullable|array',
            'items.*.addons_total' => 'nullable|numeric|min:0',
            'items.*.item_total' => 'required|numeric|min:0',
            'subtotal' => 'required|numeric|min:0',
            'discount_percent' => 'required|integer|min:0|max:100',
            'discount_amount' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
            'payment_method' => 'required|string|in:cash,gcash',
        ]);

        $order = Order::create([
            'subtotal' => $validated['subtotal'],
            'discount_percent' => $validated['discount_percent'],
            'discount_amount' => $validated['discount_amount'],
            'total' => $validated['total'],
            'payment_method' => $validated['payment_method'],
            'status' => 'pending',
        ]);

        foreach ($validated['items'] as $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'customer_name' => $item['customer_name'] ?? null,
                'product_name' => $item['product_name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'addons' => $item['addons'] ?? [],
                'addons_total' => $item['addons_total'] ?? 0,
                'item_total' => $item['item_total'],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Order processed successfully!',
            'order_id' => $order->id,
        ]);
    }

    public function updateStatus(Request $request, $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:pending,completed,void'
        ]);

        $order = Order::findOrFail($id);
        $order->status = $validated['status'];
        $order->save();

        return response()->json([
            'success' => true,
            'message' => 'Order status updated successfully!',
            'status' => $order->status
        ]);
    }
}

