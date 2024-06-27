<?php

namespace App\Http\Controllers\admin;

use App\Models\TempImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;


class TempImagesController extends Controller
{
    public function create(Request $request)
    {
        Log::info($request->all()); // Ghi lại dữ liệu request vào log

        $image = $request->file('image');

        if ($image) {
            $ext = $image->getClientOriginalExtension();
            $newName = time() . '.' . $ext;

            $tempImage = new TempImage();
            $tempImage->name = $newName;
            $tempImage->save();

            $image->move(public_path('temp'), $newName);

            return response()->json([
                'status' => true,
                'image_id' => $tempImage->id,
                'message' => 'Image uploaded successfully'
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No image uploaded'
            ]);
        }
    }
}
