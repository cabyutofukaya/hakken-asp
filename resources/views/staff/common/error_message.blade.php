@if($errors->any()) {{-- バリデーションエラーとauth_error --}}
  <div id="errorMessage">
    <p>
      @foreach($errors->all() as $error)
        {{$error}}@if(!$loop->last)<br/>@endif
      @endforeach
    </p>
  </div>
@endif

@if(session('error_message'))  {{-- エラーメッセージ --}}
  <div id="errorMessage">
    <p>{{ session('error_message') }}</p>
  </div>
@endif