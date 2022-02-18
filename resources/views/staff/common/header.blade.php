<header>
	<div id="logo">
		<a href="{{ route('staff.asp.estimates.reserve.index', $agencyAccount) }}"><img src="{{ asset('/staff/img/shared/logo.svg') }}" alt="HAKKEN" width="197" height="30" /></a>
	</div>
	<div id="loginUser">
		<p>
			<span class="userAuth">{{ Auth::user('staff')->agency_role->name }}</span>{{ Auth::user('staff')->name }}
		</p>
	</div>

	@include('staff.common.news_alert')

  <div id="logout">
		<a href="{{ route('staff.logout', $agencyAccount) }}" onclick="event.preventDefault();document.getElementById('logout-form').submit();"><span class="material-icons">logout</span>LOGOUT</a>
		<form id="logout-form" action="{{ route('staff.logout', $agencyAccount) }}" method="POST" style="display: none;">
			@csrf
		</form>
	</div>
</header>

@include('staff.common.news')