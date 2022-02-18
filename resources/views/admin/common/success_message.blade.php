@if (session('success_message'))
<div id="successMessage">
  <p><span class="material-icons">check_circle</span> {{ session('success_message') }}</p>
  <span class="material-icons closeIcon">cancel</span>
</div>
@endif