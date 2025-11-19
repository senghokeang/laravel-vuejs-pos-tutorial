<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCategory;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProductController extends Controller
{
    public function list(Request $request)
    {
        // get param value
        $name = $request->name;
        $product_category_id = $request->product_category_id ?? 0;
        $sortBy = $request->sortBy ?? 'created_at';
        $orderBy = $request->orderBy ?? 'desc';
        try {
            // product list
            $data = Product::join('product_categories', 'product_categories.id', '=', 'products.product_category_id')
                ->when($name, function ($query) use ($name) {
                    $query->where('products.name', 'like', '%' . $name . '%');
                })
                ->when($product_category_id > 0, function ($query) use ($product_category_id) {
                    $query->where('products.product_category_id', '=', $product_category_id);
                })
                ->select('products.id', 'products.name', 'products.image', 'products.unit_price', 'products.created_at', 'product_categories.name AS category_name', 'products.order')
                ->orderBy($sortBy, $orderBy)
                ->paginate(50);

            $response['success'] = true;
            $response['data'] = $data;
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }

        return response()->json($response);
    }


    public function save(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|unique:tables,name,' . $request->id,
                'image' => 'nullable|image|mimes:png,jpg,jpeg|max:1024',
                'product_category_id' => 'required',
                'unit_price' => 'required|numeric',
                'order' => 'nullable|numeric',
            ],
            [],
            [
                'product_category_id' => 'product category',
            ]
        );
        if ($validator->fails()) {
            return response()->json(
                [
                    'success' => false,
                    'errors' => $validator->errors()
                ]
            );
        }
        try {
            // DB::beginTransaction();
            if ($request->id > 0) {
                $data = Product::find($request->id);
            } else {
                $data = new Product();
                $data->created_by_id = $request->user()->id;
            }

            $data->updated_by_id = $request->user()->id;
            $data->name = $request->name;
            $data->product_category_id = $request->product_category_id;
            $data->unit_price = $request->unit_price;
            $data->name = $request->name;
            $data->order = $request->order;

            // delete uploaded file
            if ($request->is_deleted_image == 1 && $request->id > 0) {
                if (Storage::disk('public')->exists($data->image)) {
                    Storage::disk('public')->delete($data->image);
                }
                $data->image = '';
            }
            // upload file
            else if ($request->hasFile('image')) {
                if ($data->image && Storage::disk('public')->exists($data->image)) {
                    Storage::disk('public')->delete($data->image);
                }
                $data->image = Storage::disk('public')->put('product', $request->image);
            }

            $data->save();
            $response['success'] = true;
            $response['data'] = null;
            // DB::commit();
        } catch (Exception $ex) {
            abort($ex->getCode(), $ex->getMessage());
        }
        return response()->json($response);
    }

    public function edit(Request $request)
    {
        return response()->json(Product::select('id', 'name', 'product_category_id', 'unit_price', 'image', 'order')->findOrFail($request->id));
    }

    public function delete(Request $request)
    {
        $data = Product::findOrFail($request->id);
        $data->deleted_id = $request->user()->id;
        $data->delete();
        // delete uploaded file
        if ($data->image && Storage::disk('public')->exists($data->image)) {
            Storage::disk('public')->delete($data->image);
        }
        return response()->json();
    }

    public function categoryList()
    {
        return response()->json(ProductCategory::select('id', 'name')->orderBy('name')->get());
    }
}
