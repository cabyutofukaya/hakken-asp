@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
<h1><span class="material-icons">apartment</span>会社情報管理</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.front.company.update', [$agencyAccount]) }}">HAKKEN WEBページ管理</a></li>
      <li><span>会社情報管理</span></li>
    </ol>
  </div>
  
  @include('staff.web.common._check_web_valid')


  <h2 class="subTit">
    <span class="material-icons">person</span>会社説明
  </h2>

  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')
  <form method="post" action="{{ route('staff.front.company.update', [$agencyAccount]) }}">
    @csrf
    <div id="inputArea"
      consts='@json($consts)'
      jsVars='@json($jsVars)' 
      defaultValue='@json($defaultValue)' 
    ></div>
    <ul id="formControl">
      <li class="wd50"><button class="grayBtn" onClick="event.preventDefault();history.back()"><span class="material-icons">arrow_back_ios</span>登録せずに戻る</button></li>
      <li class="wd50"><button class="blueBtn"><span class="material-icons">save</span> この内容で登録する</button></li>
    </ul>
  </form>

  @if(auth('staff')->user()->web_valid)
    <h2 class="subTit">
      <span class="material-icons">apartment</span>会社基本情報
    </h2>
    <div class="inputSubArea">
    <!-- 有効化した場合、下の入力項目表示 -->
    <ul class="sideList half">
      <li><span class="inputLabel">社名</span>
          <input type="text" value="{{ $agency->company_name }}" disabled>
        </li>
      <li><span class="inputLabel"><font color="#333333">社名(カナ)</font> 
        </span>
          <input type="text" value="{{ $agency->company_kana }}" disabled>
        </li>
      <li><span class="inputLabel">代表者名</span>
          <input type="text" value="{{ $agency->representative_name }}" disabled>
        </li>
      <li><span class="inputLabel"><font color="#333333">代表者名(カナ)</font> 
        </span>
          <input type="text" value="{{ $agency->representative_kana }}" disabled>
        </li>
    </ul>
      <hr class="sepBorder">
      <ul class="baseList">
        <li class="wd40"><span class="inputLabel">郵便番号</span>
          <div class="buttonSet">
            <input type="text" class="wd60" disabled value="{{ $agency->zip_code_hyphen }}">
          </div>
        </li>
        <li><span class="inputLabel">住所</span>
          <div class="selectSet">
            <div class="selectBox wd20">
              <input type="text" value="{{ $agency->prefecture->name }}" disabled>
            </div>
            <input type="text" class="wd80" value="{{ $agency->address1 }}" disabled>
          </div>
        </li>
        <li><span class="inputLabel">ビル・建物名</span>
          <input type="text" value="{{ $agency->address2 }}" disabled>
        </li>
      </ul>
      <hr class="sepBorder">
      <ul class="sideList half">
        <li><span class="inputLabel">電話番号</span>
          <input type="tel" value="{{ $agency->tel }}" disabled>
        </li>
        <li><span class="inputLabel">FAX番号</span>
          <input type="tel" value="{{ $agency->fax }}" disabled>
        </li>
        <li><span class="inputLabel">メールアドレス</span>
          <input type="email" value="{{ $agency->email }}" disabled>
        </li>
        <li><span class="inputLabel">緊急連絡先</span>
          <input type="tel" value="{{ $agency->emergency_contact }}" disabled>
        </li>
      </ul>
      <hr class="sepBorder">
      <ul class="sideList">
        <li class="wd30"><span class="inputLabel">設立年月日</span>
          <div class="calendar">
            <input type="text" value="{{ $agency->establishment_at }}" disabled>
          </div>
        </li>
        <li class="wd30"><span class="inputLabel">資本金</span>
          <div><input type="text" value="{{ number_format($agency->capital) }}" disabled></div></li>
        <li class="wd20"><span class="inputLabel">従業員数</span>
          <div><input type="text" value="{{ number_format($agency->employees_number) }}" disabled></div></li>
      </ul>
      <hr class="sepBorder">
      <ul class="sideList">
        <li class="wd30"><span class="inputLabel">旅行業登録年月日</span>
          <div class="calendar">
            <input type="text" value="{{ $agency->travel_agency_registration_at }}" disabled>
          </div>
        </li>
        <li class="wd20"><span class="inputLabel">業務範囲</span>
          <div class="selectBox">
        <input type="text" value="{{ $agency->business_scope_label }}" disabled>
          </div>
        </li>
      </ul>
      <ul class="sideList">
        <li class="wd30"><span class="inputLabel">登録行政庁名</span>
          <input type="text" value="{{ $agency->registered_administrative_agency }}" disabled>
        </li>
        <li class="wd20"><span class="inputLabel">登録種別</span>
          <div class="selectBox">
        <input type="text" value="{{ $agency->registration_type_label }}" disabled>
          </div>
        </li>
        <li class="wd50 mr00"><span class="inputLabel">登録番号</span>
          <input type="text" value="{{ $agency->registration_number }}" disabled>
        </li>
      </ul>
      <ul class="sideList">
        <li class="wd20"><span class="inputLabel">旅行業協会</span>
          <div class="selectBox">
        <input type="text" value="{{ $agency->travel_agency_association_label }}" disabled>
          </div>
        </li>
        <li class="wd20"><span class="inputLabel">旅行取協</span>
      <input type="text" value="{{ $agency->fair_trade_council_label }}" disabled>
        </li>
        <li class="wd20"><span class="inputLabel">IATA加入</span>
          
      <input type="text" value="{{ $agency->iata_label }}" disabled>
        </li>
        <li class="wd20"><span class="inputLabel">e-TBT加入</span>
          
      <input type="text" value="{{ $agency->etbt_label }}" disabled>
        </li>
        <li class="wd20 mr00"><span class="inputLabel">ポンド保証制度</span>
      <input type="text" value="{{ $agency->bond_guarantee_label }}" disabled>
        </li>
      </ul>
    </div>
  @endif

</main>

<script src="{{ mix('/staff/js/web_company-create-edit.js') }}"></script>
@endsection
