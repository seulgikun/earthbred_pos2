<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\InventoryLog;

class InventoryController extends Controller
{
    /**
     * Display a listing of the inventory items.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $items = Inventory::all();
        
        // Calculate status dynamically for safety/consistency
        $items->map(function ($item) {
            if ($item->quantity == 0) {
                $item->status_label = 'Out of stock';
                $item->status_class = 'status-out-of-stock';
            } elseif ($item->quantity <= $item->min_threshold) {
                $item->status_label = 'Low Stock';
                $item->status_class = 'status-low-stock';
            } else {
                $item->status_label = 'In Stock';
                $item->status_class = 'status-in-stock';
            }
            return $item;
        });

        return response()->json($items);
    }

    /**
     * Add stock to an existing item (+Qty).
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function addStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity_added' => 'required|integer|min:1',
        ]);

        $item = Inventory::findOrFail($id);
        $oldQty = $item->quantity;
        $added = $validated['quantity_added'];
        $newQty = $oldQty + $added;

        $item->quantity = $newQty;
        $item->latest_issue_type = 'Restocked';
        $item->save();

        InventoryLog::create([
            'inventory_id' => $item->id,
            'quantity_changed' => $added,
            'issue_type' => 'Restocked',
            'notes' => 'Stock added manually'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock added successfully!',
            'item' => $item
        ]);
    }

    /**
     * Edit/correct stock quantity and set custom issue type.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function editStock(Request $request, $id)
    {
        $validated = $request->validate([
            'quantity' => 'required|integer|min:0',
            'issue_type' => 'required|string|max:255',
            'notes' => 'nullable|string|max:500',
        ]);

        $item = Inventory::findOrFail($id);
        $oldQty = $item->quantity;
        $newQty = $validated['quantity'];
        $diff = $newQty - $oldQty;

        // Save new values
        $item->quantity = $newQty;
        $item->latest_issue_type = $validated['issue_type'];
        $item->save();

        // Create audit log entry
        InventoryLog::create([
            'inventory_id' => $item->id,
            'quantity_changed' => $diff,
            'issue_type' => $validated['issue_type'],
            'notes' => $validated['notes'] ?? 'Manual stock correction'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Stock updated successfully!',
            'item' => $item
        ]);
    }

    /**
     * Retrieve transaction logs.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLogs()
    {
        $logs = InventoryLog::with('inventory')
            ->orderBy('created_at', 'desc')
            ->take(50)
            ->get();
            
        return response()->json($logs);
    }

    /**
     * Store a newly created item in the inventory.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function storeItem(Request $request)
    {
        $validated = $request->validate([
            'item_name' => 'required|string|max:255|unique:inventories,item_name',
            'category' => 'required|string|max:255',
            'quantity' => 'required|integer|min:0',
            'min_threshold' => 'required|integer|min:1',
        ]);

        $item = Inventory::create([
            'item_name' => $validated['item_name'],
            'category' => $validated['category'],
            'quantity' => $validated['quantity'],
            'min_threshold' => $validated['min_threshold'],
            'latest_issue_type' => 'Restocked'
        ]);

        InventoryLog::create([
            'inventory_id' => $item->id,
            'quantity_changed' => $item['quantity'],
            'issue_type' => 'Restocked',
            'notes' => 'Initial setup of new inventory item'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'New item added successfully!',
            'item' => $item
        ]);
    }
    public function destroy($id)
    {
        $item = Inventory::findOrFail($id);
        // Delete related logs first to avoid foreign key constraints (if any)
        InventoryLog::where('inventory_id', $item->id)->delete();
        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully!'
        ]);
    }
}
