<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
  @if (session('success_message'))
  $(function () {
    toastr.success('{{ session('success_message') }}');
  });
  @endif

  @if (session('auth_error'))
  $(function () {
    toastr.error('{{ session('auth_error') }}');
  });
  @endif
</script>