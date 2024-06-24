<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    public function index()
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        if (Auth::guard('admin')->check()) {
            return view('admin.dashboard');
            
            // $admin = Auth::guard('admin')->user();
            // return 'Welcome ' . $admin->name . ' <a href="' . route('admin.logout') . '">Logout</a>';
        }

        // Nếu người dùng chưa đăng nhập, chuyển hướng về trang đăng nhập
        return redirect()->route('admin.login');
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login');
    }
}
