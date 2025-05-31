<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;

class CheckoutController extends Controller
{
    protected $cartRepository;

    public function __construct(CartRepository $cartRepository)
    {
        $this->cartRepository = $cartRepository;
    }

    public function checkout(Request $request)
    {
        $cart = Cart::getCart();
        if (!$cart || !$cart->items || $cart->items->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'Cart is empty',
        ]);
    }
        return response()->json([
        'status' => true,
        'message' => 'Cart retrieved successfully',
        'cart' => [
            'id' => $cart->id,
            'items' => $cart->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'product_id' => $item->product_id,
                    'name' => $item->name,
                    'quantity' => $item->quantity,
                    'price' => $item->price,
                    'total' => $item->total,
                ];
            }),
            'grand_total' => $cart->grand_total,
        ],
    ]);
    }
}
