<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrder;
use App\Models\Product;
use App\Models\Sale;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StaffCustomerOrderController extends Controller
{
    public function index(): View
    {
        $orders = CustomerOrder::query()
            ->with(['customer', 'items'])
            ->latest()
            ->paginate(15);

        return view('customer-orders.index', compact('orders'));
    }

    public function update(Request $request, CustomerOrder $customerOrder): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', 'in:verify_payment,preparing,ready,complete,cancel'],
        ]);

        DB::transaction(function () use ($data, $customerOrder, $request) {
            $order = CustomerOrder::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($customerOrder->id);

            if (in_array($order->status, ['completed', 'cancelled'], true)) {
                throw ValidationException::withMessages([
                    'action' => 'Tapos na ang order na ito at hindi na mababago.',
                ]);
            }

            if ($data['action'] === 'verify_payment') {
                $order->payment_status = 'paid';
                $order->save();

                return;
            }

            if ($data['action'] === 'preparing') {
                if ($order->payment_status !== 'paid') {
                    throw ValidationException::withMessages([
                        'action' => 'I-confirm muna ang bayad bago ihanda ang order.',
                    ]);
                }

                $order->status = 'preparing';
                $order->save();

                return;
            }

            if ($data['action'] === 'ready') {
                if ($order->status !== 'preparing') {
                    throw ValidationException::withMessages([
                        'action' => 'I-set muna sa Preparing bago markahang Ready.',
                    ]);
                }

                $order->status = 'ready';
                $order->save();

                return;
            }

            if ($data['action'] === 'complete') {
                if ($order->status !== 'ready' || $order->payment_status !== 'paid') {
                    throw ValidationException::withMessages([
                        'action' => 'Dapat paid at ready muna ang order bago i-complete.',
                    ]);
                }

                foreach ($order->items as $item) {
                    Sale::create([
                        'product_id' => $item->product_id,
                        'customer_id' => $order->customer_id,
                        'sold_by' => $request->user()->id,
                        'quantity' => $item->quantity,
                        'total_price' => $item->line_total,
                    ]);
                }

                $order->status = 'completed';
                $order->stock_reserved = false;
                $order->save();

                return;
            }

            if ($data['action'] === 'cancel') {
                if ($order->payment_status === 'paid') {
                    throw ValidationException::withMessages([
                        'action' => 'Paid na ang order. Sundin muna ang refund process bago ito kanselahin.',
                    ]);
                }

                if ($order->stock_reserved) {
                    foreach ($order->items as $item) {
                        $product = Product::withTrashed()
                            ->lockForUpdate()
                            ->find($item->product_id);

                        if ($product) {
                            $product->stock_quantity += $item->quantity;
                            $product->save();
                        }
                    }
                }

                $order->status = 'cancelled';
                $order->stock_reserved = false;
                $order->save();
            }
        });

        return redirect()->route('customer-orders.index')
            ->with('status', 'Na-update ang customer order.');
    }
}