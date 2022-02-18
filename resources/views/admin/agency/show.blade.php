@extends('layouts.admin.app')

@section('content')
<div class="container-fluid">
  <div class="row">

    @include("admin.common.side_menu")

    <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4">

      {{ Breadcrumbs::render('admin.agencies.show', $agency) }}

      <h1>旅行会社詳細</h1>

      <div>
        <a class="nav-link" href="{{ route('admin.model_logs.index', ['model' => 'App\Models\Agency', 'model_id' => $agency->id]) }}">
          <i class="far fa-file-alt"></i>
          操作ログ
        </a>
      </div>

      <table class="table table-striped w-75">
        <tbody>
          <tr>
            <th>#会社ID</th>
            <td>{{ $agency->id }}</td>
          </tr>
          <tr>
            <th>親会社</th>
            <td>{{ $agency->parent_agency ? $agency->parent_agency->name : "-" }}</td>
          </tr>
          <tr>
            <th>アカウント</th>
            <td>{{ $agency->account }}</td>
          </tr>
          <tr>
            <th>会社名（カナ）</th>
            <td>{{ $agency->company_name }} （{{ $agency->company_kana }}）</td>
          </tr>
          <tr>
            <th>E-Mail</th>
            <td>{{ $agency->email }}</td>
          </tr>
          <tr>
            <th>住所</th>
            <td>〒{{ $agency->zip_code_hyphen }} {{ $agency->prefecture->name }}{{ $agency->address1 }}{{ $agency->address2 }}</td>
          </tr>
          <tr>
            <th>電話番号</th>
            <td>{{ $agency->tel }}</td>
          </tr>
          <tr>
            <th>スタッフ登録許可数</th>
            <td>{{ number_format($agency->number_staff_allowed) }} 名</td>
          </tr>
          <tr>
            <th>ステータス</th>
            <td>{{ $agency->status_label }}</td>
          </tr>
          <tr>
            <th>登録日</th>
            <td>{{ $agency->created_at->format('Y年m月d日 H:i') }}</td>
          </tr>
          <tr>
            <th>最終更新日</th>
            <td>{{ $agency->updated_at->format('Y年m月d日 H:i') }}</td>
          </tr>
          <tr>
            <th>ログインURL</th>
            <td><input type="text" name='login_url' class="form-control" value="{{ $agency->login_url }}"><!--p class="help-block">*同一アカウントでの複数端末によるログインは不可。<br>同時ログイン時は後からログインした端末が優先されます。</p--></td>
          </tr>
          <tr>
            <th>備考</th>
            <td>{{ $agency->note }}</td>
          </tr>
        </tbody>
      </table>


      <div class="mb-2">
        <a class="btn btn-primary" href="{{ route('admin.staffs.create', $agency->id) }}">スタッフ登録</a>
      </div>

      <table class="table table-striped">
        <thead>
          <tr>
            <th scope="col">@sortablelink('id', '#')</th>
            <th scope="col">アカウント</th>
            <th scope="col">名前</th>
            <th scope="col">メールアドレス</th>
            <th scope="col">種別</th>
            <th scope="col">状態</th>
            <th scope="col">登録日時</th>
            <th scope="col">管理</th>
          </tr>
        </thead>
        <tbody>
          @forelse($staffs as $staff)
            <tr>
              <th scope="row">{{ $staff->id }}</th>
              <td>{{ $staff->account }}</td>
              <td>{{ $staff->name }}</td>
              <td>{{ $staff->email }}</td>
              <td>{{ $staff->agency_role->name }}</td>
              <td>{{ Arr::get($statuses, $staff->status) }}</td>
              <td>{{ $staff->created_at->format('Y-m-d H:i') }}</td>
              <td>
                <a href="{{ route('admin.staffs.show', [$agency->id, $staff->id]) }}" class="btn btn-info">詳細</a>
                <a href="{{ route('admin.staffs.edit', [$agency->id, $staff->id]) }}" class="btn btn-secondary">編集</a>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="8">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      {{ $staffs->appends(request()->query())->links() }}

    </main>
  </div>
</div>
@endsection
