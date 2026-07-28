<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Models\Inventory;
use App\Models\Order;
use App\Models\ShiftNote;

class AiController extends Controller
{
    public function chat(Request $request)
    {
        $request->validate([
            'message' => 'required|string'
        ]);

        $apiKey = trim(env('GEMINI_API_KEY', ''));

        if (empty($apiKey) || str_contains($apiKey, 'your_key')) {
            return response()->json([
                'success' => false,
                'message' => 'GEMINI_API_KEY is missing in your .env file.'
            ], 400);
        }

        // Fetch Live Database Context
        try {
            $productsList = Product::select('name', 'category', 'price', 'discounted_price')->get()->map(function($p) {
                $priceStr = "₱" . number_format($p->price, 2);
                if ($p->discounted_price) {
                    $priceStr .= " (Discounted: ₱" . number_format($p->discounted_price, 2) . ")";
                }
                return "- {$p->name} [Category: {$p->category}] - Price: {$priceStr}";
            })->implode("\n");

            $inventoryList = Inventory::select('item_name', 'quantity', 'unit', 'status')->get()->map(function($i) {
                return "- {$i->item_name}: {$i->quantity} {$i->unit} (Status: {$i->status})";
            })->implode("\n");

            $totalOrders = Order::count();
            $totalSales = Order::sum('total');
            $salesSummary = "Total Orders Processed: {$totalOrders} | Total Revenue: ₱" . number_format($totalSales, 2);

            $recentNotes = ShiftNote::latest()->take(5)->get()->map(function($n) {
                return "- [{$n->category}] {$n->note} (By: {$n->cashier_name})";
            })->implode("\n");
        } catch (\Exception $e) {
            $productsList = "Unavailable";
            $inventoryList = "Unavailable";
            $salesSummary = "Unavailable";
            $recentNotes = "Unavailable";
        }

        // Build Strict System Context Constraint
        $systemContext = <<<EOT
You are the official AI Assistant for Earthbred Coffee Studio.

STRICT SCOPE & SAFETY BOUNDARIES:
1. You MUST ONLY answer questions directly related to:
   - Earthbred Coffee Studio's database (menu items, prices, inventory stock levels, sales reports, shift notes).
   - Coffee, espresso beverages, non-coffee drinks, café food items, coffee brewing, café management, and POS shop operations.
2. IF THE USER ASKS A QUESTION UNRELATED to Earthbred Coffee Studio, coffee, café products, inventory, or café management (for example: world news, history, sports, video games, general coding, general science, or random trivia), YOU MUST POLITELY REFUSE by stating:
   "I am Earthbred's AI Assistant. I can only assist with Earthbred Coffee Studio operations, menu products, inventory, sales data, and coffee-related topics."

CURRENT LIVE EARTHBRED DATABASE CONTEXT:
[MENU PRODUCTS]
{$productsList}

[INVENTORY STOCKS]
{$inventoryList}

[SALES & REVENUE SUMMARY]
{$salesSummary}

[RECENT SHIFT NOTES]
{$recentNotes}

FORMATTING INSTRUCTIONS:
- Keep your responses professional, concise, encouraging, and clear.
- Use clean Markdown formatting (bullet points, bold text).
EOT;

        $userMessage = $request->message;
        $combinedPrompt = $systemContext . "\n\nUser Prompt: " . $userMessage;

        // Supported models
        $modelsToTry = ['gemini-2.0-flash', 'gemini-1.5-flash', 'gemini-1.5-flash-latest'];
        $lastErrorMessage = '';

        foreach ($modelsToTry as $model) {
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

            try {
                $response = Http::withHeaders([
                    'Content-Type' => 'application/json',
                    'x-goog-api-key' => $apiKey
                ])->post($url, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $combinedPrompt]
                            ]
                        ]
                    ]
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    $reply = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Sorry, I could not process your request.';
                    return response()->json([
                        'success' => true,
                        'reply' => $reply
                    ]);
                } else {
                    $errData = $response->json();
                    $lastErrorMessage = $errData['error']['message'] ?? 'API error';
                    Log::warning("Gemini model {$model} failed: " . $lastErrorMessage);
                }
            } catch (\Exception $ex) {
                $lastErrorMessage = $ex->getMessage();
            }
        }

        return response()->json([
            'success' => false,
            'message' => 'Gemini API Error: ' . $lastErrorMessage
        ], 400);
    }
}
