<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Symfony\Contracts\Service\Attribute\Required;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class UsersController extends Controller
{

        public function __construct()
    {
        $this->middleware('auth', [
        'except' => ['show', 'create', 'store','index','confirmEmail']
        ]);
    }
    public function create()
    {
        return view('users.create');
    }


    public function show(User $user)
    {
        $statuses = $user->statuses()
                            ->orderBy('created_at','desc')
                            ->paginate(10);
        return view('users.show',compact('user','statuses'));
    }

    public function store(Request $request)
    {
        $this->validate($request,[
            'name' => 'required|max:50',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|confirmed|min:6'
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);
        $this->sendEmailConfirmationTo($user);
        session()->flash('success', '验证邮件已发送到你的注册邮箱上，请注意查收。');
        return redirect('/');
        // 现在的注册功能已经可以正常使用，但我们希望在用户注册成功后能够自动登录，这样的应用用户体验会
        // 更棒。在 Laravel 中，如果要让一个已认证通过的用户实例进行登录，可以使用以下方法 Auth::login($user);：
        // Auth::login($user);
        // session()->flash('success','欢迎，您将在这里开启一段新的旅程');
        // return redirect()->route('users.show',[$user]);
    }

    public function edit(User $user)
    {
    $this->authorize('update', $user);
    return view('users.edit', compact('user'));
    }

    public function update(User $user, Request $request)
    {
        $this->authorize('update', $user);
        $this->validate($request, [
            'name' => 'required|max:50',
            'password' => 'nullable|confirmed|min:6'
        ]);

        $data = [];
        $data['name'] = $request->name;
        if ($request->password) {
            $data['password'] = bcrypt($request->password);
        }
        $user->update($data);

        session()->flash('success', '个人资料更新成功！');

        return redirect()->route('users.show', $user);
    }

    public function index()
    {
        $users = User::paginate(10);
        return view('users.index',compact('users'));
    }

    public function destroy(User $user)
    {
        $this->authorize('destroy', $user);
        $user->delete();
        session()->flash('success', '成功删除用户！');
        return back();
    }

    protected function sendEmailConfirmationTo($user)
    {
        $view = 'emails.confirm';
        $data = compact('user');
        $from = 'summer@example.com';
        $name = 'Summer';
        $to = $user->email;
        $subject = "感谢注册 Weibo 应用！请确认你的邮箱。";

        Mail::send($view, $data, function ($message) use ($from, $name, $to, $subject) {
            $message->from($from, $name)->to($to)->subject($subject);
        });
    }

    public function confirmEmail($token)
    {
    $user = User::where('activation_token', $token)->firstOrFail();  //Eloquent 的 where 方法接收两个参数，第一个参数为要进行查找的字段名称，第二个参数为对应的值，
    //查询结果返回的是一个数组，因此我们需要使用 firstOrFail 方法来取出第一个用户，在查询不到指定用户时将返回一个 404 响应。
    $user->activated = true;
    $user->activation_token = null;
    $user->save();
    Auth::login($user);
    session()->flash('success', '恭喜你，激活成功！');
    return redirect()->route('users.show', [$user]);
    }

    public function followings(User $user)
    {
    $users = $user->followings()->paginate(30);
    $title = $user->name . '关注的人';
    return view('users.show_follow', compact('users', 'title'));
    }

    public function followers(User $user)
    {
    $users = $user->followers()->paginate(30);
    $title = $user->name . '的粉丝';
    return view('users.show_follow', compact('users', 'title'));
    }


}
