<div id="inputArea" class="pt10">
  <h2 class="mb30 documentSubTit"><span class="material-icons">drive_file_rename_outline</span>テンプレート名・説明</h2>
  <ul class="baseList">
    <li class="wd40">
      <span class="inputLabel req">テンプレート名</span><input type="text" name="name" value="{{ $defaultValue['name'] ?? '' }}" maxlength="50">
    </li>
    <li class="wd100">
      <span class="inputLabel">説明</span><input type="text" name="description" value="{{ $defaultValue['description'] ?? '' }}" maxlength="100">
    </li>
  </ul>
  <h2 class="mb30 documentSubTit pt00"><span class="material-icons">subject</span>出力項目設定</h2>
  <ul class="documentCheckList">
    <li>
      <h3>{{ __("values.document_commons.address_person") }}</h3>
      <ul>
        <input type="hidden" name="setting[{{ config('consts.document_commons.ADDRESS_PERSON') }}]" />{{-- チェックなしでも値が更新されるようにhiddenで初期化 --}}
        @foreach(config('consts.document_commons.ADDRESS_PERSON_LIST') as $parent => $childs)
          @include('staff.document_category.common._setting_row', [
            'checkValues' => Arr::get($defaultValue, 'setting.' . config('consts.document_commons.ADDRESS_PERSON'), null),
            'parent' => $parent,
            'childs' => $childs,
            'name' => config('consts.document_commons.ADDRESS_PERSON'),
            'idPrefix' => 'ap',
          ])
        @endforeach
      </ul>
  </li>
  <li>
    <h3>{{ __("values.document_commons.address_business") }}</h3>
    <ul>
      <input type="hidden" name="setting[{{ config('consts.document_commons.ADDRESS_BUSINESS') }}]" />{{-- チェックなしでも値が更新されるようにhiddenで初期化 --}}
      @foreach(config('consts.document_commons.ADDRESS_BUSINESS_LIST') as $parent => $childs)
        @include('staff.document_category.common._setting_row', [
          'checkValues' => Arr::get($defaultValue, 'setting.' . config('consts.document_commons.ADDRESS_BUSINESS'), null),
          'parent' => $parent,
          'childs' => $childs,
          'name' => config('consts.document_commons.ADDRESS_BUSINESS'),
          'idPrefix' => 'ab',
        ])
      @endforeach
    </ul>
  </li>
  <li>
    <h3>{{ __("values.document_commons.company_info") }}</h3>
    <ul>
      <input type="hidden" name="setting[{{ config('consts.document_commons.COMPANY_INFO') }}]" />{{-- チェックなしでも値が更新されるようにhiddenで初期化 --}}
      @foreach(config('consts.document_commons.COMPANY_INFO_LIST') as $parent => $childs)
        @include('staff.document_category.common._setting_row', [
          'checkValues' => Arr::get($defaultValue, 'setting.' . config('consts.document_commons.COMPANY_INFO'), null),
          'parent' => $parent,
          'childs' => $childs,
          'name' => config('consts.document_commons.COMPANY_INFO'),
          'idPrefix' => 'ci',
        ])
      @endforeach
    </ul>
  </li>
</ul>
<hr class="sepBorder">
<h2 class="mb30 mt20 documentSubTit"><span class="material-icons">apartment</span>自社情報</h2>
<ul class="baseList">		
    <li class="wd60"><span class="inputLabel">自社名</span>
      <input type="text" name="company_name" value="{{ $defaultValue['company_name'] ?? '' }}" maxlength="30">
    </li>
    <li class="wd100">
      <span class="inputLabel">補足情報1</span><textarea name="supplement1">{{ $defaultValue['supplement1'] ?? '' }}</textarea>
    </li>
    <li class="wd100">
      <span class="inputLabel">補足情報2</span><textarea name="supplement2">{{ $defaultValue['supplement2'] ?? '' }}</textarea>
    </li>
    <li class="wd30">
      <span class="inputLabel">郵便番号</span><input type="text" name="zip_code" value="{{ $defaultValue['zip_code'] ?? '' }}" maxlength="7" placeholder="1000000">
    </li>
    <li class="wd100">
      <span class="inputLabel">住所1</span><input type="text" name="address1" value="{{ $defaultValue['address1'] ?? '' }}" maxlength="100">
    </li>
    <li class="wd100">
      <span class="inputLabel">住所2</span><input type="text" name="address2" value="{{ $defaultValue['address2'] ?? '' }}" maxlength="100">
    </li>
    <li class="wd40">
      <span class="inputLabel">TEL</span><input type="tel" name="tel" value="{{ $defaultValue['tel'] ?? '' }}" maxlength="32">
    </li>
    <li class="wd40">
      <span class="inputLabel">FAX</span><input type="tel" name="fax" value="{{ $defaultValue['fax'] ?? '' }}" maxlength="32">
    </li>
</ul>
</div>
