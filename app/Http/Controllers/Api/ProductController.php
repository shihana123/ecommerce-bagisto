<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Webkul\Product\Repositories\ProductRepository;

class ProductController extends Controller
{
     protected $productRepository;

    public function __construct(ProductRepository $productRepository)
    {
        $this->productRepository = $productRepository;
    }

    public function index()
    {
        $products = $this->productRepository->all()->map(function ($product) {
            return [
                'id'       => $product->id,
                'name'     => $product->name,
                'price'    => $product->price,
                'category' => $product->categories->pluck('name')->first(),
            ];
        });

        return response()->json([
            'status' => true,
            'products' => $products,
        ]);
    }
}
