<?php

namespace App\Http\Livewire\Pos;

use App\Models\Category;
use App\Models\Product;
use Livewire\Component;

class PosInterface extends Component
{
    public string $search = '';
    public ?int $selectedCategory = null;
    public array $cart = [];

    protected $listeners = [
        'pos:addToCart' => 'addToCartFromEvent',
        'clearCart'      => 'clearCart',
    ];

    public function mount()
    {
        $this->cart = [];
    }

    public function render()
    {
        $query = Product::query()->where('is_active', true);
        if ($this->selectedCategory) {
            $query->where('category_id', $this->selectedCategory);
        }
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->search}%")
                  ->orWhere('barcode', 'like', "%{$this->search}%");
            });
        }

        $products = $query->with(['category', 'recipes.rawMaterial'])->limit(100)->get();
        $categories = Category::where('is_active', true)->get();

        return view('livewire.pos.pos-interface', [
            'products' => $products,
            'categories' => $categories,
        ]);
    }

    public function scanBarcode(): void
    {
        $barcode = trim($this->search);
        if (empty($barcode)) {
            return;
        }

        $product = Product::where('is_active', true)
            ->where(function ($q) use ($barcode) {
                $q->where('barcode', $barcode)
                  ->orWhere('name', $barcode);
            })
            ->first();

        if ($product) {
            $this->addToCart($product->id, 'M');
            $this->search = '';
            session()->flash('success', "Produk '{$product->name}' berhasil ditambahkan ke keranjang.");
        } else {
            session()->flash('warning', "Produk dengan barcode '{$barcode}' tidak ditemukan.");
        }
    }

    public function addToCart(int $productId, string $size = 'M')
    {
        $key = $productId . '_' . $size;
        $currentQty = isset($this->cart[$key]) ? $this->cart[$key]['quantity'] : 0;
        $targetQty = $currentQty + 1;

        $product = Product::with('recipes.rawMaterial')->find($productId);
        if (! $product) {
            return;
        }

        // Validate stock availability before adding to cart
        if (! $product->isAvailable($size, $targetQty)) {
            session()->flash('warning', "Stok bahan baku untuk menu '{$product->name}' tidak mencukupi.");
            if (method_exists($this, 'dispatch')) {
                $this->dispatch('alert', ['type' => 'warning', 'message' => "Stok bahan baku untuk '{$product->name}' tidak mencukupi."]);
            }
            return;
        }

        if (isset($this->cart[$key])) {
            $this->cart[$key]['quantity'] = $targetQty;
        } else {
            $this->cart[$key] = [
                'product_id' => $productId,
                'name' => $product->name,
                'size' => $size,
                'price' => $product->priceForSize($size),
                'quantity' => 1,
                'notes' => '',
                'sugar_level' => 'normal',
            ];
        }

        if (method_exists($this, 'dispatch')) {
            $this->dispatch('cartUpdated');
        } elseif (method_exists($this, 'emit')) {
            $this->emit('cartUpdated');
        }
    }

    public function addToCartFromEvent(array $payload): void
    {
        $this->addToCart($payload['product_id'], $payload['size'] ?? 'M');
    }

    public function removeFromCart(string $key)
    {
        unset($this->cart[$key]);
        if (method_exists($this, 'dispatch')) {
            $this->dispatch('cartUpdated');
        } elseif (method_exists($this, 'emit')) {
            $this->emit('cartUpdated');
        }
    }

    public function updateQty(string $key, int $qty)
    {
        if ($qty <= 0) {
            $this->removeFromCart($key);
            return;
        }

        $item = $this->cart[$key] ?? null;
        if ($item) {
            $product = Product::with('recipes.rawMaterial')->find($item['product_id']);
            if ($product && ! $product->isAvailable($item['size'] ?? 'M', $qty)) {
                session()->flash('warning', "Stok bahan baku untuk menu '{$product->name}' tidak mencukupi untuk jumlah {$qty}.");
                if (method_exists($this, 'dispatch')) {
                    $this->dispatch('alert', ['type' => 'warning', 'message' => "Stok bahan baku untuk '{$product->name}' tidak mencukupi."]);
                }
                return;
            }
        }

        $this->cart[$key]['quantity'] = $qty;
        if (method_exists($this, 'dispatch')) {
            $this->dispatch('cartUpdated');
        } elseif (method_exists($this, 'emit')) {
            $this->emit('cartUpdated');
        }
    }

    public function clearCart()
    {
        $this->cart = [];
        if (method_exists($this, 'dispatch')) {
            $this->dispatch('cartUpdated');
        } elseif (method_exists($this, 'emit')) {
            $this->emit('cartUpdated');
        }
    }

    public function emitOpenCheckout()
    {
        if (empty($this->cart)) {
            return;
        }
        if (method_exists($this, 'dispatch')) {
            $this->dispatch('openCheckout', cart: $this->cart);
        } elseif (method_exists($this, 'emit')) {
            $this->emit('openCheckout', $this->cart);
        }
    }

    public function getCartTotalProperty(): float
    {
        return collect($this->cart)->sum(fn($i) => $i['price'] * $i['quantity']);
    }
}
