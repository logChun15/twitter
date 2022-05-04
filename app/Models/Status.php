<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Status extends Model
{
    use HasFactory;
    protected $fillable = ['content'];
    // 我们看到其中的关键信息 MassAssignmentException - 批量赋值异常，这是因为我们未在微博模型中
    // 定义 fillable 属性，来指定在微博模型中可以进行正常更新的字段，Laravel 在尝试保护。解决的办
    // 法很简单，在微博模型的 fillable 属性中允许更新微博的 content 字段即可。
    public function user()
    {
        return $this->belongsTo(User::class);  //记得belongs要带有s，因为这个原因找了半天
    }

    protected $model = Status::class;

    public function definition()
    {
        $date_time = $this->faker->date . ' ' . $this->faker->time;
        return [
            'user_id'    => $this->faker->randomElement(['1','2','3']),
            'content'    => $this->faker->text(),
            'created_at' => $date_time,
            'updated_at' => $date_time,
        ];
    }
}
