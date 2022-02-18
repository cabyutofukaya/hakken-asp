@if(!auth('staff')->user()->web_valid)
  <div id="errorMessage">
    <p>HAKKEN WEBを利用する為には<a href="{{ route('staff.front.profile.edit', [$agencyAccount]) }}">プロフィール管理</a>でアカウントを有効化する必要があります。</p>
  </div>
@endif