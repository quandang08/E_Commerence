<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request, $categorySlug = null , $subCategorySlug = null){
        $categorySelected = '';
        $subCategorySelected = '';
        $brandsArray = [];

        // Lấy tất cả các danh mục, thương hiệu và sắp xếp theo tên (ASC), chỉ lấy những cái có status là 1
        $categories = Category::orderBy('name','ASC')->with('sub_category')->where('status',1)->get();
        $brands = Brand::orderBy('name','ASC')->where('status',1)->get();

        // Lấy tất cả các sản phẩm có status là 1
        $products = Product::where('status',1);

        // Áp dụng các bộ lọc
        if(!empty($categorySlug)) {
            // Nếu có categorySlug, lấy danh mục tương ứng và lọc sản phẩm theo danh mục đó
            $category = Category::where('slug',$categorySlug)->first();
            $products = $products->where('category_id', $category->id);
            $categorySelected = $category->id;
        }

        if(!empty($subCategorySlug)) {
            // Nếu có subCategorySlug, lấy danh mục con tương ứng và lọc sản phẩm theo danh mục con đó
            $subCategory = SubCategory::where('slug',$subCategorySlug)->first();
            $products = $products->where('sub_category_id', $subCategory->id);
            $subCategorySelected = $subCategory->id;
        }

        if(!empty($request->get('brand'))) {
            // Nếu có thương hiệu được chọn, lọc sản phẩm theo thương hiệu đó
            $brandsArray = explode(',',$request->get('brand'));
            $products = $products->whereIn('brand_id',$brandsArray);
        }

        if($request->get('price_max') != '' && $request->get('price_min') != ''){
            // Nếu có giá trị min và max của giá được chọn, lọc sản phẩm theo khoảng giá đó
            if($request->get('price_max') == 1000){
                $products = $products->whereBetween('price',[intval($request->get('price_min')),1000000]);
            }else{
                $products = $products->whereBetween('price',[intval($request->get('price_min')),intval($request->get('price_max'))]);
            }
        }

        if($request->get('sort') != ''){
            // Nếu có lựa chọn sắp xếp, áp dụng sắp xếp tương ứng
            if($request->get('sort') == 'latest'){
                $products = $products->orderBy('id','DESC');
            }else if($request->get('sort') == 'price_asc'){
                $products = $products->orderBy('price','ASC');
            }else{
                $products = $products->orderBy('price','DESC');
            }
        }else{
             // Nếu không có lựa chọn sắp xếp, sắp xếp sản phẩm theo id giảm dần (mới nhất)
            $products = $products->orderBy('id','DESC');
        }

        //Phân trang kết quả, mỗi trang hiển thị 6 sản phẩm
        $products = $products->paginate(6);

        // Đưa các biến cần thiết vào mảng data để truyền vào view
        $data['categories'] = $categories;
        $data['brands'] = $brands;
        $data['products'] = $products;
        $data['categorySelected'] = $categorySelected;
        $data['subCategorySelected'] = $subCategorySelected;
        $data['brandsArray'] = $brandsArray;
        $data['priceMax'] = ($request->get('price_max') == '') ? 1000 : intval($request->get('price_max'));
        $data['priceMin'] = ($request->get('price_min') == '') ? 0 : intval($request->get('price_min'));
        $data['sort'] = $request->get('sort');

        // Trả về view 'front.shop' với các dữ liệu đã được xử lý
        return view('front.shop', $data);
    }

    public function product($slug){
        //$slug
        $product = Product::where('slug',$slug)->with('product_images')->first();
        if($product == null){
            abort(404);
        }
        $data['product'] = $product;
        return view('front.product', $data);
    }

}

