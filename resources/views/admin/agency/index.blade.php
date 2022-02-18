@extends('layouts.admin.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">manage_search</span>顧客管理</h1>
    <div class="rtBtn">
      <button onclick="location.href='{{ route('admin.agencies.create') }}'" class="addBtn"><span class="material-icons">person_add</span>新規顧客追加</button>
    </div>

    {!! Form::open(['route'=>['admin.agencies.index'], 'method'=>'get']) !!}
      <div id="searchBox">
        <div id="inputList">
          <ul class="sideList">
            <li class="wd30"><span class="inputLabel">社名</span>
              <input type="text" name="company_name" value="{{ Arr::get($searchParam, 'company_name') }}"/>
            </li>
            <li class="wd30"><span class="inputLabel">住所</span>
              <input type="text" name="address" value="{{ Arr::get($searchParam, 'address') }}"/>
            </li>
            <li class="wd30"><span class="inputLabel">電話番号</span>
              <input type="text" name="tel" value="{{ Arr::get($searchParam, 'tel') }}"/>
            </li>

            <li class="wd15 mr00"><span class="inputLabel">契約状況</span>
              <div class="selectBox">
                <select name="">
                </select>
              </div></li>
          </ul>
          <ul class="sideList">
            <li class="wd15"><span class="inputLabel">業務範囲</span>
              <div class="selectBox">
                <select name="business_scope">
                  @foreach([''=>'すべて'] + $businessScopes as $key => $val)
                    <option value="{{$key}}" @if(Arr::get($searchParam, 'business_scope', '')==$key) selected @endif>{{$val}}</option>
                  @endforeach
                </select>
              </div>
            </li>
            <li class="wd15"><span class="inputLabel">登録種別</span>
              <div class="selectBox">
                <select name="registration_type">
                  @foreach([''=>'すべて'] + $registrationTypes as $key => $val)
                    <option value="{{$key}}" @if(Arr::get($searchParam, 'registration_type', '')==$key) selected @endif>{{$val}}</option>
                  @endforeach
                </select>
              </div>
            </li>
            <li class="wd15"><span class="inputLabel">旅行業協会</span>
              <div class="selectBox">
                <select name="travel_agency_association">
                  @foreach([''=>'すべて'] + $travelAgencyAssociations as $key => $val)
                    <option value="{{$key}}" @if(strlen(Arr::get($searchParam, 'travel_agency_association')) && Arr::get($searchParam, 'travel_agency_association')==$key) selected @endif>{{$val}}</option>
                  @endforeach
                </select>
              </div>
            </li>
            <li class="wd15"><span class="inputLabel">旅公取協</span>
              <div class="selectBox">
                <select name="fair_trade_council">
                  @foreach([''=>'すべて'] + $fairTradeCouncils as $key => $val)
                    <option value="{{$key}}" @if(strlen(Arr::get($searchParam, 'fair_trade_council')) && Arr::get($searchParam, 'fair_trade_council')==$key) selected @endif>{{$val}}</option>
                  @endforeach
                </select>
              </div>
            </li>
            <li class="wd15"><span class="inputLabel">IATA加入</span>
              <div class="selectBox">
                <select name="iata">
                  @foreach([''=>'すべて'] + $iatas as $key => $val)
                    <option value="{{$key}}" @if(strlen(Arr::get($searchParam, 'iata')) && Arr::get($searchParam, 'iata')==$key) selected @endif>{{$val}}</option>
                  @endforeach
                </select>
              </div>
            </li>
            <li class="wd15"><span class="inputLabel">e-TBT加入</span>
              <div class="selectBox">
                <select name="etbt">
                  @foreach([''=>'すべて'] + $etbts as $key => $val)
                    <option value="{{$key}}" @if(strlen(Arr::get($searchParam, 'etbt')) && Arr::get($searchParam, 'etbt')==$key) selected @endif>{{$val}}</option>
                  @endforeach
                </select>
              </div>
            </li>
            <li class="wd15 mr00"><span class="inputLabel">ポンド保証制度</span>
              <div class="selectBox">
                <select name="bond_guarantee">
                  @foreach([''=>'すべて'] + $bondGuarantees as $key => $val)
                    <option value="{{$key}}" @if(strlen(Arr::get($searchParam, 'bond_guarantee')) && Arr::get($searchParam, 'bond_guarantee')==$key) selected @endif>{{$val}}</option>
                  @endforeach
                </select>
              </div>
            </li>
          </ul>
        </div>
        <div id="controlList">
          <ul>
            <li>
              <button class="orangeBtn icon-left"><span class="material-icons">search</span>検索</button>
            </li>
            <li>
              <button class="grayBtn slimBtn" type="reset">条件クリア</button>
            </li>
          </ul>
        </div>
      </div>
    {!! Form::close() !!}

  </div>

  @include("admin.common.decline_message")
  @include("admin.common.error_message")
  @include("admin.common.success_message")

  <div id="tableWrap" class="dragTable">
    <div id="tableCont">
      <table>
        <thead>
          <tr>
            <th class="sort" data-sort="id"><span>ID</span></th>
            <th class="sort" data-sort="account"><span>アカウントID</span></th>
            <th class="sort" data-sort="company_name"><span>社名</span></th>
            <th class="sort" data-sort="address"><span>住所</span></th>
            <th class="sort" data-sort="tel"><span>電話番号</span></th>
            <th class="sort txtalc"><span>契約状況</span></th>
            <th class="sort txtalc"><span>開始日</span></th>
            <th class="sort txtalc"><span>終了日</span></th>
            <th class="sort txtalc" data-sort="business_scope"><span>業務範囲</span></th>
            <th class="sort txtalc" data-sort="registration_type"><span>登録種別</span></th>
            <th class="sort txtalc" data-sort="travel_agency_association"><span>旅行業協会</span></th>
            <th class="sort txtalc" data-sort="fair_trade_council"><span>旅公取協</span></th>
            <th class="sort txtalc" data-sort="iata"><span>IATA加入</span></th>
            <th class="sort txtalc" data-sort="etbt"><span>e-TBT加入</span></th>
            <th class="sort txtalc" data-sort="bond_guarantee"><span>ポンド保証制度</span></th>
          </tr>
        </thead>
        <tbody>
          @forelse($agencies as $agency)
          <tr>
            <td>{{ $agency->id }}</td>
            <td><a href="{{ route('admin.agencies.edit', $agency->id) }}">{{ $agency->account }}</a></td>
            <td>{{ $agency->company_name }}</td>
            <td>{{ $agency->address_label }}</td>
            <td>{{ $agency->tel }}</td>
            <td class="txtalc"><span class="status green">--</span></td>
            <td class="txtalc">---/--/--</td>
            <td class="txtalc">----/--/--</td>
            <td class="txtalc">{{ $agency->business_scope_label }}</td>
            <td class="txtalc">{{ $agency->registration_type_label }}</td>
            <td class="txtalc">{{ $agency->travel_agency_association_label }}</td>
            <td class="txtalc">
              @if($agency->fair_trade_council)
                <span class="material-icons enable">check_circle</span>
              @else
                <span class="material-icons">remove</span>
              @endif
            </td>
            <td class="txtalc">
              @if($agency->iata)
                <span class="material-icons enable">check_circle</span>
              @else
                <span class="material-icons">remove</span>
              @endif
            </td>
            <td class="txtalc">
              @if($agency->etbt)
                <span class="material-icons enable">check_circle</span>
              @else
                <span class="material-icons">remove</span>
              @endif
            </td>
            <td class="txtalc">
              @if($agency->bond_guarantee)
                <span class="material-icons enable">check_circle</span>
              @else
                <span class="material-icons">remove</span>
              @endif
            </td>
          </tr>
          @empty
            <tr>
              <td colspan="15">データがありません</td>
            </tr>
          @endforelse
        </tbody>
      </table>
      {{ $agencies->appends(request()->query())->links('vendor.pagination.app') }}
    </div>
  </div>
</main>

<script src="{{ mix('/admin/js/sortable.js') }}"></script>
@endsection
