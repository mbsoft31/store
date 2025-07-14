<?php
// app/Http/Controllers/Api/ProductController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\DTOs\ProductDTO;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::query()
            ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->boolean('active'), fn($q) => $q->active())
            ->when($request->boolean('low_stock'), fn($q) => $q->lowStock())
            ->with(['inventoryMovements'])
            ->paginate($request->get('limit', 20));

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request)
    {
        $dto = ProductDTO::fromRequest($request->validated());
        
        $product = Product::create([
            'tenant_id' => $dto->tenantId,
            'sku' => $dto->sku,
            'name' => $dto->name,
            'description' => $dto->description,
            'price_cents' => $dto->priceCents,
            'image_key' => $dto->imageKey,
            'is_active' => $dto->isActive,
        ]);

        return new ProductResource($product);
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load(['inventoryMovements']));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());
        
        return new ProductResource($product);
    }

    public function destroy(Product $product)
    {
        $product->delete();
        
        return response()->json(['message' => 'Product deleted successfully']);
    }
}
