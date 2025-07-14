# Laravel POS/E-commerce System Design

## Laravel Version & Requirements

**Laravel 11.x** (Latest LTS)
- PHP 8.3+
- PostgreSQL 15+
- Redis 7+
- Node.js 20+ (for frontend assets)

## Project Structure

```
pos-ecommerce/
├── app/
│   ├── Actions/              # Single-purpose action classes
│   ├── Broadcasting/         # WebSocket broadcasting
│   ├── DTOs/                # Data Transfer Objects
│   ├── Enums/               # PHP 8.1+ enums
│   ├── Events/              # Domain events
│   ├── Exceptions/          # Custom exceptions
│   ├── Http/
│   │   ├── Controllers/     # API controllers
│   │   ├── Middleware/      # Custom middleware
│   │   ├── Requests/        # Form requests
│   │   └── Resources/       # API resources
│   ├── Jobs/                # Queue jobs
│   ├── Listeners/           # Event listeners
│   ├── Models/              # Eloquent models
│   ├── Observers/           # Model observers
│   ├── Policies/            # Authorization policies
│   ├── Providers/           # Service providers
│   ├── Services/            # Business logic services
│   └── Traits/              # Reusable traits
├── bootstrap/
├── config/
├── database/
│   ├── factories/           # Model factories
│   ├── migrations/          # Database migrations
│   └── seeders/            # Database seeders
├── resources/
│   ├── js/                 # Vue.js/React frontend
│   ├── views/              # Blade templates
│   └── lang/               # Localization
├── routes/
│   ├── api.php             # API routes
│   ├── web.php             # Web routes
│   └── channels.php        # Broadcasting routes
├── storage/
├── tests/
│   ├── Feature/            # Feature tests
│   └── Unit/               # Unit tests
└── vendor/
```

## 1. Database Migrations

### Migration Files

```php
<?php
// database/migrations/2024_01_01_000001_create_tenants_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->json('settings')->nullable();
            $table->timestamps();
            
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
```

```php
<?php
// database/migrations/2024_01_01_000002_create_users_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('password');
            $table->enum('role', ['owner', 'manager', 'cashier'])->default('cashier');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->rememberToken();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'email']);
            $table->index(['tenant_id', 'role']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

```php
<?php
// database/migrations/2024_01_01_000003_create_products_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('sku', 50);
            $table->text('name');
            $table->text('description')->nullable();
            $table->integer('price_cents');
            $table->string('image_key')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['tenant_id', 'sku']);
            $table->index(['tenant_id', 'is_active']);
            $table->index(['tenant_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
```

```php
<?php
// database/migrations/2024_01_01_000004_create_inventory_movements_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('product_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity');
            $table->text('note')->nullable();
            $table->timestamp('occurred_at');
            $table->timestamps();
            
            $table->index(['product_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};
```

```php
<?php
// database/migrations/2024_01_01_000005_create_orders_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('tenant_id')->constrained()->cascadeOnDelete();
            $table->enum('channel', ['pos', 'online']);
            $table->enum('status', ['pending', 'paid', 'cancelled', 'refunded'])->default('pending');
            $table->integer('total_cents');
            $table->string('customer_email')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
            
            $table->index(['tenant_id', 'channel']);
            $table->index(['tenant_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

```php
<?php
// database/migrations/2024_01_01_000006_create_order_items_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignUuid('order_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('product_id')->constrained();
            $table->integer('quantity');
            $table->integer('unit_price_cents');
            $table->timestamps();
            
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
```

```php
<?php
// database/migrations/2024_01_01_000007_create_payments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('order_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('provider', 50);
            $table->string('provider_id', 100);
            $table->integer('amount_cents');
            $table->enum('status', ['succeeded', 'failed', 'refunded'])->default('succeeded');
            $table->timestamps();
            
            $table->index(['provider', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
```

## 2. Eloquent Models

### Base Model with Tenant Scoping

```php
<?php
// app/Models/BaseModel.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

abstract class BaseModel extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function booted(): void
    {
        static::addGlobalScope('tenant', function (Builder $builder) {
            if (auth()->check() && auth()->user()->tenant_id) {
                $builder->where('tenant_id', auth()->user()->tenant_id);
            }
        });
    }

    public function scopeWithoutTenantScope(Builder $query): Builder
    {
        return $query->withoutGlobalScope('tenant');
    }
}
```

### Tenant Model

```php
<?php
// app/Models/Tenant.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tenant extends Model
{
    use HasUuids;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'slug',
        'settings',
    ];

    protected $casts = [
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
```

### User Model

```php
<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Enums\UserRole;

class User extends Authenticatable
{
    use HasApiTokens, HasUuids, Notifiable;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'tenant_id',
        'email',
        'password',
        'role',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'role' => UserRole::class,
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function hasRole(UserRole $role): bool
    {
        return $this->role === $role;
    }

    public function canManageUsers(): bool
    {
        return $this->role === UserRole::OWNER;
    }

    public function canManageProducts(): bool
    {
        return in_array($this->role, [UserRole::OWNER, UserRole::MANAGER]);
    }
}
```

### Product Model

```php
<?php
// app/Models/Product.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\Storage;

class Product extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'sku',
        'name',
        'description',
        'price_cents',
        'image_key',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'price_cents' => 'integer',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessor for formatted price
    public function price(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->price_cents / 100,
            set: fn($value) => ['price_cents' => $value * 100]
        );
    }

    // Accessor for image URL
    public function imageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->image_key ? Storage::url($this->image_key) : null
        );
    }

    // Get current stock
    public function getCurrentStock(): int
    {
        return $this->inventoryMovements()->sum('quantity');
    }

    // Check if product is low stock
    public function isLowStock(int $threshold = 10): bool
    {
        return $this->getCurrentStock() <= $threshold;
    }

    // Scope for active products
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    // Scope for low stock products
    public function scopeLowStock($query, int $threshold = 10)
    {
        return $query->whereHas('inventoryMovements', function ($q) use ($threshold) {
            $q->havingRaw('SUM(quantity) <= ?', [$threshold]);
        });
    }
}
```

### Order Model

```php
<?php
// app/Models/Order.php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;
use App\Enums\OrderChannel;
use App\Enums\OrderStatus;

class Order extends BaseModel
{
    protected $fillable = [
        'tenant_id',
        'channel',
        'status',
        'total_cents',
        'customer_email',
        'paid_at',
    ];

    protected $casts = [
        'channel' => OrderChannel::class,
        'status' => OrderStatus::class,
        'total_cents' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    // Accessor for formatted total
    public function total(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->total_cents / 100,
            set: fn($value) => ['total_cents' => $value * 100]
        );
    }

    // Calculate total from items
    public function calculateTotal(): int
    {
        return $this->items->sum(fn($item) => $item->quantity * $item->unit_price_cents);
    }

    // Mark order as paid
    public function markAsPaid(): void
    {
        $this->update([
            'status' => OrderStatus::PAID,
            'paid_at' => now(),
        ]);
    }

    // Check if order is paid
    public function isPaid(): bool
    {
        return $this->status === OrderStatus::PAID;
    }

    // Scope for paid orders
    public function scopePaid($query)
    {
        return $query->where('status', OrderStatus::PAID);
    }
}
```

### Order Item Model

```php
<?php
// app/Models/OrderItem.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Casts\Attribute;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price_cents',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price_cents' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor for formatted unit price
    public function unitPrice(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->unit_price_cents / 100,
            set: fn($value) => ['unit_price_cents' => $value * 100]
        );
    }

    // Get total for this item
    public function getTotal(): int
    {
        return $this->quantity * $this->unit_price_cents;
    }
}
```

## 3. Enums (PHP 8.1+)

```php
<?php
// app/Enums/UserRole.php

namespace App\Enums;

enum UserRole: string
{
    case OWNER = 'owner';
    case MANAGER = 'manager';
    case CASHIER = 'cashier';

    public function getLabel(): string
    {
        return match ($this) {
            self::OWNER => 'Store Owner',
            self::MANAGER => 'Manager',
            self::CASHIER => 'Cashier',
        };
    }

    public function getPermissions(): array
    {
        return match ($this) {
            self::OWNER => ['*'],
            self::MANAGER => ['products.*', 'inventory.*', 'orders.*'],
            self::CASHIER => ['orders.create', 'orders.read', 'products.read'],
        };
    }
}
```

```php
<?php
// app/Enums/OrderStatus.php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PAID = 'paid';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function getLabel(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PAID => 'Paid',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::PENDING => 'yellow',
            self::PAID => 'green',
            self::CANCELLED => 'red',
            self::REFUNDED => 'gray',
        };
    }
}
```

```php
<?php
// app/Enums/OrderChannel.php

namespace App\Enums;

enum OrderChannel: string
{
    case POS = 'pos';
    case ONLINE = 'online';

    public function getLabel(): string
    {
        return match ($this) {
            self::POS => 'Point of Sale',
            self::ONLINE => 'Online Store',
        };
    }
}
```

## 4. DTOs (Data Transfer Objects)

```php
<?php
// app/DTOs/CreateOrderDTO.php

namespace App\DTOs;

use App\Enums\OrderChannel;

readonly class CreateOrderDTO
{
    public function __construct(
        public string $tenantId,
        public OrderChannel $channel,
        public ?string $customerEmail,
        public array $items,
        public ?string $paymentMethod = null,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tenantId: auth()->user()->tenant_id,
            channel: OrderChannel::from($data['channel']),
            customerEmail: $data['customer_email'] ?? null,
            items: $data['items'],
            paymentMethod: $data['payment_method'] ?? null,
        );
    }
}
```

```php
<?php
// app/DTOs/ProductDTO.php

namespace App\DTOs;

readonly class ProductDTO
{
    public function __construct(
        public string $tenantId,
        public string $sku,
        public string $name,
        public ?string $description,
        public int $priceCents,
        public ?string $imageKey = null,
        public bool $isActive = true,
    ) {}

    public static function fromRequest(array $data): self
    {
        return new self(
            tenantId: auth()->user()->tenant_id,
            sku: $data['sku'],
            name: $data['name'],
            description: $data['description'] ?? null,
            priceCents: $data['price_cents'],
            imageKey: $data['image_key'] ?? null,
            isActive: $data['is_active'] ?? true,
        );
    }
}
```

## 5. Actions (Single Responsibility)

```php
<?php
// app/Actions/CreateOrderAction.php

namespace App\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\InventoryMovement;
use App\DTOs\CreateOrderDTO;
use App\Events\OrderCreated;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function execute(CreateOrderDTO $dto): Order
    {
        return DB::transaction(function () use ($dto) {
            // Create order
            $order = Order::create([
                'tenant_id' => $dto->tenantId,
                'channel' => $dto->channel,
                'total_cents' => $this->calculateTotal($dto->items),
                'customer_email' => $dto->customerEmail,
            ]);

            // Create order items and update inventory
            foreach ($dto->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price_cents' => $item['unit_price_cents'],
                ]);

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
    }

    private function calculateTotal(array $items): int
    {
        return collect($items)->sum(fn($item) => $item['quantity'] * $item['unit_price_cents']);
    }
}
```

```php
<?php
// app/Actions/UpdateInventoryAction.php

namespace App\Actions;

use App\Models\InventoryMovement;
use App\Models\Product;
use App\Events\InventoryUpdated;

class UpdateInventoryAction
{
    public function execute(string $productId, int $quantity, ?string $note = null): InventoryMovement
    {
        $movement = InventoryMovement::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'note' => $note,
            'occurred_at' => now(),
        ]);

        $product = Product::find($productId);
        
        // Check for low stock
        if ($product->isLowStock()) {
            // Dispatch low stock event
            InventoryUpdated::dispatch($product, $movement);
        }

        return $movement;
    }
}
```

## 6. API Controllers

```php
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
```

```php
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
```

## 7. Form Requests

```php
<?php
// app/Http/Requests/StoreProductRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->canManageProducts();
    }

    public function rules(): array
    {
        return [
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products')->where(function ($query) {
                    return $query->where('tenant_id', auth()->user()->tenant_id);
                }),
            ],
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price_cents' => 'required|integer|min:0',
            'image_key' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'sku.unique' => 'SKU already exists in your store.',
            'price_cents.min' => 'Price must be greater than or equal to 0.',
        ];
    }
}
```

```php
<?php
// app/Http/Requests/StoreOrderRequest.php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Enums\OrderChannel;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Cashiers can create orders
    }

    public function rules(): array
    {
        return [
            'channel' => ['required', Rule::enum(OrderChannel::class)],
            'customer_email' => 'nullable|email',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|uuid|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price_cents' => 'required|integer|min:0',
            'payment_method' => 'nullable|string',
        ];
    }
}
```

## 8. API Resources

```php
<?php
// app/Http/Resources/ProductResource.php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [