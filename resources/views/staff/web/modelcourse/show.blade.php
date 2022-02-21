@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1><span class="material-icons">event_note</span>モデルコース {{ $courseNo }}</h1>
    <ol class="breadCrumbs">
      <li><a href="{{ route('staff.front.modelcourse.index',[$agencyAccount]) }}">モデルコース管理</a></li>
      <li><span>モデルコース {{ $courseNo }}</span></li>
    </ol>
    <ul class="estimateControl" id="controlArea"
      jsVars='@json($jsVars)' 
      modelcourseId='{{ $id }}'
      previewUrl='{{ $formSelects['previewUrl'] }}'
      >
    </ul>
  </div>
  <div class="userList show">
    <ul class="sideList half">
      <li>
        <h2><span class="material-icons">subject</span>基本情報</h2>
        <table class="baseTable">
          <tbody>
            <tr>
              <th>モデルコースNo</th>
              <td>{{ $courseNo }}</td>
            </tr>
            <tr>
              <th>モデルコース名</th>
              <td>{{ $webModelcourse->name }}</td>
            </tr>
            <tr>
              <th>メイン写真</th>
              <td>
                @if($webModelcourse->web_modelcourse_photo->file_name)
                  <img src="{{ $consts['thumbSBaseUrl'] . $webModelcourse->web_modelcourse_photo->file_name }}" alt="" class="modelCoursePh">
                @endif
              </td>
            </tr>
            <tr>
              <th>説明文</th>
              <td>{{ $webModelcourse->description }}</td>
            </tr>
            {{-- <tr>
              <th>参加人数</th>
              <td>大人(AD)0名　子供(CH)0名　幼児(INF)0名</td>
            </tr> --}}
            <tr>
              <th>日数</th>
              <td>{{ $formSelects['stays'][$webModelcourse->stays] ?? "-" }}</td>
            </tr>
            <tr>
              <th>出発地</th>
              <td>{{ $webModelcourse->departure->name }}{{ $webModelcourse->departure_place }}</td>
            </tr>
            <tr>
              <th>目的地</th>
              <td>{{ $webModelcourse->destination->name }}{{ $webModelcourse->destination_place }}</td>
            </tr>
            <tr>
              <th>タグ&nbsp;</th>
              <td>
                <ul class="tagList">
                  @foreach($webModelcourse->web_modelcourse_tags as $tag)
                    <li>{{ $tag->tag }}</li>
                  @endforeach
                </ul>
              </td>
            </tr>
          </tbody>
        </table>
      </li>
      {{-- <li>
        <h2><span class="material-icons">app_registration</span>見積金額内訳</h2>
        <table class="baseTable">
          <tbody>
            <!--
            <tr>
              <th>GRS合計</th>
              <td>￥0</td>
            </tr>
            <tr>
              <th>NET合計</th>
              <td>￥0&nbsp;</td>
            </tr>
            <tr>
              <th>利益(利益率)</th>
              <td>￥0(0.0%)</td>
            </tr>
            -->
            <tr>
              <th>表示単価</th>
              <td>大人1名￥{{ number_format($webModelcourse->price_per_ad) }} 子供1名￥{{ number_format($webModelcourse->price_per_ch) }} 幼児1名￥{{ number_format($webModelcourse->price_per_inf) }}</td>
            </tr>
          </tbody>
        </table>
      </li> --}}
    </ul>
    <h2 class="mt40"><span class="material-icons">playlist_add_check</span>モデルコース管理情報</h2>
    <ul class="sideList half">
      <li>
        <table class="baseTable">
          <tbody>
            <tr>
              <th>作成者</th>
              <td>{{ $webModelcourse->author->name }}&nbsp;</td>
            </tr>
          </tbody>
        </table>
      </li>
    </ul>
    <ul id="formControl">
      <li class="wd50">
        <button class="grayBtn" onClick="event.preventDefault();location.href='{{ route('staff.front.modelcourse.index',[$agencyAccount]) }}'"><span class="material-icons">arrow_back_ios</span>一覧に戻る</button>
      </li>
      <li class="wd50">
        <button class="blueBtn" onClick="event.preventDefault();location.href='{{ route('staff.front.modelcourse.edit',[$agencyAccount, $courseNo]) }}'"><span class="material-icons">edit_note</span> 基本情報を編集する</button>
      </li>
    </ul>
  </div>
</main>

<script src="{{ mix('/staff/js/web_modelcourse-show.js') }}"></script>
@endsection
