<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\OrderItem;
use App\Support\PosCache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $page = max($request->integer('page', 1), 1);

        $products = Cache::remember(PosCache::productsIndexKey($page), now()->addMinutes(5), function () use ($page) {
            $products = Product::query()
                ->select(['id', 'name', 'price', 'stock', 'category_id'])
                ->with('category:id,name')
                ->paginate(15, ['*'], 'page', $page);

            $products->setCollection(
                $products->getCollection()->map(function (Product $product) {
                    return [
                        'id'       => $product->id,
                        'name'     => $product->name,
                        'price'    => $product->price,
                        'stock'    => $product->stock,
                        'category' => optional($product->category)->name,
                    ];
                })
            );

            return $products->toArray();
        });

        return response()->json($products);
    }

    public function salesReport(Request $request)
    {
        $page = max($request->integer('page', 1), 1);

        $report = OrderItem::query()
            ->select(['id', 'order_id', 'product_id', 'quantity', 'unit_price'])
            ->with([
                'order:id,customer_id',
                'order.customer:id,name',
                'product:id,name',
            ])
            ->paginate(15, ['*'], 'page', $page);

        $report->setCollection(
            $report->getCollection()->map(function (OrderItem $item) {
                return [
                    'order_id'     => $item->order_id,
                    'product_name' => optional($item->product)->name,
                    'qty'          => $item->quantity,
                    'total'        => $item->quantity * $item->unit_price,
                    'customer'     => optional($item->order?->customer)->name,
                ];
            })
        );

        return response()->json($report);
    }

    public function dashboard()
    {
        $dashboard = Cache::remember(PosCache::productsDashboardKey(), now()->addMinutes(5), function () {
            $totalProducts = Product::all()->count();
            $totalOrders   = Order::all()->count();
            $totalRevenue  = Order::all()->sum('total_amount');
            $categories    = Category::all();

            $topProducts = Product::all()
                ->sortByDesc('sold_count')
                ->take(5)
                ->values();

            return [
                'total_products' => $totalProducts,
                'total_orders'   => $totalOrders,
                'total_revenue'  => $totalRevenue,
                'categories'     => $categories,
                'top_products'   => $topProducts,
            ];
        });

        return response()->json($dashboard);
    }

    public function search(Request $request)
    {
        $keyword  = $request->input('q');
        $products = Product::where('name', 'LIKE', '%' . $keyword . '%')
                           ->orWhere('description', 'LIKE', '%' . $keyword . '%')
                           ->get();

        return response()->json($products);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'stock'       => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $product = Product::create($request->all());
        PosCache::forgetProductReads();

        return response()->json($product, 201);
    }
}
