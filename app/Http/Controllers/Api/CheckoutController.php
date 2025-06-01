<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Webkul\Checkout\Facades\Cart;
use Webkul\Checkout\Repositories\CartRepository;
use Webkul\Checkout\Repositories\CartAddressRepository;
use Webkul\Sales\Repositories\OrderRepository;
use Illuminate\Support\Facades\DB;


class CheckoutController extends Controller
{
    protected $cartRepository;
    protected $orderRepository;
    protected $cartAddressRepository;

    public function __construct(CartRepository $cartRepository, OrderRepository $orderRepository, CartAddressRepository $cartAddressRepository)
    {
        $this->cartRepository = $cartRepository;
        $this->orderRepository = $orderRepository;
        $this->cartAddressRepository = $cartAddressRepository;
    }

    public function checkout(Request $request)
{
    ini_set('xdebug.max_nesting_level', 512);
    $validated = $request->validate([
        'billing_address.first_name' => 'required|string',
        'billing_address.last_name' => 'required|string',
        'billing_address.email' => 'required|email',
        'billing_address.address1' => 'required|array',
        'billing_address.city' => 'required|string',
        'billing_address.state' => 'required|string',
        'billing_address.country' => 'required|string',
        'billing_address.postcode' => 'required|string',
        'billing_address.phone' => 'required|string',

        'shipping_address.first_name' => 'required|string',
        'shipping_address.last_name' => 'required|string',
        'shipping_address.email' => 'required|email',
        'shipping_address.address1' => 'required|array',
        'shipping_address.city' => 'required|string',
        'shipping_address.state' => 'required|string',
        'shipping_address.country' => 'required|string',
        'shipping_address.postcode' => 'required|string',
        'shipping_address.phone' => 'required|string',

        'shipping_method' => 'required|string',
        'payment_method' => 'required|string',
        'payment_method_title' => 'required|string',
    ]);

    $cart = Cart::getCart();

    if (! $cart || ! $cart->items->count()) {
        return response()->json(['status' => false, 'message' => 'Cart is empty'], 400);
    }

    // Save billing address
    $this->cartAddressRepository->create(array_merge([
        'cart_id' => $cart->id,
        'address_type' => 'billing',
    ], $validated['billing_address']));

    // Save shipping address
    $this->cartAddressRepository->create(array_merge([
        'cart_id' => $cart->id,
        'address_type' => 'shipping',
    ], $validated['shipping_address']));

    $cart->shipping_method = $validated['shipping_method'];
    $cart->customer_email = $validated['billing_address']['email'];
    $cart->customer_first_name = $validated['billing_address']['first_name'];
    $cart->customer_last_name = $validated['billing_address']['last_name'];
    $cart->save();
    
    DB::table('cart_payment')->updateOrInsert(
        ['cart_id' => $cart->id],  // condition to find existing
        [
            'method' => $validated['payment_method'],
            'method_title' => $validated['payment_method_title'] ?? '',
            'updated_at' => now(),
            'created_at' => now(),  // only used if inserting
        ]
    );

    
    // Manually insert order items if needed
    // return $cart;

    $order = $this->orderRepository->createOrderIfNotThenRetry($cart->toArray());

    foreach ($cart->items as $item) {
        $itemData = $item->toArray();
        $itemData['order_id'] = $order->id;

        $orderItem = app(\Webkul\Sales\Repositories\OrderItemRepository::class)->create($itemData);

        if (!empty($itemData['children'])) {
            foreach ($itemData['children'] as $child) {
                $child['order_id'] = $order->id;
                $child['parent_id'] = $orderItem->id;
                app(\Webkul\Sales\Repositories\OrderItemRepository::class)->create($child);
            }
        }
    }

    if (! $order) {
        return response()->json(['status' => false, 'message' => 'Failed to place order'], 500);
    }

    return response()->json([
        'status' => true,
        'message' => 'Order placed successfully',
        'order' => $order,
    ]);


}

}
