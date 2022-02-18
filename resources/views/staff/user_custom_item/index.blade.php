@extends('layouts.staff.app')

@section('content')

<main>
  <div id="pageHead">
    <h1><span class="material-icons">playlist_add</span>カスタム項目</h1>
  </div>

  @include('staff.common.success_message')
  @include('staff.common.decline_message')
  @include('staff.common.error_message')

  <div id="tabNavi">
    <ul>
      @foreach($formSelects['userCustomCategoryDatas'] as $category) {{-- 上部タブ（カテゴリ名） --}}
        <li>
          <span class="tab
            @if($defaultOpenTab) {{-- タブID指定あり --}}
              @if($defaultOpenTab == $category->code) tabstay @endif
            @elseif($loop->first) {{-- タブIDの指定がない場合は一番目のタブをactiveに --}}
              tabstay
            @endif">{{ $category->name }}
          </span>
        </li>
      @endforeach
    </ul>
  </div>

  @foreach($formSelects['userCustomCategoryDatas'] as $category)
    <div class="customList
      @if($defaultOpenTab) {{-- タブID指定あり --}}
        @if($defaultOpenTab == $category->code) show @endif
      @elseif($loop->first) {{-- タブIDの指定がない場合は一番目のタブをactiveに --}}
        show
      @endif">
      @foreach($category->user_custom_category_items as $categoryItem)
        <h2>
          <span class="material-icons">text_fields</span>{{ $categoryItem->name }}
          @can('create', App\Models\UserCustomItem::class) {{-- 作成権限 --}}
            <a href="
            @if($categoryItem->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'))
              {{ route('staff.system.custom.text.create', [$agencyAccount]) }}?default_category={{$categoryItem->user_custom_category_id}}
            @elseif($categoryItem->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'))
              {{ route('staff.system.custom.list.create', [$agencyAccount]) }}?default_category={{$categoryItem->user_custom_category_id}}
            @elseif($categoryItem->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'))
              {{ route('staff.system.custom.date.create', [$agencyAccount]) }}?default_category={{$categoryItem->user_custom_category_id}}
            @endif
            "><span class="material-icons">add_circle</span>項目追加</a>
          @endcan
        </h2>
        <table>
          <thead>
            <tr>
              <th class="wd40">項目名</th>
              <th class="wd60">
                @if($category->code === config('consts.user_custom_categories.CUSTOM_CATEGORY_SUBJECT')) {{-- 科目マスタ --}}
                  設定科目
                @else
                  設置個所
                @endif
              </th>
              <th>有効</th>
            </tr>
          </thead>
          <tbody>
            @foreach($categoryItem->user_custom_items_for_agency as $customItem)
              @can('view', $customItem)
                <tr>
                  <td>
                    <a href="
                      @if($categoryItem->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_TEXT'))
                        {{ route('staff.system.custom.text.edit', [$agencyAccount, $customItem->id]) }}
                      @elseif($categoryItem->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_LIST'))
                        {{ route('staff.system.custom.list.edit', [$agencyAccount, $customItem->id]) }}
                      @elseif($categoryItem->type === config('consts.user_custom_items.CUSTOM_ITEM_TYPE_DATE'))
                        {{ route('staff.system.custom.date.edit', [$agencyAccount, $customItem->id]) }}
                      @endif
                      ">{{ $customItem->name }}</a>
                  </td>
                  <td>
                    @if($customItem->fixed_item)
                      固定
                    @else
                      {{ $customItem->display_position_name }}
                    @endif
                  </td>
                  <td>
                    @if($customItem->undelete_item) {{--削除不可項目は表示・非表示切り替え不可 --}}
                      -
                    @else
                      <div class="checkBox">
                        <input type="checkbox" name="flg[]" id="flg_{{ $customItem->id }}" data-agency_account="{{ $agencyAccount }}" value="{{ $customItem->id }}"@if($customItem->flg) checked @endif>
                        <label for="flg_{{ $customItem->id }}"></label>
                      </div>
                    @endif
                  </td>
                </tr>
              @endcan
            @endforeach
          </tbody>
        </table>
      @endforeach
    </div>
  @endforeach
</main>

<script src="{{ mix('/staff/js/user_custom_item-index.js') }}"></script>
@endsection
