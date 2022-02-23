@extends('layouts.admin.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">person</span>{{ $webUser->name }}({{ $webUser->name_kana }})</h1>

    <div id="acountControl"
      webUserId='{{ $webUser->id }}'
      status='{{ $webUser->status }}'
    ></div>
    
    <ol class="breadCrumbs">
      <li><a href="{{ route('admin.web.web_users.index') }}">HAKKEN個人顧客管理</a></li>
      <li><span>{{ $webUser->name }}({{ $webUser->name_kana }})</span></li>
    </ol>
  </div>

  {!! Form::open(['route'=>['admin.web.web_users.update',$webUser->id], 'method'=>'put']) !!}

  <h2 class="subTit"><span class="material-icons">person</span>基本情報</h2>
  
  @include("admin.common.error_message")
  @include("admin.common.success_message")
  
  <div id="inputArea">
    <ul class="sideList">
      <li class="wd30">
        <span class="inputLabel">顧客番号</span>
        <input type="text" value="{{ $webUser->web_user_number }}" disabled>
      </li>
    </ul>
  <ul class="sideList">
    <li class="wd40">
      <span class="inputLabel">氏名</span>
      <input type="text" name="name" value="{{ $webUser->name }}" placeholder="例）山田 太郎">
    </li>
    <li class="wd40">
      <span class="inputLabel">氏名(カナ)</span>
      <input type="text" name="name_kana" value="{{ $webUser->name_kana }}" placeholder="例）ヤマダ タロウ">
    </li>
    <li class="wd40 mr00">
      <span class="inputLabel">氏名(ローマ字)</span>
      <input type="text" name="name_roman" value="{{ $webUser->name_roman }}" placeholder="例）YAMADA TAROU">
    </li>
  </ul>
  <ul class="sideList">
    <li class="wd20">
      <span class="inputLabel">性別</span>
      <ul class="baseRadio sideList half mt10">
        <li>
          <input type="radio" id="radio03" name="sex" value="{{ config('consts.web_users.SEX_MALE') }}"@if(old('sex', $webUser->sex) == config('consts.web_users.SEX_MALE')) checked @endif>
          <label for="radio03">男性</label>
        </li>
        <li>
          <input type="radio" id="radio04" name="sex" value="{{ config('consts.web_users.SEX_FEMALE') }}"@if(old('sex', $webUser->sex) == config('consts.web_users.SEX_FEMALE')) checked @endif>
          <label for="radio04">女性</label>
        </li>
      </ul>
    </li>
    <li class="wd60">
      <span class="inputLabel">生年月日</span>
      <div class="selectSet wd100">
        <div class="selectBox wd40 mr10">
          <select name="birthday_y">
            @foreach($birthdayYears as $val => $str)
              <option value="{{ $val }}"@if(old('birthday_y', $webUser->birthday_y) == $val)selected="selected" @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
        <div class="selectBox wd30 mr10">
          <select name="birthday_m">
            @foreach($birthdayMonths as $val => $str)
              <option value="{{ $val }}"@if(old('birthday_m', $webUser->birthday_m) == $val)selected="selected" @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
        <div class="selectBox wd30">
          <select name="birthday_d">
            @foreach($birthdayDays as $val => $str)
              <option value="{{ $val }}"@if(old('birthday_d', $webUser->birthday_d) == $val)selected="selected" @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </li>
    <li class="wd20 mr00">
      <span class="inputLabel">年齢区分</span>
      <div class="selectBox">
        <select name="age_kbn">
          @foreach($ageKbns as $val => $str)
            <option value="{{ $val }}"@if(old('age_kbn', $webUser->age_kbn) == $val)selected="selected" @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
  </ul>
  <hr class="sepBorder">
    <ul class="sideList half">
      <li>
        <span class="inputLabel">携帯 <span class="default">(緊急連絡先)</span></span>
        <input type="tel" name="mobile_phone" value="{{ old('mobile_phone',$webUser->mobile_phone) }}" placeholder="例）090-1111-1111">
      </li>
      <li>
        <span class="inputLabel">固定電話</span>
        <input type="tel" name="tel" value="{{ old('tel',$webUser->tel) }}" placeholder="例）03-1111-1111">
      </li>
      <li><span class="inputLabel">FAX</span>
        <input type="tel" name="fax" value="{{ old('fax',$webUser->fax) }}" placeholder="例）03-1111-1111">
      </li>
      <li><span class="inputLabel">メールアドレス</span>
        <input type="email" name="email" value="{{ $webUser->email }}" placeholder="例）yamada@cab-station.com" disabled>
      </li>
    </ul>
    <hr class="sepBorder">
    <ul class="baseList">
      <li class="wd40"><span class="inputLabel">郵便番号</span>
        <div class="buttonSet">
          <input type="text" name="zip_code" value="{{ old('zip_code', $webUser->zip_code) }}" class="wd60" maxlength="7">
          <button class="orangeBtn wd40 addressSearch">検索</button>
        </div>
      </li>
      <li><span class="inputLabel">住所</span>
        <div class="selectSet">
          <div class="selectBox wd20">
            <select name="prefecture_code">
              @foreach($prefectures as $val => $str)
                <option value="{{ $val }}"@if(old('prefecture_code', $webUser->prefecture_code) == $val)selected="selected" @endif>{{ $str }}</option>
              @endforeach
            </select>  
          </div>
          <input type="text" name="address1" value="{{ old('address1', $webUser->address1) }}" class="wd80">
        </div>
      </li>
      <li><span class="inputLabel">ビル・建物名</span>
        <input type="text" name="address2" value="{{ old('address2', $webUser->address2) }}">
      </li>
    </ul>
    <hr class="sepBorder">
    <ul class="sideList">
      <li>
        <span class="inputLabel">旅券番号</span>
        <input type="text" name="passport_number" value="{{ old('passport_number', $webUser->passport_number) }}">
      </li>
      <li>
        <span class="inputLabel">旅券発行日</span>
        <div class="calendar">
          <input type="text" name="passport_issue_date" value="{{ old('passport_issue_date', $webUser->passport_issue_date) }}">
        </div>
      </li>
      <li>
        <span class="inputLabel">旅券有効期限</span>
        <div class="calendar">
          <input type="text" name="passport_expiration_date" value="{{ old('passport_expiration_date', $webUser->passport_expiration_date) }}">
        </div>
      </li>
      <li>
        <span class="inputLabel">旅券発行国</span>
        <div class="selectBox">
          <select name="passport_issue_country_code">
            @foreach($countries as $val => $str)
              <option value="{{ $val }}"@if(old('passport_issue_country_code', $webUser->passport_issue_country_code) == $val)selected="selected" @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
      </li>
      <li class="mr00">
      <span class="inputLabel">国籍</span>
      <div class="selectBox">
        <select name="citizenship_code">
          @foreach($countries as $val => $str)
            <option value="{{ $val }}"@if(old('citizenship_code', $webUser->citizenship_code) == $val)selected="selected" @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
  </ul>
  </div>
  <h2 class="subTit">
    <span class="material-icons">perm_contact_calendar</span>勤務先/学校
  </h2>
  <div class="inputSubArea">
    <ul class="baseList">
      <li class="wd50"><span class="inputLabel">名称</span>
        <input type="text" name="workspace_name" value="{{ old('workspace_name', $webUser->workspace_name) }}">
      </li>
      <li class="wd100">
        <span class="inputLabel">住所</span>
        <input type="text" name="workspace_address" value="{{ old('workspace_address', $webUser->workspace_address) }}">
      </li>
      <li class="wd50">
        <span class="inputLabel">電話番号</span>
        <input type="tel" name="workspace_tel" value="{{ old('workspace_tel', $webUser->workspace_tel) }}">
      </li>
      <li class="wd100">
        <span class="inputLabel">備考 ※ユーザー、会社側にも表示されます</span>
        <textarea rows="3" name="workspace_note">{{ old('workspace_note', $webUser->workspace_note) }}</textarea>
      </li>
    </ul>
  </div>  
  
  {{-- <h2 class="subTit"><span class="material-icons">more</span>その他オプション</h2>
  <div class="inputSubArea">
    <h2 class="optTit">ビザ情報<a href="#" class="js-modal-open" data-target="mdAddVisa"><span class="material-icons">add_circle</span>追加</a></h2>
    <div class="tableWrap dragTable">
      <div class="tableCont">
        <table>
        <thead>
          <tr>
            <th><span>番号</span></th>
            <th><span>国</span></th>
            <th><span>種別</span></th>
            <th><span>発行地</span></th>
            <th><span>発行日</span></th>
            <th><span>有効期限</span></th>
            <th><span>備考</span></th>
            <th class="txtalc wd10"><span>削除</span></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td class="txtalc">
      <span class="material-icons js-modal-open" data-target="mdDeleteCard">delete</span></td>
          </tr>
    </tbody>
    </table>
  </div>
    </div>
    <h2 class="optTit mt40">マイレージ<a href="#" class="js-modal-open" data-target="mdAddMile"><span class="material-icons">add_circle</span>追加</a></h2>
  <div class="tableWrap dragTable">
    <div class="tableCont">
    <table>
        <thead>
          <tr>
            <th><span>航空会社</span></th>
            <th><span>カード番号</span></th>
            <th><span>備考</span></th>
            <th class="txtalc wd10"><span>削除</span></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td class="txtalc">
      <span class="material-icons js-modal-open" data-target="mdDeleteCard">delete</span></td>
          </tr>
    </tbody>
    </table>
  </div>
    </div>
    <h2 class="optTit mt40">メンバーズカード<a href="#" class="js-modal-open" data-target="mdAddCard"><span class="material-icons">add_circle</span>追加</a></h2>
  <div class="tableWrap dragTable">
    <div class="tableCont">
    <table>
        <thead>
          <tr>
            <th><span>カード名</span></th>
            <th><span>カード番号</span></th>
            <th><span>備考</span></th>
            <th class="txtalc wd10"><span>削除</span></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td>-</td>
            <td>-</td>
            <td>-</td>
            <td class="txtalc">
      <span class="material-icons js-modal-open" data-target="mdDeleteCard">delete</span></td>
          </tr>
    </tbody>
    </table>
  </div>
    </div>
  </div> --}}

  <ul id="formControl">
    <li class="wd50">
      <button class="grayBtn" onClick="event.preventDefault();location.href='{{ route('admin.web.web_users.index') }}'">
        <span class="material-icons">arrow_back_ios</span>変更せずに戻る
      </button>
    </li>
    <li class="wd50">
      <button class="blueBtn">
        <span class="material-icons">save</span> この内容で更新する
      </button>
    </li>
  </ul>
  {!! Form::close() !!}
</main>

<link rel="stylesheet" type="text/css" href="https://npmcdn.com/flatpickr/dist/themes/airbnb.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script> 
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/ja.js"></script> 
<script>
    flatpickr.localize(flatpickr.l10ns.ja);
    flatpickr('.calendar input', {
        allowInput: true,
		dateFormat: "Y/m/d"
    });
</script>
<script src="{{ mix('/admin/js/address_search.js') }}"></script>
<script src="{{ mix('/admin/js/web_user-edit.js') }}"></script>
@endsection
