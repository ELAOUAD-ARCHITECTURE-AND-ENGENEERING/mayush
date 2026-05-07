<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\Category;
use App\Models\User;
use App\Models\CombinedOrder;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Address;
use App\Models\BusinessSetting;
use Auth;
use Session;

class PosController extends Controller
{
    public function index()
    {
        $customers = User::where('user_type', 'customer')->get();
        return view('backend.pos.index', compact('customers'));
    }

    public function search_product(Request $request)
    {
        $keyword = $request->keyword;
        $category = $request->category;
        $brand = $request->brand;

        $product_query = ProductStock::query();
        $product_query->whereHas('product', function ($query) use ($keyword, $category, $brand) {
            $query->where('user_id', Auth::user()->id)
                  ->where('published', 1);
            if ($keyword != null) {
                $query->where(function ($q) use ($keyword) {
                    $q->where('name', 'like', '%' . $keyword . '%')
                      ->orWhere('barcode', $keyword);
                });
            }
            if ($category != null) {
                $category_id = (explode('-', $category))[1];
                $query->where('category_id', $category_id);
            }
            if ($brand != null) {
                $query->where('brand_id', $brand);
            }
        });

        $product_stocks = $product_query->paginate(16);

        foreach ($product_stocks as $key => $product_stock) {
            $product_stock->name = $product_stock->product->getTranslation('name');
            if ($product_stock->variant != null) {
                $product_stock->name .= ' - ' . $product_stock->variant;
            }
            $product_stock->thumbnail_image = uploaded_asset($product_stock->product->thumbnail_img);
            $product_stock->price = single_price(home_discounted_base_price($product_stock->product->id));
            $product_stock->base_price = single_price($product_stock->price);
        }

        return $product_stocks;
    }

    public function addToCart(Request $request)
    {
        $stock = ProductStock::findOrFail($request->stock_id);
        $product = $stock->product;

        $cart = Session::get('pos.cart', []);

        $cart_item = null;
        $key = null;
        foreach ($cart as $k => $item) {
            if ($item['stock_id'] == $request->stock_id) {
                $cart_item = $item;
                $key = $k;
                break;
            }
        }

        if ($cart_item != null) {
            if ($stock->qty < $cart_item['quantity'] + 1) {
                return ['success' => 0, 'message' => translate('Insufficient stock!')];
            }
            $cart[$key]['quantity']++;
        } else {
            if ($stock->qty < 1) {
                return ['success' => 0, 'message' => translate('Insufficient stock!')];
            }

            $price = home_discounted_base_price($product->id);
            $tax = 0;
            foreach ($product->taxes as $product_tax) {
                if ($product_tax->tax_type == 'flat') {
                    $tax += $product_tax->tax;
                } else {
                    $tax += ($price * $product_tax->tax) / 100;
                }
            }

            $cart[] = [
                'stock_id' => $request->stock_id,
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $price,
                'tax' => $tax,
                'variant' => $stock->variant
            ];
        }

        Session::put('pos.cart', $cart);

        return [
            'success' => 1,
            'view' => view('backend.pos.cart')->render()
        ];
    }

    public function removeFromCart(Request $request)
    {
        $cart = Session::get('pos.cart', []);
        unset($cart[$request->key]);
        Session::put('pos.cart', $cart);

        return view('backend.pos.cart')->render();
    }

    public function updateQuantity(Request $request)
    {
        $cart = Session::get('pos.cart', []);
        $stock = ProductStock::findOrFail($cart[$request->key]['stock_id']);

        if ($stock->qty < $request->quantity) {
            return ['success' => 0, 'message' => translate('Insufficient stock!')];
        }

        $cart[$request->key]['quantity'] = $request->quantity;
        Session::put('pos.cart', $cart);

        return [
            'success' => 1,
            'view' => view('backend.pos.cart')->render()
        ];
    }

    public function setShipping(Request $request)
    {
        Session::put('pos.shipping', $request->shipping);
        return view('backend.pos.cart')->render();
    }

    public function setDiscount(Request $request)
    {
        Session::put('pos.discount', $request->discount);
        return view('backend.pos.cart')->render();
    }

    public function getShippingAddress(Request $request)
    {
        $user = User::find($request->id);
        if ($user) {
            $addresses = $user->addresses;
            return view('backend.pos.shipping_address', compact('addresses'));
        }
        return view('backend.pos.guest_shipping_address');
    }

    public function set_shipping_address(Request $request)
    {
        Session::put('pos.shipping_address', $request->all());
    }

    public function getOrderSummary(Request $request)
    {
        return view('backend.pos.order_summary');
    }

    public function order_place(Request $request)
    {
        $cart = Session::get('pos.cart', []);
        if (empty($cart)) {
            return ['success' => 0, 'message' => translate('Cart is empty!')];
        }

        $subtotal = 0;
        $tax = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
            $tax += $item['tax'] * $item['quantity'];
        }

        $shipping = Session::get('pos.shipping', 0);
        $discount = Session::get('pos.discount', 0);
        $total = $subtotal + $tax + $shipping - $discount;

        $combined_order = new CombinedOrder;
        $combined_order->user_id = $request->user_id;
        $combined_order->shipping_address = json_encode(Session::get('pos.shipping_address'));
        $combined_order->grand_total = $total;
        $combined_order->save();

        $order = new Order;
        $order->combined_order_id = $combined_order->id;
        $order->user_id = $request->user_id;
        $order->seller_id = Auth::user()->id;
        $order->shipping_address = $combined_order->shipping_address;
        $order->payment_type = $request->payment_type;
        $order->payment_status = ($request->payment_type == 'cash' || $request->payment_type == 'wallet') ? 'paid' : 'unpaid';
        $order->grand_total = $total;
        $order->date = strtotime('now');
        $order->save();

        foreach ($cart as $item) {
            $order_detail = new OrderDetail;
            $order_detail->order_id = $order->id;
            $order_detail->seller_id = Auth::user()->id;
            $order_detail->product_id = $item['product_id'];
            $order_detail->product_name = Product::find($item['product_id'])?->getTranslation('name');
            $order_detail->variation = $item['variant'];
            $order_detail->price = $item['price'];
            $order_detail->tax = $item['tax'];
            $order_detail->quantity = $item['quantity'];
            $order_detail->save();

            $stock = ProductStock::where('id', $item['stock_id'])->first();
            $stock->qty -= $item['quantity'];
            $stock->save();

            $product = $stock->product;
            $product->current_stock -= $item['quantity'];
            $product->num_of_sale += $item['quantity'];
            $product->save();
        }

        Session::forget('pos.cart');
        Session::forget('pos.shipping');
        Session::forget('pos.discount');
        Session::forget('pos.shipping_address');

        return [
            'success' => 1,
            'message' => translate('Order has been placed successfully'),
            'order_id' => $order->id
        ];
    }

    public function thermal_printer($order_id)
    {
        $order = Order::findOrFail($order_id);
        return view('backend.pos.thermal_printer', compact('order'));
    }
}
