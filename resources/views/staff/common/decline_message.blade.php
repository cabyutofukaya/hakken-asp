@if (session('decline_message'))
<div id="declineMessage">
  <p><span class="material-icons">do_not_disturb_on</span> &nbsp;{{ session('decline_message') }}</p>
  <span class="material-icons closeIcon">cancel</span>
</div>
@endif