<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Inventory;
use App\Models\ShiftNote;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    /**
     * Display the manager dashboard page view.
     */
    public function index()
    {
        return view('manager');
    }

    /**
     * Get statistics for the dashboard in JSON format.
     */
    public function getStats()
    {
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();
        $yesterdayStart = Carbon::yesterday();
        $yesterdayEnd = Carbon::yesterday()->endOfDay();

        // 1. TODAY'S SALES
        $todaySales = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        $yesterdaySales = Order::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        // Trend calculation for Sales
        $salesTrendPercent = 0;
        $salesTrendDirection = 'up';
        if ($yesterdaySales > 0) {
            $salesTrendPercent = round((($todaySales - $yesterdaySales) / $yesterdaySales) * 100);
            $salesTrendDirection = $salesTrendPercent >= 0 ? 'up' : 'down';
            $salesTrendPercent = abs($salesTrendPercent);
        } else {
            // If yesterday had 0 sales and today has sales, trend is +100%
            $salesTrendPercent = $todaySales > 0 ? 100 : 0;
            $salesTrendDirection = 'up';
        }

        // 2. ORDERS TODAY
        $todayOrders = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $yesterdayOrders = Order::whereBetween('created_at', [$yesterdayStart, $yesterdayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $ordersDiff = $todayOrders - $yesterdayOrders;
        $ordersTrendDirection = $ordersDiff >= 0 ? 'up' : 'down';
        $ordersDiffText = abs($ordersDiff) . ' ' . ($ordersDiff >= 0 ? 'more' : 'fewer') . ' than yesterday';

        // 3. AVERAGE ORDER VALUE (AOV)
        $todayAOV = $todayOrders > 0 ? ($todaySales / $todayOrders) : 0;
        $yesterdayAOV = $yesterdayOrders > 0 ? ($yesterdaySales / $yesterdayOrders) : 0;

        $aovDiff = round($todayAOV - $yesterdayAOV, 2);
        $aovTrendDirection = $aovDiff >= 0 ? 'up' : 'down';
        $aovDiffText = '₱' . abs($aovDiff) . ' vs yesterday';

        // 4. CHART DATA: LAST 7 DAYS SALES
        $chartLabels = [];
        $chartValues = []; // in thousands, e.g. 7.4
        
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayStart = $date->copy()->startOfDay();
            $dayEnd = $date->copy()->endOfDay();

            $salesForDay = Order::whereBetween('created_at', [$dayStart, $dayEnd])
                ->whereIn('status', ['pending', 'completed'])
                ->sum('total');

            // Format label as Day name abbreviation (e.g. Mon, Tue, etc.)
            $chartLabels[] = $date->format('D');
            
            // Round to 1 decimal place in thousands, e.g. 7.2k
            $chartValues[] = round($salesForDay / 1000, 1);
        }

        // 5. TOP SELLING ITEMS TODAY (fallback to all-time if today is empty)
        $topItemsToday = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($todayStart, $todayEnd) {
                $query->whereBetween('created_at', [$todayStart, $todayEnd])
                      ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        $isFallback = false;
        if ($topItemsToday->isEmpty()) {
            $isFallback = true;
            // Fallback to all-time top selling
            $topItemsToday = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
                ->whereHas('order', function ($query) {
                    $query->whereIn('status', ['pending', 'completed']);
                })
                ->groupBy('product_name')
                ->orderBy('qty_sold', 'desc')
                ->limit(5)
                ->get();
        }

        // Get total revenue for share calculation
        $totalRevenueForShare = $topItemsToday->sum('revenue');

        $topItemsFormatted = [];
        $categoriesMap = [
            'Americano' => 'Coffee',
            'Cafe Latte' => 'Coffee',
            'Cafe Mocha' => 'Coffee',
            'Matcha Drink' => 'Non-Coffee',
            'Sweetened' => 'Lemonade',
            'Strawberry Drink' => 'Lemonade',
            'Strawberry' => 'Lemonade',
            'Chicken Ala King' => 'Rice Bowls',
            'Sweet Garlic Longganisa' => 'Rice Bowls',
            'Chicken Fried Rice' => 'Rice Bowls',
            'Cheezy Bacon' => 'Rice Bowls',
            'Beef Tapa' => 'Rice Bowls',
        ];

        foreach ($topItemsToday as $item) {
            $sharePercent = $totalRevenueForShare > 0 ? round(($item->revenue / $totalRevenueForShare) * 100) : 0;
            $topItemsFormatted[] = [
                'product_name' => $item->product_name,
                'category' => $categoriesMap[$item->product_name] ?? 'Beverage',
                'qty_sold' => (int) $item->qty_sold,
                'revenue' => (float) $item->revenue,
                'share_percent' => $sharePercent
            ];
        }

        // 6. INVENTORY ALERTS (from database)
        $outOfStockItems = Inventory::where('quantity', 0)->get(['item_name'])->pluck('item_name')->toArray();
        $lowStockItems = Inventory::where('quantity', '>', 0)
            ->whereColumn('quantity', '<=', 'min_threshold')
            ->get(['item_name', 'quantity'])
            ->toArray();

        // 7. UNRESOLVED SHIFT NOTES
        $unresolvedShiftNotesCount = ShiftNote::where('is_done', false)->count();

        return response()->json([
            'success' => true,
            'today_sales' => (float) $todaySales,
            'today_sales_trend' => [
                'percent' => $salesTrendPercent,
                'direction' => $salesTrendDirection,
            ],
            'today_orders' => $todayOrders,
            'today_orders_trend' => [
                'text' => $ordersDiffText,
                'direction' => $ordersTrendDirection,
            ],
            'avg_order_value' => round($todayAOV, 2),
            'avg_order_value_trend' => [
                'text' => $aovDiffText,
                'direction' => $aovTrendDirection,
            ],
            'chart_data' => [
                'labels' => $chartLabels,
                'values' => $chartValues,
            ],
            'top_items' => $topItemsFormatted,
            'is_top_items_fallback' => $isFallback,
            'inventory_alerts' => [
                'out_of_stock' => $outOfStockItems,
                'low_stock' => $lowStockItems,
            ],
            'unresolved_shift_notes_count' => $unresolvedShiftNotesCount
        ]);
    }

    /**
     * Display the manager-specific shift notes page view.
     */
    public function shiftNotes()
    {
        $notes = ShiftNote::orderBy('created_at', 'desc')->get();
        return view('manager-shift-notes', compact('notes'));
    }

    /**
     * Display the manager-specific sales report page view.
     */
    public function salesReport()
    {
        return view('manager-sales-report');
    }

    /**
     * Get sales report datasets for Daily, Weekly, and Monthly reports.
     */
    public function getSalesData()
    {
        $categoriesMap = [
            'Americano' => 'Coffee',
            'Cafe Latte' => 'Coffee',
            'Cafe Mocha' => 'Coffee',
            'Matcha Drink' => 'Non-Coffee',
            'Sweetened' => 'Lemonade',
            'Strawberry Drink' => 'Lemonade',
            'Strawberry' => 'Lemonade',
            'Chicken Ala King' => 'Rice Bowls',
            'Sweet Garlic Longganisa' => 'Rice Bowls',
            'Chicken Fried Rice' => 'Rice Bowls',
            'Cheezy Bacon' => 'Rice Bowls',
            'Beef Tapa' => 'Rice Bowls',
        ];

        // --- 1. DAILY STATS ---
        $todayStart = Carbon::today();
        $todayEnd = Carbon::today()->endOfDay();

        $dailySales = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        $dailyOrders = Order::whereBetween('created_at', [$todayStart, $todayEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $dailyAOV = $dailyOrders > 0 ? round($dailySales / $dailyOrders, 2) : 0;

        // Daily Chart (Hourly buckets from 8 AM to 10 PM, in 2-hour increments)
        $dailyChartLabels = ['08 AM', '10 AM', '12 PM', '02 PM', '04 PM', '06 PM', '08 PM', '10 PM'];
        $dailyChartValues = [];
        foreach ($dailyChartLabels as $hourStr) {
            $hour = intval(substr($hourStr, 0, 2));
            if (strpos($hourStr, 'PM') !== false && $hour != 12) {
                $hour += 12;
            }
            if (strpos($hourStr, 'AM') !== false && $hour == 12) {
                $hour = 0;
            }

            $bucketStart = Carbon::today()->setHour($hour)->startOfHour();
            $bucketEnd = $bucketStart->copy()->addHours(2)->subSecond();

            $sum = Order::whereBetween('created_at', [$bucketStart, $bucketEnd])
                ->whereIn('status', ['pending', 'completed'])
                ->sum('total');
            $dailyChartValues[] = round($sum, 2);
        }

        // Daily Top Selling Items
        $dailyTopItems = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($todayStart, $todayEnd) {
                $query->whereBetween('created_at', [$todayStart, $todayEnd])
                      ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        $dailyMaxQty = $dailyTopItems->first() ? $dailyTopItems->first()->qty_sold : 1;
        $dailyItemsFormatted = [];
        foreach ($dailyTopItems as $item) {
            $share = $dailyMaxQty > 0 ? round(($item->qty_sold / $dailyMaxQty) * 100) : 0;
            $dailyItemsFormatted[] = [
                'product_name' => $item->product_name,
                'category' => $categoriesMap[$item->product_name] ?? 'Beverage',
                'qty_sold' => (int) $item->qty_sold,
                'revenue' => (float) $item->revenue,
                'share_percent' => $share
            ];
        }

        // --- 2. WEEKLY STATS ---
        $weeklyStart = Carbon::today()->subDays(6)->startOfDay();
        $weeklyEnd = Carbon::today()->endOfDay();

        $weeklySales = Order::whereBetween('created_at', [$weeklyStart, $weeklyEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        $weeklyOrders = Order::whereBetween('created_at', [$weeklyStart, $weeklyEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $weeklyAOV = $weeklyOrders > 0 ? round($weeklySales / $weeklyOrders, 2) : 0;

        // Weekly Chart (Daily totals for last 7 days)
        $weeklyChartLabels = [];
        $weeklyChartValues = [];
        for ($i = 6; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $weeklyChartLabels[] = $day->format('D');

            $sum = Order::whereBetween('created_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()])
                ->whereIn('status', ['pending', 'completed'])
                ->sum('total');
            $weeklyChartValues[] = round($sum, 2);
        }

        // Weekly Top Selling Items
        $weeklyTopItems = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($weeklyStart, $weeklyEnd) {
                $query->whereBetween('created_at', [$weeklyStart, $weeklyEnd])
                      ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        $weeklyMaxQty = $weeklyTopItems->first() ? $weeklyTopItems->first()->qty_sold : 1;
        $weeklyItemsFormatted = [];
        foreach ($weeklyTopItems as $item) {
            $share = $weeklyMaxQty > 0 ? round(($item->qty_sold / $weeklyMaxQty) * 100) : 0;
            $weeklyItemsFormatted[] = [
                'product_name' => $item->product_name,
                'category' => $categoriesMap[$item->product_name] ?? 'Beverage',
                'qty_sold' => (int) $item->qty_sold,
                'revenue' => (float) $item->revenue,
                'share_percent' => $share
            ];
        }

        // --- 3. MONTHLY STATS ---
        $monthlyStart = Carbon::today()->subDays(29)->startOfDay();
        $monthlyEnd = Carbon::today()->endOfDay();

        $monthlySales = Order::whereBetween('created_at', [$monthlyStart, $monthlyEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->sum('total');

        $monthlyOrders = Order::whereBetween('created_at', [$monthlyStart, $monthlyEnd])
            ->whereIn('status', ['pending', 'completed'])
            ->count();

        $monthlyAOV = $monthlyOrders > 0 ? round($monthlySales / $monthlyOrders, 2) : 0;

        // Monthly Chart (Weekly buckets for last 4 weeks)
        $monthlyChartLabels = ['Week 1', 'Week 2', 'Week 3', 'Week 4'];
        $monthlyChartValues = [];
        for ($w = 3; $w >= 0; $w--) {
            $bucketStart = Carbon::today()->subWeeks($w)->startOfWeek();
            $bucketEnd = $bucketStart->copy()->endOfWeek();

            $sum = Order::whereBetween('created_at', [$bucketStart, $bucketEnd])
                ->whereIn('status', ['pending', 'completed'])
                ->sum('total');
            $monthlyChartValues[] = round($sum, 2);
        }

        // Monthly Top Selling Items
        $monthlyTopItems = OrderItem::select('product_name', DB::raw('SUM(quantity) as qty_sold'), DB::raw('SUM(item_total) as revenue'))
            ->whereHas('order', function ($query) use ($monthlyStart, $monthlyEnd) {
                $query->whereBetween('created_at', [$monthlyStart, $monthlyEnd])
                      ->whereIn('status', ['pending', 'completed']);
            })
            ->groupBy('product_name')
            ->orderBy('qty_sold', 'desc')
            ->limit(5)
            ->get();

        $monthlyMaxQty = $monthlyTopItems->first() ? $monthlyTopItems->first()->qty_sold : 1;
        $monthlyItemsFormatted = [];
        foreach ($monthlyTopItems as $item) {
            $share = $monthlyMaxQty > 0 ? round(($item->qty_sold / $monthlyMaxQty) * 100) : 0;
            $monthlyItemsFormatted[] = [
                'product_name' => $item->product_name,
                'category' => $categoriesMap[$item->product_name] ?? 'Beverage',
                'qty_sold' => (int) $item->qty_sold,
                'revenue' => (float) $item->revenue,
                'share_percent' => $share
            ];
        }

        return response()->json([
            'success' => true,
            'daily' => [
                'revenue' => (float) $dailySales,
                'orders_count' => $dailyOrders,
                'aov' => $dailyAOV,
                'chart' => [
                    'labels' => $dailyChartLabels,
                    'values' => $dailyChartValues
                ],
                'top_items' => $dailyItemsFormatted
            ],
            'weekly' => [
                'revenue' => (float) $weeklySales,
                'orders_count' => $weeklyOrders,
                'aov' => $weeklyAOV,
                'chart' => [
                    'labels' => $weeklyChartLabels,
                    'values' => $weeklyChartValues
                ],
                'top_items' => $weeklyItemsFormatted
            ],
            'monthly' => [
                'revenue' => (float) $monthlySales,
                'orders_count' => $monthlyOrders,
                'aov' => $monthlyAOV,
                'chart' => [
                    'labels' => $monthlyChartLabels,
                    'values' => $monthlyChartValues
                ],
                'top_items' => $monthlyItemsFormatted
            ]
        ]);
    }
}
