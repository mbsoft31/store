<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryMovement;
use App\DTOs\CreateOrderDTO;
use App\Events\OrderCreated;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Log;
use Throwable;

class CreateOrderAction
{

    public CreateOrderDTO $dto;

    /**
     * @throws Throwable
     */
    public function execute(CreateOrderDTO $dto): Order
    {
        if (empty($dto->items)) {
            throw new InvalidArgumentException('Order items cannot be empty.');
        }

        $this->dto = $dto;

        try {
            return DB::transaction(function () use ($dto) {


                // Create order
                $order = Order::create(
                    $this->getOrderDetails()
                );

                // Create order items and update inventory
                foreach ($dto->items as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price_cents' => $item['unit_price_cents'],
                    ]);
                    /*OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price_cents' => $item['unit_price_cents'],
                    ]);*/

                    // Decrease inventory
                    InventoryMovement::create([
                        'product_id' => $item['product_id'],
                        'quantity' => -$item['quantity'],
                        'note' => "Sale - Order {$order->id}",
                        'occurred_at' => now(),
                    ]);
                }

                // Dispatch event
                OrderCreated::dispatch($order);

                return $order->load(['items.product']);
            });
        } catch (Exception $e) {
            // Log the exception or handle as needed
            Log::error('Order creation failed: ' . $e->getMessage(), ['exception' => $e]);
            throw $e; // Rethrow or handle as appropriate
        }
    }

    /**
     * @param array $items
     * @return int
     */
    private function calculateTotal(array $items): int
    {
        return collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price_cents']);
    }

    /**
     * get Order details
     * @return array
     */
    private function getOrderDetails(): array
    {
        return [
            'tenant_id' => $this->dto->tenantId,
            'channel' => $this->dto->channel,
            'total_cents' => $this->calculateTotal($this->dto->items),
            'customer_email' => $this->dto->customerEmail,
            'items' => $this->dto->items,
        ];
    }
}
