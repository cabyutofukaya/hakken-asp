<input type="hidden" name="setting" />{{-- settingのcheckboxが未選択の場合の初期値(null)--}}

<div id="inputArea" class="pt10">
  <h2 class="mb30 documentSubTit"><span class="material-icons">drive_file_rename_outline</span>テンプレート名</h2>
  <ul class="baseList">
    <li class="wd40"><span class="inputLabel">テンプレート名</span>
      <input type="text" name="name" value="{{ $defaultValue['name'] ?? null }}">
    </li>
    <li class="wd100"><span class="inputLabel">説明</span>
      <input type="text" name="description" value="{{ $defaultValue['description'] ?? null }}">
    </li>
  </ul>
  <h2 class="mb30 documentSubTit pt00"><span class="material-icons">subject</span>出力項目設定</h2>
  <ul class="sideList mailTemplate">
    <li class="wd60">
      <ul class="baseList mt20">
        <li class="wd100"><span class="inputLabel">件名</span>
          <input type="text" name="subject" value="{{ $defaultValue['subject'] ?? null }}">
        </li>
        <li class="wd100"><span class="inputLabel">本文</span>
          <textarea rows="30" name="body">{{ $defaultValue['body'] ?? null }}</textarea>
        </li>
      </ul>
    </li>
    <li class="wd40 mr00">
      <div class="mailTag">
        <ul class="sideList">
          <li class="wd50">
            <h3>基本情報</h3>
            <ul class="baseList mb30">
              <li>
                <button class="grayBtn" data-tag="予約番号">予約番号</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="件名">件名</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="出発日">出発日</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="帰着日">帰着日</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="出発地">出発地</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="目的地">目的地</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="GRS合計">GRS合計</button>
              </li>
            </ul>
            <h3>予約管理情報</h3>
            <ul class="baseList mt00">
              <li>
                <button class="grayBtn" data-tag="担当者">担当者</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="状況">状況</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="旅行種別">旅行種別</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="申込種別">申込種別</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="区分">区分</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="分類">分類</button>
              </li>
              <li>
                <button class="grayBtn" data-tag="申込日">申込日</button>
              </li>
            </ul>
          </li>
          <li class="wd50 mr00">
            <h3>申込者</h3>
            <ul class="baseList mb30">
              <li>
                <ul class="checkBox">
                  <li>
                    <input type="checkbox" id="applicant1" name="setting[applicant][]" value="漢字" data-tag_group="applicant" @if(in_array('漢字',data_get($defaultValue, 'setting.applicant', []), true)) checked @endif>
                    <label for="applicant1">漢字</label>
                  </li>
                  <li>
                    <input type="checkbox" id="applicant2" name="setting[applicant][]" value="カナ" data-tag_group="applicant" @if(in_array('カナ',data_get($defaultValue, 'setting.applicant', []), true)) checked @endif>
                    <label for="applicant2">カナ</label>
                  </li>
                  <li>
                    <input type="checkbox" id="applicant3" name="setting[applicant][]" value="ローマ字" data-tag_group="applicant" @if(in_array('ローマ字',data_get($defaultValue, 'setting.applicant', []), true)) checked @endif>
                    <label for="applicant3">ローマ字</label>
                  </li>
                  <li>
                    <input type="checkbox" id="applicant4" name="setting[applicant][]" value="敬称" data-tag_group="applicant" @if(in_array('敬称',data_get($defaultValue, 'setting.applicant', []), true)) checked @endif>
                    <label for="applicant4">敬称</label>
                  </li>
                </ul>
                <button class="grayBtn" data-tag="申込者" data-has_child="applicant">申込者</button>
              </li>
            </ul>
            <h3>代表者</h3>
            <ul class="baseList mb30 mt00">
              <li>
                <ul class="checkBox">
                  <li>
                    <input type="checkbox" id="representative1" name="setting[representative][]" value="漢字" data-tag_group="representative" @if(in_array('漢字',data_get($defaultValue, 'setting.representative', []), true)) checked @endif>
                    <label for="representative1">漢字</label>
                  </li>
                  {{-- <li>
                    <input type="checkbox" id="representative2" name="setting[representative][]" value="カナ" data-tag_group="representative" @if(in_array('カナ',data_get($defaultValue, 'setting.representative', []), true)) checked @endif>
                    <label for="representative2">カナ</label>
                  </li>
                  <li>
                    <input type="checkbox" id="representative3" name="setting[representative][]" value="ローマ字" data-tag_group="representative" @if(in_array('ローマ字',data_get($defaultValue, 'setting.representative', []), true)) checked @endif>
                    <label for="representative3">ローマ字</label>
                  </li> --}}
                  <li>
                    <input type="checkbox" id="representative4" name="setting[representative][]" value="敬称" data-tag_group="representative" @if(in_array('敬称',data_get($defaultValue, 'setting.representative', []), true)) checked @endif>
                    <label for="representative4">敬称</label>
                  </li>
                </ul>
                <button class="grayBtn" data-tag="代表者" data-has_child="representative">代表者</button>
              </li>
            </ul>
            <h3>参加者</h3>
            <ul class="baseList mb30 mt00">
              <li>
                <ul class="checkBox">
                  <li>
                    <input type="checkbox" id="participant1" name="setting[participant][]" value="漢字" data-tag_group="participant" @if(in_array('漢字',data_get($defaultValue, 'setting.participant', []), true)) checked @endif>
                    <label for="participant1">漢字</label>
                  </li>
                  {{-- <li>
                    <input type="checkbox" id="participant2" name="setting[participant][]" value="カナ" data-tag_group="participant" @if(in_array('カナ',data_get($defaultValue, 'setting.participant', []), true)) checked @endif>
                    <label for="participant2">カナ</label>
                  </li>
                  <li>
                    <input type="checkbox" id="participant3" name="setting[participant][]" value="ローマ字" data-tag_group="participant" @if(in_array('ローマ字',data_get($defaultValue, 'setting.participant', []), true)) checked @endif>
                    <label for="participant3">ローマ字</label>
                  </li> --}}
                  <li>
                    <input type="checkbox" id="participant4" name="setting[participant][]" value="敬称" data-tag_group="participant" @if(in_array('敬称',data_get($defaultValue, 'setting.participant', []), true)) checked @endif>
                    <label for="participant4">敬称</label>
                  </li>
                </ul>
                <button class="grayBtn" data-tag="参加者" data-has_child="participant">参加者</button>
              </li>
            </ul>
            <h3>スケジュール</h3>
            <ul class="baseList mb30 mt00">
              <li>
                <ul class="checkBox">
                  <li>
                    <input type="checkbox" id="schedule1" name="setting[schedule][]" value="便名" data-tag_group="schedule" @if(in_array('便名',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule1">便名</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule2" name="setting[schedule][]" value="クラス" data-tag_group="schedule" @if(in_array('クラス',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule2">クラス</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule3" name="setting[schedule][]" value="出発地" data-tag_group="schedule" @if(in_array('出発地',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule3">出発地</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule4" name="setting[schedule][]" value="到着地" data-tag_group="schedule" @if(in_array('到着地',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule4">到着地</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule5" name="setting[schedule][]" value="出発日時" data-tag_group="schedule" @if(in_array('出発日時',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule5">出発日時</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule6" name="setting[schedule][]" value="到着日時" data-tag_group="schedule" @if(in_array('到着日時',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule6">到着日時</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule7" name="setting[schedule][]" value="ステータス" data-tag_group="schedule" @if(in_array('ステータス',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule7">ステータス</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule8" name="setting[schedule][]" value="日本語表記" data-tag_group="schedule" @if(in_array('日本語表記',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule8">日本語表記</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule9" name="setting[schedule][]" value="席数" data-tag_group="schedule" @if(in_array('席数',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule9">席数</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule10" name="setting[schedule][]" value="GDS" data-tag_group="schedule" @if(in_array('GDS',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule10">GDS</label>
                  </li>
                  <li>
                    <input type="checkbox" id="schedule11" name="setting[schedule][]" value="PNR" data-tag_group="schedule" @if(in_array('PNR',data_get($defaultValue, 'setting.schedule', []), true)) checked @endif>
                    <label for="schedule11">PNR</label>
                  </li>
                </ul>
                <button class="grayBtn" data-tag="スケジュール" data-has_child="schedule">スケジュール</button>
              </li>
            </ul>
            <h3>代金内訳</h3>
            <ul class="baseList mb30 mt00">
              <li>
                <ul class="checkBox">
                  <li>
                    <input type="checkbox" id="breakdown1" name="setting[breakdown][]" value="内容" data-tag_group="breakdown" @if(in_array('内容',data_get($defaultValue, 'setting.breakdown', []), true)) checked @endif>
                    <label for="breakdown1">内容</label>
                  </li>
                  <li>
                    <input type="checkbox" id="breakdown2" name="setting[breakdown][]" value="単価" data-tag_group="breakdown" @if(in_array('単価',data_get($defaultValue, 'setting.breakdown', []), true)) checked @endif>
                    <label for="breakdown2">単価</label>
                  </li>
                  <li>
                    <input type="checkbox" id="breakdown3" name="setting[breakdown][]" value="数量" data-tag_group="breakdown" @if(in_array('数量',data_get($defaultValue, 'setting.breakdown', []), true)) checked @endif>
                    <label for="breakdown3">数量</label>
                  </li>
                  <li>
                    <input type="checkbox" id="breakdown4" name="setting[breakdown][]" value="金額" data-tag_group="breakdown" @if(in_array('金額',data_get($defaultValue, 'setting.breakdown', []), true)) checked @endif>
                    <label for="breakdown4">金額</label>
                  </li>
                  <li>
                    <input type="checkbox" id="breakdown5" name="setting[breakdown][]" value="消費税" data-tag_group="breakdown" @if(in_array('消費税',data_get($defaultValue, 'setting.breakdown', []), true)) checked @endif>
                    <label for="breakdown5">消費税</label>
                  </li>
                </ul>
                <button class="grayBtn" data-tag="代金内訳" data-has_child="breakdown">代金内訳</button>
              </li>
            </ul>
            <h3>ホテル情報</h3>
            <ul class="baseList mb30 mt00">
              <li>
                <ul class="checkBox">
                  <li>
                    <input type="checkbox" id="hotel1" name="setting[hotel][]" value="宿泊機関" data-tag_group="hotel" @if(in_array('宿泊機関',data_get($defaultValue, 'setting.hotel', []), true)) checked @endif>
                    <label for="hotel1">宿泊機関</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel2" name="setting[hotel][]" value="泊数" data-tag_group="hotel" @if(in_array('泊数',data_get($defaultValue, 'setting.hotel', []), true)) checked @endif>
                    <label for="hotel2">泊数</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel3" name="setting[hotel][]" value="ホテル名" data-tag_group="hotel" @if(in_array('ホテル名',data_get($defaultValue, 'setting.hotel', []), true)) checked @endif>
                    <label for="hotel3">ホテル名</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel4" name="setting[hotel][]" value="部屋タイプ" data-tag_group="hotel" @if(in_array('部屋タイプ',data_get($defaultValue, 'setting.hotel', []), true)) checked @endif>
                    <label for="hotel4">部屋タイプ</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel5" name="setting[hotel][]" value="食事" data-tag_group="hotel" @if(in_array('食事',data_get($defaultValue, 'setting.hotel', []), true)) checked @endif>
                    <label for="hotel5">食事</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel6" name="setting[hotel][]" value="人数" data-tag_group="hotel" @if(in_array('人数',data_get($defaultValue, 'setting.hotel', []), true)) checked @endif>
                    <label for="hotel6">人数</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel7" name="setting[hotel][]" value="部屋数" data-tag_group="hotel" @if(in_array('部屋数',data_get($defaultValue, 'setting.hotel', []), true)) checked @endif>
                    <label for="hotel7">部屋数</label>
                  </li>
                </ul>
                <button class="grayBtn" data-tag="ホテル情報" data-has_child="hotel">ホテル情報</button>
              </li>
            </ul>
            <h3>ホテル連絡先</h3>
            <ul class="baseList mb30 mt00">
              <li>
                <ul class="checkBox">
                  <li>
                    <input type="checkbox" id="hotel_contact1" name="setting[hotel_contact][]" value="ホテル名" data-tag_group="hotel_contact" @if(in_array('ホテル名',data_get($defaultValue, 'setting.hotel_contact', []), true)) checked @endif>
                    <label for="hotel_contact1">ホテル名</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel_contact2" name="setting[hotel_contact][]" value="住所" data-tag_group="hotel_contact" @if(in_array('住所',data_get($defaultValue, 'setting.hotel_contact', []), true)) checked @endif>
                    <label for="hotel_contact2">住所</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel_contact3" name="setting[hotel_contact][]" value="URL" data-tag_group="hotel_contact" @if(in_array('URL',data_get($defaultValue, 'setting.hotel_contact', []), true)) checked @endif>
                    <label for="hotel_contact3">URL</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel_contact4" name="setting[hotel_contact][]" value="TEL" data-tag_group="hotel_contact" @if(in_array('TEL',data_get($defaultValue, 'setting.hotel_contact', []), true)) checked @endif>
                    <label for="hotel_contact4">TEL</label>
                  </li>
                  <li>
                    <input type="checkbox" id="hotel_contact5" name="setting[hotel_contact][]" value="FAX" data-tag_group="hotel_contact" @if(in_array('FAX',data_get($defaultValue, 'setting.hotel_contact', []), true)) checked @endif>
                    <label for="hotel_contact5">FAX</label>
                  </li>
                </ul>
                <button class="grayBtn" data-tag="ホテル連絡先" data-has_child="hotel_contact">ホテル連絡先</button>
              </li>
            </ul>
          </li>
        </ul>
      </div>
    </li>
  </ul>
</div>
