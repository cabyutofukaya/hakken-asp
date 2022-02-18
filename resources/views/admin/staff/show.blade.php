@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">
      <h1>スタッフ詳細</h1>

      <div>
        <a class="nav-link" href="{{ route('admin.model_logs.index', ['model' => 'App\Models\Staff', 'model_id' => $staff->id]) }}">
          <i class="far fa-file-alt"></i>
          操作ログ
        </a>
      </div>

      <table class="table">
        <tbody>
          <tr>
            <th>ID</th>
            <td>{{ $staff->id }}</td>
          </tr>
          <tr>
            <th>アカウント</th>
            <td>{{ $staff->account }}</td>
          </tr>
          <tr>
            <th>会社</th>
            <td>{{ $staff->agency->name }}</td>
          </tr>
          <tr>
            <th>名前</th>
            <td>{{ $staff->name }}</td>
          </tr>
          <tr>
            <th>E-Mail</th>
            <td>{{ $staff->email }}</td>
          </tr>
          <tr>
            <th>種別</th>
            <td>{{ $staff->role->name }}</td>
          </tr>
          <tr>
            <th>ステータス</th>
            <td>{{ $staff->status_label }}</td>
          </tr>
          <tr>
            <th>登録日</th>
            <td>{{ $staff->created_at->format('Y年m月d日 H:i') }}</td>
          </tr>
          <tr>
            <th>最終更新日</th>
            <td>{{ $staff->updated_at->format('Y年m月d日 H:i') }}</td>
          </tr>
        </tbody>
      </table>
      <a class="btn btn-primary" type="button" href="{{ route('admin.staffs.edit', [$staff->agency->id, $staff->id]) }}">編集</a>

    </main>
  </div>
</div>
@endsection
