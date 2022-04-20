@extends('layouts.staff.front')

@section('content')
<div id="wrapper">
  @include('staff.web.common.preview_header')
  <main>
    <div class="searchHead">
      <div class="backList"><a href="javascript:void(0)">旅行マイスター 詳細へ</a></div></div>
      
      <div id="modelCourse">
        <div class="modelHead">
          <div class="mainImg">
            @if(data_get($webModelcourse, 'web_modelcourse_photo.file_name'))
              <img src="{{ $consts['thumbBaseUrl'] . $webModelcourse->web_modelcourse_photo->file_name }}" alt="{{ $webModelcourse->name }}">
            @endif
          </div>
          <ul class="titlePrice">
            <li><h1>{{ $webModelcourse->name }}</h1></li>
            <li>
              <p>
                <span class="day">{{ $formSelects['stays'][$webModelcourse->stays] ?? "-" }}</span>
              {{-- 大人1名<span class="price">6,000</span>円<br>子供1名<span class="price">3,500</span>円 --}}
              </p>
            </li>
          </ul>
        </div>
      <div class="modelCourseTxt">
        <ul class="tag">
          @foreach($webModelcourse->web_modelcourse_tags as $tag)
            <li>{{ $tag->tag }}</li>
          @endforeach
        </ul>
        <p>
          <span>{!! nl2br(e($webModelcourse->description)) !!}</span>
        </p>
      </div>
      
      {{-- <div class="dispCourse">
        <div class="dispPlan">
          <h3>1日目</h3>
          <div class="inner">
            <ul class="spotList">
              <li>
                <div class="spotBlock express">
                  <span class="time">10:20</span>
                  <h4>東京駅</h4>
                  <p>東海道新幹線 普通車指定席</p>
                </div>
              </li>
              <li>
                <div class="spotBlock train">
                  <span class="time">10:20</span>
                  <h4>京都駅烏丸口</h4>
                  <p>トロッコ列車“ロマンティックトレイン嵯峨野”</p>
                </div>
              </li>
              <li>
                <div class="spotBlock">
                  <span class="time">10:20</span>
                  <h4 class="pickup">保津川下り<span>30分滞在</span></h4>
                  <p>亀岡から嵐山までの川下り<br>※コース出発時、河川増水等により保津川下りが欠航となった場合、案内中止となります。<br>この場合、コース内容を一部変更します。</p>
                </div>
              </li>
              <li>
                <div class="spotBlock walk">
                  <span class="time">10:20</span>
                  <h4 class="pickup">嵐山散策<span>120分滞在</span></h4>
                  <p>（水量や船の待ち時間により到着時間が前後します。）</p>
                </div>
              </li>
              <li>
                <div class="spotBlock taxi">
                  <span class="time">10:20</span>
                  <h4>渡月橋・天龍寺・竹林・京都嵐山オルゴール博物館など自由散策</h4>
                  <p>※昼食は各自でお召し上がりください※</p>
                </div>
              </li>
              <li>
                <div class="spotBlock">
                  <span class="time">10:20</span>
                  <h4>京都駅烏丸口</h4>
                  <p>東海道新幹線 のぞみ200号京都行</p>
                </div>
              </li>
            </ul>
          </div>
        </div>
        <div class="dispPlan">
          <h3>2日目</h3>
          <div class="inner">
            <ul class="spotList">
              <li>
                <div class="spotBlock airplane">
                  <span class="time">10:20</span>
                  <h4>東京駅</h4>
                  <p>東海道新幹線 普通車指定席</p>
                </div>
              </li>
              <li>
                <div class="spotBlock train">
                  <span class="time">10:20</span>
                  <h4>京都駅烏丸口</h4>
                  <p>トロッコ列車“ロマンティックトレイン嵯峨野”</p>
                </div>
              </li>
              <li>
                <div class="spotBlock">
                  <span class="time">10:20</span>
                  <h4 class="pickup">保津川下り<span>30分滞在</span></h4>
                  <p>亀岡から嵐山までの川下り<br>※コース出発時、河川増水等により保津川下りが欠航となった場合、案内中止となります。<br>この場合、コース内容を一部変更します。</p>
                </div>
              </li>
              <li>
                <div class="spotBlock walk">
                  <span class="time">10:20</span>
                  <h4 class="pickup">嵐山散策<span>120分滞在</span></h4>
                  <p>（水量や船の待ち時間により到着時間が前後します。）</p>
                </div>
              </li>
              <li>
                <div class="spotBlock taxi">
                  <span class="time">10:20</span>
                  <h4>渡月橋・天龍寺・竹林・京都嵐山オルゴール博物館など自由散策</h4>
                  <p>※昼食は各自でお召し上がりください※</p>
                </div>
              </li>
              <li>
                <div class="spotBlock">
                  <span class="time">10:20</span>
                  <h4>京都駅烏丸口</h4>
                  <p>東海道新幹線 のぞみ200号京都行</p>
                </div></li>
              </ul>
            </div>
          </div>
        </div>
      <div class="dispPh">
      <div class="dispSpotPh">
        <div class="ph"><img src="../common/img/search/sample_ph01.jpg" alt=""></div>
        <h4>保津川下り</h4>
        <p>深淵あり、激流あり、四季を映して流れる保津川の峡谷を縫って、丹波の国「亀岡」から京の名勝「嵐山」までの約16キロの旅。</p>
      </div>
      <div class="dispSpotPh">
        <div class="ph"><img src="../common/img/search/sample_ph02.jpg" alt=""></div>
        <h4>嵐山散策</h4>
        <p>京都を代表する観光地、嵐山。国の史跡および名勝に指定され、「渡月橋」や「竹林の道」など京都らしい見どころの宝庫です。</p>
      </div>
      <div class="dispSpotPh">
        <div class="ph"><img src="../common/img/search/sample_ph03.jpg" alt=""></div>
        <h4>嵐山散策</h4>
        <p>京都を代表する観光地、嵐山。国の史跡および名勝に指定され、「渡月橋」や「竹林の道」など京都らしい見どころの宝庫です。</p>
      </div>
      <div class="dispSpotPh">
        <div class="ph"><img src="../common/img/search/sample_ph04.jpg" alt=""></div>
        <h4>嵐山散策</h4>
        <p>京都を代表する観光地、嵐山。国の史跡および名勝に指定され、「渡月橋」や「竹林の道」など京都らしい見どころの宝庫です。</p>
      </div>
    </div>--}}
    
    <div class="meisterProf">
      <div class="profCard">
        <div class="profImg">
          @if($webModelcourse->author->web_profile->web_profile_profile_photo->file_name)
            <img src="{{ $consts['thumbMBaseUrl'] . $webModelcourse->author->web_profile->web_profile_profile_photo->file_name }}" alt="{{ data_get($webModelcourse, 'author.web_profile.name') }}">
          @endif
        </div>
        <h3><span>{{ data_get($webModelcourse, 'agency.company_name') }}</span>{{ data_get($webModelcourse, 'author.web_profile.name') }}</h3></div>
        <ul class="meisterNav">
          <li><a class="baseBtn ico-prof" href="javascript:void(0)">旅行マイスターの詳細</a></li>
          <li>
            <p class="baseBtn ico-consult" data-target="mdConsult">この旅行マイスターに相談する</p>
          </li>
        </ul>
      </div>
    </div>
  </main>
</div>
@include('staff.web.common.preview_footer')
@endsection
