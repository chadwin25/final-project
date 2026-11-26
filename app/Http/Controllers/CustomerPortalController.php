<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CustomerPortalController extends Controller
{
    public function showSignup(): View
    {
        return view('customer.auth.signup');
    }

    public function signup(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email:rfc', 'max:100', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $customer = Customer::create($data);

        Auth::guard('customer')->login($customer);
        $request->session()->regenerate();

        return redirect()->route('customer.home');
    }

    public function showLogin(): View
    {
        return view('customer.auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email:rfc'],
            'password' => ['required', 'string'],
        ]);

        if (! Auth::guard('customer')->attempt($credentials)) {
            return back()
                ->withErrors(['email' => 'Hindi nagtugma ang email at password.'])
                ->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->route('customer.home');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();
        $request->session()->regenerateToken();

        return redirect()->route('landing');
    }

    public function home(): View
    {
        $products = Product::query()
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        $cart = session('customer_cart', []);
        $cartProducts = Product::query()
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $cartLines = collect($cart)->map(function ($quantity, $productId) use ($cartProducts) {
            $product = $cartProducts->get($productId);

            if (! $product) {
                return null;
            }

            return [
                'product' => $product,
                'quantity' => (int) $quantity,
                'line_total' => (float) $product->price * (int) $quantity,
            ];
        })->filter()->values();

        $cartTotal = $cartLines->sum('line_total');

        return view('customer.home', compact(
            'products',
            'cartLines',
            'cartTotal'
        ));
    }

    public function addToCart(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        if ($product->stock_quantity < $data['quantity']) {
            throw ValidationException::withMessages([
                'quantity' => "Hanggang {$product->stock_quantity} lang ang available na stock.",
            ]);
        }

        $cart = session('customer_cart', []);
        $newQuantity = ($cart[$product->id] ?? 0) + $data['quantity'];

        if ($newQuantity > $product->stock_quantity) {
            throw ValidationException::withMessages([
                'quantity' => "Hanggang {$product->stock_quantity} lang ang available na stock.",
            ]);
        }

        $cart[$product->id] = $newQuantity;
        session(['customer_cart' => $cart]);

        return back()->with('status', 'Naidagdag sa cart ang '.$product->name.'.');
    }

    public function updateCart(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:0'],
        ]);

        $cart = session('customer_cart', []);

        if ($data['quantity'] === 0) {
            unset($cart[$product->id]);
        } else {
            if ($data['quantity'] > $product->stock_quantity) {
                throw ValidationException::withMessages([
                    'quantity' => "Hanggang {$product->stock_quantity} lang ang available na stock.",
                ]);
            }

            $cart[$product->id] = $data['quantity'];
        }

        session(['customer_cart' => $cart]);

        return back();
    }

    public function checkout(Request $request): RedirectResponse
    {
        $cart = session('customer_cart', []);

        if (empty($cart)) {
            return redirect()->route('customer.home')
                ->withErrors(['cart' => 'Magdagdag muna ng produkto sa cart.']);
        }

        $validated = $request->validate([
            'payment_method' => ['required', 'in:cash,gcash'],
            'amount_tendered' => ['nullable', 'numeric', 'min:0'],
            'payment_reference' => ['nullable', 'string', 'max:100'],
        ]);

        if (
            $validated['payment_method'] === 'gcash'
            && trim((string) ($validated['payment_reference'] ?? '')) === ''
        ) {
            throw ValidationException::withMessages([
                'payment_reference' => 'Ilagay ang GCash reference number.',
            ]);
        }

        $order = DB::transaction(function () use ($cart, $validated) {
            $productIds = array_map('intval', array_keys($cart));

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($cart)) {
                throw ValidationException::withMessages([
                    'cart' => 'May produktong hindi na available. Tingnan ulit ang menu.',
                ]);
            }

            $total = 0;

            foreach ($cart as $productId => $quantity) {
                $product = $products->get((int) $productId);
                $quantity = (int) $quantity;

                if ($quantity < 1 || $quantity > $product->stock_quantity) {
                    throw ValidationException::withMessages([
                        'cart' => "Hindi sapat ang stock para sa {$product->name}.",
                    ]);
                }

                $total += (float) $product->price * $quantity;
            }

            $amountTendered = null;
            $changeDue = 0;

            if ($validated['payment_method'] === 'cash') {
                $amountTendered = (float) ($validated['amount_tendered'] ?? 0);

                if ($amountTendered < $total) {
                    throw ValidationException::withMessages([
                        'amount_tendered' => 'Kulang ang cash na inilagay kumpara sa total.',
                    ]);
                }

                $changeDue = $amountTendered - $total;
            }

            $order = CustomerOrder::create([
                'order_code' => 'CB-'.Str::upper(Str::random(12)),
                'customer_id' => Auth::guard('customer')->id(),
                'status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_reference' => $validated['payment_method'] === 'gcash'
                    ? trim((string) $validated['payment_reference'])
                    : null,
                'payment_status' => 'pending',
                'amount_tendered' => $amountTendered,
                'change_due' => $changeDue,
                'total_price' => $total,
                'stock_reserved' => true,
            ]);

            foreach ($cart as $productId => $quantity) {
                $product = $products->get((int) $productId);
                $quantity = (int) $quantity;
                $lineTotal = (float) $product->price * $quantity;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'unit_price' => $product->price,
                    'quantity' => $quantity,
                    'line_total' => $lineTotal,
                ]);

                $product->stock_quantity -= $quantity;
                $product->save();
            }

            return $order;
        });

        session()->forget('customer_cart');

        return redirect()->route('customer.orders')
            ->with('status', 'Na-place ang order '.$order->order_code.'. Hintayin ang staff confirmation.');
    }

    public function orders(): View
    {
        $orders = CustomerOrder::query()
            ->with('items')
            ->where('customer_id', Auth::guard('customer')->id())
            ->latest()
            ->paginate(10);

        return view('customer.orders', compact('orders'));
    }
}