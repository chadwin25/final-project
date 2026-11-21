<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $search = mb_substr(trim((string) $request->query('search', '')), 0, 100);
        $showArchived = $request->query('view') === 'archived';

        $products = Product::query()
            ->when($showArchived, fn ($query) => $query->onlyTrashed())
            ->when(
                $search !== '',
                fn ($query) => $query->where('name', 'like', '%'.$search.'%')
            )
            ->latest('id')
            ->paginate(12)
            ->withQueryString();

        return view('products.index', compact('products', 'search', 'showArchived'));
    }

    public function create(): View
    {
        return view('products.form', [
            'product' => new Product(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $newImage = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $request->file('image')->store('products', 'public');

                if ($newImage === false) {
                    throw new \RuntimeException('The product image could not be saved.');
                }

                $data['image'] = $newImage;
            }

            Product::create($data);
        } catch (Throwable $exception) {
            if (is_string($newImage)) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        return redirect()->route('products.index')
            ->with('status', 'Product added.');
    }

    public function edit(Product $product): View
    {
        return view('products.form', compact('product'));
    }

    public function update(ProductRequest $request, Product $product): RedirectResponse
    {
        $data = $request->safe()->except('image');
        $oldImage = $product->image;
        $newImage = null;

        try {
            if ($request->hasFile('image')) {
                $newImage = $request->file('image')->store('products', 'public');

                if ($newImage === false) {
                    throw new \RuntimeException('The product image could not be saved.');
                }

                $data['image'] = $newImage;
            }

            $product->update($data);
        } catch (Throwable $exception) {
            if (is_string($newImage)) {
                Storage::disk('public')->delete($newImage);
            }

            throw $exception;
        }

        if (is_string($newImage) && $oldImage) {
            Storage::disk('public')->delete($oldImage);
        }

        return redirect()->route('products.index')
            ->with('status', 'Product updated.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('status', 'Product archived. Past orders will still retain it.');
    }

    public function restore(int $product): RedirectResponse
    {
        Product::onlyTrashed()->findOrFail($product)->restore();

        return redirect()->route('products.index', ['view' => 'archived'])
            ->with('status', 'Product restored.');
    }
}
