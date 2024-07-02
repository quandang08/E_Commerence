<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;

class ProductSubCategoryController extends Controller
{
    //lấy danh sách các subcategories (danh mục con) dựa trên category_id
    public function index(Request $request)
    {
        if (!empty($request->category_id)) {
            // Truy vấn bảng SubCategory để lấy các subcategories dựa trên category_id
            $subCategories = SubCategory::where('category_id', $request->category_id)
                ->orderBy('name', 'ASC')
                ->get();

             // Trả về JSON response với trạng thái true và danh sách subcategories
            return response()->json([
                'status' => true,
                'subcategories' => $subCategories
            ]);
        }else{
            return response()->json([
                'status' => true,
                'subcategories' => []
            ]);
        }
    }
}
