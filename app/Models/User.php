<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function gravatar($size = '100')
    {
        $hash = md5(strtolower(trim($this->attributes['email'])));
        return "http://www.gravatar.com/avatar/$hash?s=$size";
        // 1. 为 gravatar 方法传递的参数 size 指定了默认值 100；
        // 2. 通过 $this->attributes['email'] 获取到用户的邮箱；
        // 3. 使用 trim 方法剔除邮箱的前后空白内容；
        // 4. 用 strtolower 方法将邮箱转换为小写；
        // 5. 将小写的邮箱使用 md5 方法进行转码；
        // 6. 将转码后的邮箱与链接、尺寸拼接成完整的 URL 并返回；
    }
    // boot 方法会在用户模型类完成初始化之后进行加载，因此我们对事件的监听需要放在该方法中。
    public static function boot()
    {
        parent::boot();
        static::creating(function ($user){
            $user->activation_token = Str::random(30);
        }) ;
    }

    public function statuses()
    {
        return $this->hasMany(Status::class); //需要注意的一点是，由于一个用户拥有多条微博，因此在用户模型中我们使用了微博动态的复数形式statuses\来作为定义的函数名。
    }

    // public function feed()
    // {
    // return $this->statuses()
    //             ->orderBy('created_at', 'desc');
    // }
    public function feed() //动态流显示关注的人的动态
    {
    $user_ids = $this->followings->pluck('id')->toArray(); // 通过 followings 方法取出所有关注用户的信息，再借助 pluck 方法将 id 进行分离并赋值给 user_ids ；

    array_push($user_ids, $this->id); //. 将当前用户的 id 加入到 user_ids 数组中；
    return Status::whereIn('user_id', $user_ids) //使用 Laravel 提供的 whereIn 方法取出所有用户的微博动态并进行倒序排序；
                    ->with('user') //我们使用了 Eloquent 关联的 with 方法，预加载避免了 N+1 查找的问题 ，大大提高了查询效率。 N+1 问题 的例子可以阅读此文档 。

                    ->orderBy('created_at', 'desc');
    }
    // 在 Laravel 中会默认将两个关联模型的名称进行合并，并按照字母排序，因此我们生成的关联关系表名称
    // 会是 user_user 。我们也可以自定义生成的名称，把关联表名改为 followers 。
    public function followers()
    {
        return $this->belongsToMany(User::class, 'followers', 'user_id', 'follower_id');
    }

    public function followings()
    {
        return $this->belongsToMany(User::class, 'followers', 'follower_id', 'user_id');
    }
    // belongsToMany 方法的第三个参数 user_id 是定义在关联中的模型外键名，而第四个参数follower_id 则是要合并的模型外键名。

    public function follow($user_ids)
    {
        if ( ! is_array($user_ids)) {
            $user_ids = compact('user_ids');
        }
        $this->followings()->sync($user_ids,false);
    }

    public function unfollow($user_ids)
    {
        if ( ! is_array($user_ids) ) {
            $user_ids = compact('user_ids');
        }
        $this -> followings()->datach($user_ids);
    }

    public function isFollowing($user_id)
    {
        return $this->followings->contains($user_id);
    }
}
