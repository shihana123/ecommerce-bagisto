<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Webkul\Checkout\Facades\Cart;
use Webkul\Product\Repositories\ProductRepository;

class CartController extends Controller
{
    protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function addToCart(Request $request)
    {
        $data = $request->validate([
           'product_id' => 'required|integer|exists:products,id',
            'quantity'   => 'required|integer|min:1',
        ]);

        $product = $this->productRepository->find($data['product_id']);
        $cartItem = Cart::addProduct($product, ['quantity' => $data['quantity']]);

        return response()->json([
            'status' => true,
            'message' => 'Product added to cart',
            'item' => $cartItem,
        ]);
    }
}
