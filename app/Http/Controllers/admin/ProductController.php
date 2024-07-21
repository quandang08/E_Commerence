<?php

namespace App\Http\Controllers\admin;

use App\Models\Brand;
use App\Models\Product;
use App\Models\Category;
use App\Models\TempImage;
use App\Models\SubCategory;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Faker\Provider\Lorem;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Facades\Validator;



class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::latest('id')->with('product_images');

        if ($request->get('keyword') != "") {
            $products = $products->where('title', 'like', '%' . $request->keyword . '%');
        }
        $products = $products->paginate();
        $data['products'] = $products;
        return view('admin.product.list', $data);
    }

    public function create()
    {
        $data = [];
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['brands'] = $brands;

        return view('admin.product.create', $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'title' => 'required',
            'slug'  => 'required|unique:products',
            'price' => 'required|numeric',
            'sku' => 'required|unique:products',
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes') {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $product = new Product;
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->shipping_returns = $request->shipping_returns;
            $product->short_description = $request->short_description;
            $product->save();

            //Save Gallery Pics
            if (!empty($request->image_array)) {
                foreach ($request->image_array as $temp_image_id) {
                    $tempImageInfo = TempImage::find($temp_image_id);
                    $extArray = explode('.', $tempImageInfo->name);
                    $ext = last($extArray);

                    $productImage = new ProductImage();
                    $productImage->product_id = $product->id;
                    $productImage->image = 'NULL';
                    $productImage->save();

                    $imageName = $product->id . '-' . $productImage->id . '-' . time() . '.' . $ext;
                    $productImage->image = $imageName;
                    $productImage->save();

                    $sourcePath = public_path() . '/temp/' . $tempImageInfo->name;
                    $largeDestPath = public_path() . '/uploads/product/large/' . $imageName;
                    $smallDestPath = public_path() . '/uploads/product/small/' . $imageName;

                    $image = Image::make($sourcePath);

                    $image->resize(1400, null, function ($constraint) {
                        $constraint->aspectRatio();
                    })->save($largeDestPath);

                    $image->fit(300, 300)->save($smallDestPath);
                }
            }

            session()->flash('success', 'Product created successfully!');
            return response()->json([
                'message' => 'Product created successfully!'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($id, Request $request)
    {
        $product = Product::find($id);
        if (empty($product)) {
            return redirect()->route('products.index')->with('error', 'Product not found!');
        }

        // Fetch product image
        $productImages = ProductImage::where('product_id', $id)->get();

        // Fetch subcategories based on product's category_id
        $subCategories = SubCategory::where('category_id', $product->category_id)->get();

        // Fetch all categories and brands
        $categories = Category::orderBy('name', 'ASC')->get();
        $brands = Brand::orderBy('name', 'ASC')->get();

        // Prepare data to pass to view
        $data = [
            'product' => $product,
            'productImages' => $productImages,
            'subCategories' => $subCategories,
            'categories' => $categories,
            'brands' => $brands,
        ];

        return view('admin.product.edit', $data);
    }


    public function update($id, Request $request)
    {
        $product = Product::find($id);

        $rules = [
            'title' => 'required',
            'slug' => 'required|unique:products,slug,' . $product->id,
            'price' => 'required|numeric',
            'sku' => 'required|unique:products,sku,' . $product->id,
            'track_qty' => 'required|in:Yes,No',
            'category' => 'required|numeric',
            'is_featured' => 'required|in:Yes,No',
        ];

        if (!empty($request->track_qty) && $request->track_qty == 'Yes') {
            $rules['qty'] = 'required|numeric';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->passes()) {
            $product->title = $request->title;
            $product->slug = $request->slug;
            $product->description = $request->description;
            $product->price = $request->price;
            $product->compare_price = $request->compare_price;
            $product->sku = $request->sku;
            $product->barcode = $request->barcode;
            $product->track_qty = $request->track_qty;
            $product->qty = $request->qty;
            $product->status = $request->status;
            $product->category_id = $request->category;
            $product->sub_category_id = $request->sub_category;
            $product->brand_id = $request->brand;
            $product->is_featured = $request->is_featured;
            $product->shipping_returns = $request->shipping_returns;
            $product->short_description = $request->short_description;

            $product->save();

            //Save Gallery Pics

            session()->flash('success', 'Product updated successfully!');
            return response()->json([
                'message' => 'Product updated successfully!'
            ], 200);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id, Request $request){
        $product = Product::find($id);

        if(empty($product)){
            session()->flash('error','Product not Found!');
            return response()->json([
                'status' => false,
                'notFound' => 'true'
            ]);
        }
        $productImages =ProductImage::where('product_id',$id)->get();

        if(!empty($productImages)) {
            foreach($productImages as $productImage){
                        File::delete(public_path('uploads/products/large/'.$productImage->image));
                        File::delete(public_path('uploads/products/small/'.$productImage->image));
            }
            ProductImage::where('product_id',$id)->delete();
        }
        $product->delete();
        session()->flash('success','Product deleted successfully!');

        return response()->json([
            'status' => true,
            'message' => 'Product deleted successfully!'
        ]);
    }
}
