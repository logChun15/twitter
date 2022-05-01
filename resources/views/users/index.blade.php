@extends('layouts.default')
@section('title', '所有用户')

@section('content')
<div class="offset-md-2 col-md-8">
  <h2 class="mb-4 text-center">所有用户</h2>
  <div class="list-group list-group-flush">
    @foreach ($users as $user)
    @include('users._user')     
    @endforeach

  </div>

  <div class="mt-3">
      {!! $users->render() !!}  
  </div>
{{-- laravel8默认使用taiwind样式，需要转换为bootstrap在app\Provider\appServicePeovider中更改 --}}
{{-- 
    {{ $users->links() }}也可以用来渲染页面视图
由render方法生成的html代码会默认使用boostrap框架样式，渲染出来的视图也统一会
带上？page参数来设置指定页数连接，{!!  !!} 而不是{{  }} 用来渲染html代码防止转义 

--}}
</div>
@stop