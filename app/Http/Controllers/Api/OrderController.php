<?php
// app/Http/Controllers/Api/OrderController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Actions\CreateOrderAction;
use App\DTOs\CreateOrderDTO;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private CreateOrderAction $createOrderAction
    ) {}

    public function index(Request $request)
    {
        $orders = Order::query()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->channel, fn($q) => $q->where('channel', $request->channel))
            ->when($request->start_date, fn($q) => $q->whereDate('created_at', '>=', $request->start_date))
            ->when($request->end_date, fn($q) => $q->whereDate('created_at', '<=', $request->end_date))
            ->with(['items.product', 'payment'])
            ->latest()
            ->paginate($request->get('limit', 20));

        return OrderResource::collection($orders);
    }

    public function store(StoreOrderRequest $request)
    {
        $dto = CreateOrderDTO::fromRequest($request->validated());
        $order = $this->createOrderAction->execute($dto);

        return new OrderResource($order);
    }

    public function show(Order $order)
    {
        return new OrderResource($order->load(['items.product', 'payment']));
    }
}
