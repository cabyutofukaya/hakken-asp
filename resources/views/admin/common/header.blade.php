<header>
  <div id="logo">
    <a href="{{ route('admin.home.index') }}"><img src="{{ asset('/admin/img/shared/logo.svg') }}" alt="HAKKEN" width="197" height="30" /></a>
  </div>
  <div id="logout">
    <a href="{{ route('admin.logout') }}" onclick="event.preventDefault();
    document.getElementById('logout-form').submit();"><span class="material-icons md-dark">logout</span>LOGOUT</a>
    <form id="logout-form" action="{{ route('admin.logout') }}" method="POST" style="display: none;">
      @csrf
    </form>
  </div>
</header>