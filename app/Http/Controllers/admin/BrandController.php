<?php

namespace App\Http\Controllers\admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Facades\Validator;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        // Khởi tạo truy vấn ban đầu
        $brands = Brand::query();

        // Nếu có từ khóa tìm kiếm, thêm điều kiện vào truy vấn
        if ($request->get('keyword')) {
            $keyword = $request->get('keyword');
            $brands = $brands->where('name', 'like', '%' . $keyword . '%');
        }

        // Sắp xếp và phân trang kết quả sau khi đã thêm điều kiện tìm kiếm
        $brands = $brands->latest('id')->paginate(10);

        // Trả về view với dữ liệu thương hiệu
        return view('admin.brand.list', compact('brands'));
    }



    public function create()
    {
        return view('admin.brand.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands',
        ]);

        if ($validator->passes()) {

            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => 'Brand added successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        };
    }

    public function edit($id, Request $request)
    {
        $brand = Brand::find($id);

        if (empty($brand)) {
            session()->flash('error', 'Record not found!');
            return redirect()->route('brand.index');
        }

        $data['brand'] = $brand;
        return view('admin.brand.edit', $data);
    }

    public function update($id, Request $request)
    {
        $brand = Brand::find($id);

        if (empty($brand)) {
            session()->flash('error', 'Record not found!');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
            // return redirect()->route('brand.index');
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:brands,slug,' . $brand->id . ',id',
        ]);

        if ($validator->passes()) {

            $brand = new Brand();
            $brand->name = $request->name;
            $brand->slug = $request->slug;
            $brand->status = $request->status;
            $brand->save();

            return response()->json([
                'status' => true,
                'message' => 'Brand updated successfully.'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        };
    }

    public function destroy($id, Request $request)
    {
        $brand = Brand::find($id);
        if (empty($brand)) {
            session()->flash('error', 'Record not found.');
            return response()->json([
                'status' => false,
                'notFound' => true
            ]);
        }

        $brand->delete();

        session()->flash('success', 'Sub Category deleted successfully.');

        return response()->json([
            'status' => true,
            'message' => 'Sub Category deleted successfully.'
        ]);
    }
}