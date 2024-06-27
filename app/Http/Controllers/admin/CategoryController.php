<?php

namespace App\Http\Controllers\admin;

use App\Models\Category;
use App\Models\TempImage;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;



class CategoryController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách các danh mục mới nhất và phân trang, mỗi trang 10 mục
        $categories = Category::latest();
        if (!empty($request->get('keyword'))) {
            $categories = $categories->where('name', 'like', '%' . $request->get('keyword') . '%');
        }
        $categories = $categories->paginate(10);

        // Trả về view 'admin.category.list' với dữ liệu danh mục
        return view('admin.category.list', compact('categories'));
    }

    public function create()
    {
        return view('admin.category.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories',
        ]);

        if ($validator->passes()) {
            $category = new Category();
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            // Save Image Here
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id . '.' . $ext;
                $sourcePath = public_path() . '/temp/' . $tempImage->name;
                $destinationPath = public_path() . '/uploads/category/' . $newImageName;

                // Copy original image to destination
                File::copy($sourcePath, $destinationPath);

                // Generate Thumbnail
                $thumbnailPath = public_path() . '/uploads/category/thumb/' . $newImageName; // Corrected path
                $img = Image::make($sourcePath); // Use Intervention Image facade here
                // $img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($thumbnailPath);

                $category->image = $newImageName;
                $category->save();
            }

            session()->flash('success', 'Category created successfully!');

            return response()->json([
                'status' => true,
                'message' => 'Category added successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }


    public function edit($categoryID, Request $request)
    {
        $category = Category::find($categoryID);
        if (empty($category)) {
            return redirect()->route('categories.index');
        }

        return view('admin.category.edit', compact('category'));
    }

    public function update($categoryID, Request $request)
    {
        $category = Category::find($categoryID);
        if (empty($category)) {
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'slug' => 'required|unique:categories,slug,' . $category->id . ',id',
        ]);

        if ($validator->passes()) {
            // Update existing category instead of creating a new instance
            $category->name = $request->name;
            $category->slug = $request->slug;
            $category->status = $request->status;
            $category->save();

            $oldImage = $category->image;

            // Save Image Here
            if (!empty($request->image_id)) {
                $tempImage = TempImage::find($request->image_id);
                $extArray = explode('.', $tempImage->name);
                $ext = last($extArray);

                $newImageName = $category->id . '-' . time() . '.' . $ext;
                $sourcePath = public_path() . '/temp/' . $tempImage->name;
                $destinationPath = public_path() . '/uploads/category/' . $newImageName;

                // Copy original image to destination
                File::copy($sourcePath, $destinationPath);

                // Generate Thumbnail
                $thumbnailPath = public_path() . '/uploads/category/thumb/' . $newImageName; // Corrected path
                $img = Image::make($sourcePath); // Use Intervention Image facade here
                // $img->resize(450, 600);
                $img->fit(450, 600, function ($constraint) {
                    $constraint->upsize();
                });
                $img->save($thumbnailPath);

                $category->image = $newImageName;
                $category->save();

                // Delete Old Image Here
                if ($oldImage) {
                    File::delete(public_path() . '/uploads/category/thumb/' . $oldImage);
                    File::delete(public_path() . '/uploads/category/' . $oldImage);
                }
            }

            session()->flash('success', 'Category updated successfully!');

            return response()->json([
                'status' => true,
                'message' => 'Category updated successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ]);
        }
    }


    public function destroy($categoryID, Request $request)
    {
        $category = Category::find($categoryID);
        if (empty($category)) {
            return response()->json([
                'status' => false,
                'notFound' => true,
                'message' => 'Category not found'
            ]);
        }

        // Delete images
        File::delete(public_path() . '/uploads/category/thumb/' . $category->image);
        File::delete(public_path() . '/uploads/category/' . $category->image);

        // Delete the category
        $category->delete();

        session()->flash('success', 'Category deleted successfully!');

        return response()->json([
            'status' => true,
            'message' => 'Category deleted successfully',
        ]);
    }
}
