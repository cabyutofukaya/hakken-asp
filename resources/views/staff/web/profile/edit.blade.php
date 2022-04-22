@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">badge</span>プロフィール管理
    </h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.front.profile.update', [$agencyAccount]) }}">HAKKEN WEBページ管理</a></li>
      <li><span>プロフィール管理</span></li>
    </ol>
    @if(env('MIX_OPEN_MODE') === 'grand-open') {{-- プレビューはブランドオープン時に有効に --}}
      <div class="deleteControl wd15">
        <form method="post" action="{{ route('staff.front.profile.preview', [$agencyAccount, \Hashids::encode($my->id)]) }}" target="_blank" id="previewForm">
          <button id="preview" class="blueBtn">プレビュー</button>
        </form>
      </div>
    @endif
  </div>
  
  <h2 class="subTit">
    <span class="material-icons">person</span>プロフィール基本情報
  </h2>

  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <form method="post" action="{{ route('staff.front.profile.update', [$agencyAccount]) }}" id="updateForm">
    @csrf
    <div id="inputArea"
    consts='@json($consts)'
    jsVars='@json($jsVars)' 
    defaultValue='@json($defaultValue)' 
    formSelects='@json($formSelects)'
    ></div>
    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
      <li class="wd50"><button class="blueBtn doubleBan"><span class="material-icons">save</span>この内容で登録する</button></li>
    </ul>
  </form>
</main>

<script type="text/javascript">
  $(()=>{
    $("#preview").on("click", function(e){
      e.preventDefault();
  
      // 入力されたデータを取得
      const $updateForm = $('#updateForm'); 
      const query = $updateForm.serialize();
      const param = $updateForm.serializeArray();
  
      // プレビュー用の送信formの中身を一旦空に
      $("#previewForm").find("input").remove();
      
      // 入力データを隠しフィールドにセット
      for(let row of param){
        $("#previewForm").append($('<input />', {
          type: 'hidden',
          name: row.name,
          value: row.value,
          }));
      }
  
      // プレビューページへ遷移
      $("#previewForm").submit();
    });
  });
</script>
<script src="{{ mix('/staff/js/web_profile-create-edit.js') }}"></script>
@endsection
