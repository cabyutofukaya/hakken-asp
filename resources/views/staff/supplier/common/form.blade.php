<div id="inputArea">
  <ul class="baseList">		
    <li class="wd40"><span class="inputLabel req">仕入れ先コード</span>
      <input 
        type="text" 
        name="code" 
        value="{{ $defaultValue['code'] ?? null }}"
        @if($editMode === 'edit') disabled @endif
        >
    </li>
    <li class="wd100"><span class="inputLabel">仕入れ先名称</span>
      <input type="text" name="name" value="{{ $defaultValue['name'] ?? null }}">
    </li>
    <li class="wd40">
      <span class="inputLabel">基準日</span>
      <div class="selectBox">
        <select name="reference_date">
          @foreach($formSelects['referenceDates'] as $val => $str)
            <option value="{{ $val }}" @if(Arr::get($defaultValue, "reference_date", "") == $val) selected @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
  </ul>
  <ul class="sideList">		
    <li class="wd20">
      <span class="inputLabel">締日</span>
      <div class="selectBox">
        <select name="cutoff_date">
          @foreach($formSelects['cutoffDates'] as $val => $str)
            <option value="{{ $val }}" @if(Arr::get($defaultValue, "cutoff_date", "") == $val) selected @endif>{{ $str }}</option>
          @endforeach
        </select>
      </div>
    </li>
    <li class="wd40">
      <span class="inputLabel">支払い日</span>
      <div class="selectSet wd100">
        <div class="selectBox wd50 mr10">
          <select name="payment_month">
            @foreach($formSelects['paymentMonths'] as $val => $str)
              <option value="{{ $val }}" @if(Arr::get($defaultValue, "payment_month", "") == $val) selected @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
        <div class="selectBox wd50">
          <select name="payment_day">
            @foreach($formSelects['paymentDays'] as $val => $str)
              <option value="{{ $val }}" @if(Arr::get($defaultValue, "payment_day", "") == $val) selected @endif>{{ $str }}</option>
            @endforeach
          </select>
        </div>
      </div>
    </li>
  </ul>
  <hr class="sepBorder">

  {{-- 振込先情報 --}}
  <div id="supplierAccountPayableArea" 
    defaultValue='@json(collect($defaultValue)->only(['supplier_account_payables']))' formSelects='@json(collect($formSelects)->only(['bankAccountTypes', 'bankSelectItems']))'
    jsVars='@json($jsVars)'
    ></div>

  {{-- カスタム項目 --}}
  @if($formSelects['userCustomItems']->isNotEmpty())
    <ul class="sideList half">
      @foreach($formSelects['userCustomItems'] as $uci)
        <li>
          @include('staff.common._custom_field', [
            'row' => $uci,
            'value' => $defaultValue[$uci->key],
            'addClass' => '',
            'customCategoryCode' => $customCategoryCode,
            'unedit' => $uci->unedit_item
            ])
        </li>
      @endforeach
    </ul> 
    <hr class="sepBorder">
  @endif
    
  <ul class="baseList">
    <li><span class="inputLabel">備考</span><textarea name="note">{{ $defaultValue['note'] ?? "" }}</textarea></li>
  </ul>
</div>