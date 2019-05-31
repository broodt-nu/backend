<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Helpers\HttpStatusCodes;
use App\Validators\ValidatesProductsRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends Controller {
    use ValidatesProductsRequests;

    /**
     * Show all products
     *
     * @return JsonResponse
     */
    public function index()
    {
        $products = Product::all();
        return response()->json($products, HttpStatusCodes::SUCCESS_OK);
    }

    /**
     * View product (multiple can be requested with comma's)
     *
     * @param string
     *
     * @return JsonResponse
     */
    public function show($id) {
        $ids = array_map('intval', explode(',', $id));

        return response()->json(
            Product::findOrFail($ids),
            HttpStatusCodes::SUCCESS_OK
        );
    }

    public function create(Request $request, $id) {
        $this->validateCreate($request);

        $product = Product::create([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'img_url' => $request->get('img_url'),
            'price' => $request->get('price'),
            'tags' => $request->get('tags'),
            'recommended_addons' => $request->get('recommended_addons'),
        ]);

        return response()->json(
            $product,
            HttpStatusCodes::SUCCESS_CREATED
        );
    }

    public function update(Request $request, $id) {
        $this->validateUpdate($request);

        $product = Product::findOrFail($id);

        $product->update([
            'name' => $request->get('name'),
            'description' => $request->get('description'),
            'img_url' => $request->get('img_url'),
            'price' => $request->get('price'),
            'tags' => $request->get('tags'),
            'recommended_addons' => $request->get('recommended_addons'),
        ]);

        $product->save();

        return response()->json(
            $product,
            HttpStatusCodes::SUCCESS_OK
        );
    }

    /**
     * Delete product
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function delete($id) {
        // TODO: add access_level system

        Product::findOrFail($id)->delete();

        return response()->json(
            null,
            HttpStatusCodes::SUCCESS_NO_CONTENT
        );
    }
}
