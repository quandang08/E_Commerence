<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SubCategory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SubCategoryController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách các danh mục mới nhất và phân trang, mỗi trang 10 mục
        $subCategories = SubCategory::select('sub_categories.*', 'categories.name as categoryName')
            ->latest('id')
            ->leftJoin('categories', 'categories.id', 'sub_categories.category_id');

        if (!empty($request->get('keyword'))) {
            $subCategories = $subCategories->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $subCategories = $subCategories->paginate(10);

        // Trả về view 'admin.category.list' với dữ liệu danh mục
        return view('admin.sub_category.list', compact('subCategories'));
    }

    public function create()
    {
        // Execute the query to get categories
        $categories = Category::orderBy('name', 'ASC')->get();

        // Pass the categories to the view
        return view('admin.sub_category.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories',
            'category' => 'required',
            'status' => 'required|boolean'
        ]);

        if ($validator->passes()) {

            $subCategory = new SubCategory();
            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            session()->flash('success', 'Sub Category created successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Sub Category created successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function edit($id)
    {
        $subCategory = SubCategory::find($id);
        if (empty($subCategory)) {
            session()->flash('error', 'Record not found.');
            return redirect()->route('sub-categories.index');
        }

        $categories = Category::orderBy('name', 'ASC')->get();
        $data['categories'] = $categories;
        $data['subCategory'] = $subCategory;
        return view('admin.sub_category.edit', $data);
    }


    public function update($id, Request $request)
    {
        $subCategory = SubCategory::find($id);

        if (empty($subCategory)) {
            session()->flash('error', 'Record not found.');
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Record not found.'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:sub_categories,slug,' . $subCategory->id . ',id',
            'category' => 'required',
            'status' => 'required'
        ]);

        if ($validator->passes()) {

            $subCategory->name = $request->name;
            $subCategory->slug = $request->slug;
            $subCategory->status = $request->status;
            $subCategory->category_id = $request->category;
            $subCategory->save();

            session()->flash('success', 'Sub Category updated successfully.');

            return response()->json([
                'status' => true,
                'message' => 'Sub Category updated successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }

    public function destroy($id, Request $request)
    {
        $subCategory = SubCategory::find($id);
        if (empty($subCategory)) {
            session()->flash('error', 'Record not found.');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }

        $subCategory->delete();

        session()->flash('success', 'Sub Category deleted successfully.');

        return response()->json([
            'status' => true,
            'message' => 'Sub Category deleted successfully.'
        ]);
    }
}
