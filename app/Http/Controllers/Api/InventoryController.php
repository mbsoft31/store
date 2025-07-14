<?php
// app/Http/Controllers/Api/InventoryController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryMovement;
use App\Models\Product;
use App\Http\Requests\CreateInventoryMovementRequest;
use App\Http\Resources\InventoryMovementResource;
use App\Http\Resources\ProductResource;
use App\Actions\UpdateInventoryAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(
        private readonly UpdateInventoryAction $updateInventoryAction
    ) {}

    public function movements(Request $request)
    {
        $movements = InventoryMovement::query()
            ->with(['product'])
            ->when($request->product_id, fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->start_date, fn($q) => $q->whereDate('occurred_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('occurred_at', '<=', $request->end_date))
            ->latest('occurred_at')
            ->paginate($request->get('limit', 20));

        return InventoryMovementResource::collection($movements);
    }

    public function createMovement(CreateInventoryMovementRequest $request)
    {
        $movement = $this->updateInventoryAction->execute(
            $request->product_id,
            $request->quantity,
            $request->note
        );

        return new InventoryMovementResource($movement->load('product'));
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function lowStock(Request $request)
    {
        try {
            $threshold = $request->get('threshold', 10);
            $products = Product::with(['inventoryMovements'])
                ->get()
                ->filter(fn($product) => $product->isLowStock($threshold))
                ->values();
            return response()->json([
                'products' => $products->map(function ($product) use ($threshold) {
                    return [
                        'id' => $product->id,
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'current_stock' => $product->getCurrentStock(),
                        'threshold' => $threshold,
                        'price_cents' => $product->price_cents,
                    ];
                }),
                'count' => $products->count(),
            ]);
        } catch (\Exception $e) {
            dump($e->getMessage());
        }
    }
}
