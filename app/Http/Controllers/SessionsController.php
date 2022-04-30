<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionsController extends Controller
{


    public function __construct()
    {
        $this->middleware('guest', [
        'only' => ['create']
        ]);
    }


    public function create()
    {
        return view('sessions.create');
    }

    public function store(Request $request)
    {
        $credentials = $this -> validate($request,[
            'email' => 'required|email|max:225',
            'password' => 'required'
        ]);
        //attempt第一个方法会接收一个数组来作为第一个参数，该参数提供的值将用于寻找数据库中的用户数据。先对第一个参数进行匹配，如果有再对第二个参数进行校对，都匹配正确返回true。

        if (Auth::attempt($credentials,$request->has('remember'))) {
            // 登录成功后的相关操作
            session()->flash('success', '欢迎回来！');
            $fallback = route('users.show',Auth::user());
            return redirect()->intended($fallback);
            } else {
            // 登录失败后的相关操作
            session()->flash('danger', '很抱歉，您的邮箱和密码不匹配');
            return redirect()->back()->withInput();
        }
    }

    public function destroy()
    {
    Auth::logout();
    session()->flash('success', '您已成功退出！');
    return redirect('login');
    }
}
