@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.users.show', $user) }}

      <h1>顧客詳細（個人）</h1>

      <div>
        <a class="nav-link" href="{{ route('admin.model_logs.index', ['model' => 'App\Models\User', 'model_id' => $user->id]) }}">
          <i class="far fa-file-alt"></i>
          操作ログ
        </a>
      </div>

      <table class="table table-striped w-75">
        <tbody>
          <tr>
            <th>#ユーザーID</th>
            <td>{{ $user->id }}</td>
          </tr>
          <tr>
            <th>従属会社</th>
            <td><a href="{{ route('admin.agencies.show', $user->agency->id) }}" target="_blank">{{ $user->agency->company_name }}</a></td>
          </tr>
          <tr>
            <th>お客様番号</th>
            <td>{{ $user->user_number }}</td>
          </tr>
          <tr>
            <th>名前</th>
            <td>{{ $user->name }}</td>
          </tr>
          <tr>
            <th>E-Mail</th>
            <td>{{ $user->email }}</td>
          </tr>

          @foreach($user->agency->user_custom_items as $userCustomItem)
            @if($userCustomItem->flg)
              <tr>
                <th>{{ $userCustomItem->name }}</th>
                <td>{{ optional($user->user_custom_values->firstWhere('user_custom_item_id', $userCustomItem->id))->val }}</td>
              </tr>
            @endif
          @endforeach

          <tr>
            <th>流入サイト</th>
            <td>{{ $user->inflow->site_name }}</td>
          </tr>
          <tr>
            <th>状態</th>
            <td>{{ $user->status_label }}</td>
          </tr>
          <tr>
            <th>登録日</th>
            <td>{{ $user->created_at->format('Y年m月d日 H:i') }}</td>
          </tr>
          <tr>
            <th>最終更新日</th>
            <td>{{ $user->updated_at->format('Y年m月d日 H:i') }}</td>
          </tr>
          <tr>
            <th>備考</th>
            <td>{{ $user->note }}</td>
          </tr>
        </tbody>
      </table>
      <a class="btn btn-primary" type="button" href="{{ route('admin.users.edit', $user->id) }}">編集</a>

    </main>
  </div>
</div>
@endsection
