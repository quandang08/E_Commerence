<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class AdminLoginController extends Controller
{
    public function index()
    {
        return view('admin.login');
    }

    public function authenticate(Request $request)
    {
        // Kiểm tra dữ liệu đầu vào
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Nếu dữ liệu không hợp lệ, trả về trang đăng nhập với thông báo lỗi
        if ($validator->fails()) {
            return redirect()->route('admin.login')
                ->withErrors($validator)
                ->withInput($request->only('email'));
        }

        // Thông tin xác thực
        $credentials = $request->only('email', 'password');
        $remember = $request->filled('remember');

        // Thử đăng nhập với thông tin người dùng
        if (Auth::guard('admin')->attempt($credentials, $remember)) {
            $admin = Auth::guard('admin')->user();

            // Nếu đăng nhập thành công, kiểm tra vai trò và chuyển hướng đến trang dashboard của admin
            if ($admin->role == 2) {
                return redirect()->intended(route('admin.dashboard'));
            }

            // Nếu không phải admin, chuyển hướng đến trang đăng nhập
            Auth::guard('admin')->logout(); // Đăng xuất nếu không phải admin
            return redirect()->route('admin.login')
                ->withErrors(['email' => 'Bạn không có quyền truy cập.']);
        }

        // Nếu đăng nhập không thành công, hiển thị thông báo lỗi và đưa người dùng quay lại trang đăng nhập
        return redirect()->route('admin.login')
            ->withErrors(['email' => 'Thông tin đăng nhập không chính xác.'])
            ->withInput($request->only('email'));
    }
}
