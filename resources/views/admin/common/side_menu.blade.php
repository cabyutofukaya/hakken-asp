<nav>
  <div id="minimal"><span class="material-icons">arrow_left</span></div>
  <ul>
    <li class="@if(strpos(\Route::current()->getName(), 'admin.agencies.') !== false) stay @endif">
      <a href="{{ route('admin.agencies.index') }}"><span class="material-icons">account_box</span>顧客管理</a>
    </li>
    {{-- <li class="current">
      <a href="#"><span class="material-icons">payments</span>経理業務</a>
      <ul class="subNavi">
        <li><a href="/management/invoice/"><span class="material-icons">get_app</span>請求管理</a></li>
        <li><a href="/management/deposited/"><span class="material-icons">check_box</span>請求・入金済み</a></li>
      </ul>
    </li> --}}
    <li class="current @if(strpos(\Route::current()->getName(), 'admin.areas.') !== false) active @endif">
      <a href="#"><span class="material-icons">location_on</span>エリアマスタ管理</a><ul class="subNavi">
        <li class="@if(strpos(\Route::current()->getName(), 'admin.areas.master_directions.') !== false) stay @endif">
          <a href="{{ route('admin.areas.master_directions.import.create') }}"><span class="material-icons">explore</span>方面マスタ</a></li>
        <li class="@if(strpos(\Route::current()->getName(), 'admin.areas.master_areas.') !== false) stay @endif">
          <a href="{{ route('admin.areas.master_areas.import.create') }}"><span class="material-icons">public</span>国・地域マスタ</a></li>
      </ul></li>
      <li class="@if(strpos(\Route::current()->getName(), 'admin.banks.') !== false) active @endif">
        <a href="{{ route('admin.banks.import.create') }}"><span class="material-icons">storage</span>銀行マスタ管理</a></li>
      <li class="current @if(strpos(\Route::current()->getName(), 'admin.web.') !== false) active @endif">
      <a href="#"><span class="material-icons">recent_actors</span>HAKKEN管理</a>
      <ul class="subNavi">
        <li class="@if(strpos(\Route::current()->getName(), 'admin.web.web_users.') !== false || strpos(\Route::current()->getName(), 'admin.web.purposes.') !== false || strpos(\Route::current()->getName(), 'admin.web.interests.') !== false) stay @endif">
          <a href="{{ route('admin.web.web_users.index') }}"><span class="material-icons">person</span>顧客管理</a></li>
          <li class="@if(strpos(\Route::current()->getName(), 'admin.web.system_news.') !== false) stay @endif">
            <a href="{{ route('admin.web.system_news.index') }}"><span class="material-icons">notifications</span>通知管理</a>
          </li>
      </ul>
    </li>
  </ul>
</nav>
{{--  
  <a href="{{ route('admin.roles.index') }}">権限管理</a>
  <a href="{{ route('admin.users.index', ['sort'=>'id','direction'=>'desc']) }}">ユーザー管理</a>
  <a href="{{ route('admin.prefectures.index') }}">都道府県マスタ</a>
  <a href="{{ route('admin.inflows.index') }}">流入サイト</a>
  hakken
  <a href="{{ route('admin.web.purposes.index') }}">目的マスタ</a>
  <a href="{{ route('admin.web.interests.index') }}">興味マスタ</a>
  ログ
  <a href="{{ route('admin.model_logs.index') }}">操作ログ</a>
  <a href="{{ route('admin.act_logs.index') }}">操作ログ</a> --}}