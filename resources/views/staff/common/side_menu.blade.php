<nav>
   <div id="minimal">
      <span class="material-icons">arrow_left</span>
   </div>
   <ul>
      @if(auth('staff')->user()->is_reserve_setting_read_permission){{-- 予約/見積閲覧権限 --}}
         <li class="current @if(strpos(\Route::current()->getName(), 'staff.asp.estimates.') !== false || strpos(\Route::current()->getName(), 'staff.web.estimates.') !== false) active @endif">
            <a href="#">
               <span class="material-icons">edit_calendar</span>予約/見積
            </a>
            <ul class="subNavi">
               <li class="
                  @if(strpos(\Route::current()->getName(), 'staff.asp.estimates.reserve.') !== false || (strpos(\Route::current()->getName(), 'staff.asp.estimates') !== false && \Route::current()->parameter('applicationStep') === config("consts.reserves.APPLICATION_STEP_RESERVE"))) stay 
                  @endif">
                  <a href="{{ route('staff.asp.estimates.reserve.index', $agencyAccount) }}">
                     <span class="material-icons">event_note</span>予約管理
                  </a>
               </li>
               <li class="
                  @if(strpos(\Route::current()->getName(), 'staff.asp.estimates.normal.') !== false || (strpos(\Route::current()->getName(), 'staff.asp.estimates') !== false && \Route::current()->parameter('applicationStep') === config("consts.reserves.APPLICATION_STEP_DRAFT"))) stay 
                  @endif">
                  <a href="{{ route('staff.asp.estimates.normal.index', $agencyAccount) }}">
                     <span class="material-icons">event_note</span>見積管理
                  </a>
               </li>
               <li class="
                  @if(strpos(\Route::current()->getName(), 'staff.web.estimates.reserve.') !== false || (strpos(\Route::current()->getName(), 'staff.web.estimates') !== false && \Route::current()->parameter('applicationStep') === config("consts.reserves.APPLICATION_STEP_RESERVE"))) stay 
                  @endif">
                  <a href="{{ route('staff.web.estimates.reserve.index', $agencyAccount) }}"><span class="material-icons">language</span>WEB予約管理</a>
               </li>
               <li class="
                  @if(strpos(\Route::current()->getName(), 'staff.web.estimates.normal.') !== false || (strpos(\Route::current()->getName(), 'staff.web.estimates') !== false && \Route::current()->parameter('applicationStep') === config("consts.reserves.APPLICATION_STEP_DRAFT"))) stay 
                  @endif">
                  <a href="{{ route('staff.web.estimates.normal.index', $agencyAccount) }}"><span class="material-icons">language</span>WEB見積管理</a>
               </li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.asp.estimates.departed.') !== false) stay @endif">
                  <a href="{{ route('staff.asp.estimates.departed.index', $agencyAccount) }}">
                     <span class="material-icons">event_available</span>催行済み一覧
                  </a>
               </li>
            </ul>
         </li>
      @endif

      @if(auth('staff')->user()->is_user_setting_read_permission || auth('staff')->user()->is_business_user_setting_read_permission){{-- 個人顧客/法人顧客閲覧権限 --}}
         <li class="current @if(strpos(\Route::current()->getName(), 'staff.client.') !== false) active @endif">
            <a href="#">
               <span class="material-icons">account_box</span>顧客管理
            </a>
            <ul class="subNavi">
               @if(auth('staff')->user()->is_user_setting_read_permission){{-- 個人顧客閲覧権限 --}}
                  <li class="@if(strpos(\Route::current()->getName(), 'staff.client.person.') !== false) stay @endif">
                     <a href="{{ route('staff.client.person.index', $agencyAccount) }}">
                        <span class="material-icons">person</span>個人顧客
                     </a>
                  </li>
               @endif
               @if(auth('staff')->user()->is_business_user_setting_read_permission){{-- 法人顧客閲覧権限 --}}
                  <li class="@if(strpos(\Route::current()->getName(), 'staff.client.business.') !== false) stay @endif">
                     <a href="{{ route('staff.client.business.index', $agencyAccount) }}">
                        <span class="material-icons">business</span>法人顧客
                     </a>
                  </li>
               @endif
            </ul>
         </li>
         @endif

         @if(auth('staff')->user()->is_management_setting_read_permission){{-- 経理業務閲覧権限 --}}
            <li class="current @if(strpos(\Route::current()->getName(), 'staff.management.') !== false) active @endif">
               <a href="#">
                  <span class="material-icons">payments</span>経理業務
               </a>
               <ul class="subNavi">
                  <li class="@if(strpos(\Route::current()->getName(), 'staff.management.invoice.') !== false) stay @endif">
                     <a href="{{ route('staff.management.invoice.index', $agencyAccount) }}">
                        <span class="material-icons">get_app</span>請求管理
                     </a>
                  </li>
                  @can('viewAny', App\Models\AccountPayableDetail::class)
                     <li class="@if(strpos(\Route::current()->getName(), 'staff.management.payment.') !== false) stay @endif">
                        <a href="{{ route('staff.management.payment.index', $agencyAccount) }}">
                           <span class="material-icons">upload</span>支払管理
                        </a>
                     </li>
                  @endcan
               </ul>
            </li>
         @endif

         @if(auth('staff')->user()->is_consultation_setting_read_permission){{-- 相談履歴閲覧権限 --}}
            <li class="current @if(strpos(\Route::current()->getName(), 'staff.consultation.') !== false) active @endif">
               <a href="#">
                  <span class="material-icons">question_answer</span>相談履歴
               </a>
               <ul class="subNavi">
                  <li class="@if(strpos(\Route::current()->getName(), 'staff.consultation.index') !== false) stay @endif">
                     <a href="{{ route('staff.consultation.index', $agencyAccount) }}">
                        <span class="material-icons">question_answer</span>相談履歴
                     </a>
                  </li>
                  <li class="@if(strpos(\Route::current()->getName(), 'staff.consultation.message.') !== false) stay @endif">
                     <a href="{{ route('staff.consultation.message.index', $agencyAccount) }}">
                        <span class="material-icons">question_answer</span>メッセージ履歴
                     </a>
                  </li>
               </ul>
            </li>
         @endif

      @if(auth('staff')->user()->is_master_setting_read_permission){{-- マスタ設定閲覧権限 --}}
         <li class="current @if(strpos(\Route::current()->getName(), 'staff.master.') !== false) active @endif">
            <a href="#">
               <span class="material-icons">storage</span>マスタ管理
            </a>
            <ul class="subNavi">
               <li class="@if(strpos(\Route::current()->getName(), 'staff.master.direction.') !== false) stay @endif">
                  <a href="{{ route('staff.master.direction.index', $agencyAccount) }}"><span class="material-icons">explore</span>方面</a></li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.master.area.') !== false) stay @endif">
                  <a href="{{ route('staff.master.area.index', $agencyAccount) }}"><span class="material-icons">public</span>国・地域</a></li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.master.city.') !== false) stay @endif">
                  <a href="{{ route('staff.master.city.index', $agencyAccount) }}"><span class="material-icons">location_on</span>都市・空港</a></li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.master.subject.') !== false) stay @endif">
                  <a href="{{ route('staff.master.subject.index', $agencyAccount) }}"><span class="material-icons">list</span>科目</a></li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.master.supplier.') !== false) stay @endif">
                  <a href="{{ route('staff.master.supplier.index', $agencyAccount) }}"><span class="material-icons">move_to_inbox</span>仕入れ先</a></li>
            </ul>
         </li>
      @endif

      @if(auth('staff')->user()->is_system_setting_read_permission){{-- システム設定閲覧権限 --}}
         <li class="current @if(strpos(\Route::current()->getName(), 'staff.system.') !== false) active @endif">
            <a href="#">
               <span class="material-icons">settings</span>システム設定
            </a>
            <ul class="subNavi">
               <li class="@if(strpos(\Route::current()->getName(), 'staff.system.custom.') !== false) stay @endif">
                  <a href="{{ route('staff.system.custom.index', $agencyAccount) }}">
                     <span class="material-icons">playlist_add</span>カスタム項目
                  </a>
               </li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.system.user.') !== false) stay @endif">
                  <a href="{{ route('staff.system.user.index', $agencyAccount) }}">
                     <span class="material-icons">person_search</span>ユーザー管理
                  </a>
               </li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.system.role.') !== false) stay @endif">
                  <a href="{{ route('staff.system.role.index', $agencyAccount) }}">
                     <span class="material-icons">manage_accounts</span>ユーザー権限
                  </a>
               </li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.system.document.') !== false) stay @endif">
                  <a href="{{ route('staff.system.document.index', $agencyAccount) }}">
                     <span class="material-icons">description</span>帳票設定
                  </a>
               </li>
               {{-- <li class="@if(strpos(\Route::current()->getName(), 'staff.system.mail.') !== false) stay @endif">
                  <a href="{{ route('staff.system.mail.index', $agencyAccount) }}">
                     <span class="material-icons">mark_email_read</span>メール定型文設定
                  </a>
               </li> --}}
            </ul>
         </li>
      @endif
      
      @if(auth('staff')->user()->is_web_setting_read_permission){{-- WEBページ管理覧権限 --}}
         <li class="current @if(strpos(\Route::current()->getName(), 'staff.front.') !== false) active @endif">
            <a href="#">
               <span class="material-icons">cast</span>WEBページ管理
            </a>
            <ul class="subNavi">
               <li class="@if(strpos(\Route::current()->getName(), 'staff.front.modelcourse.') !== false) stay @endif">
                  @can('viewAny', App\Models\WebModelcourse::class) {{-- 閲覧権限 --}}
                     <a href="{{ route('staff.front.modelcourse.index', $agencyAccount) }}">
                        <span class="material-icons">description</span>モデルコース管理
                     </a>
                  @endcan
               </li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.front.profile.') !== false) stay @endif">
                  @can('viewAny', App\Models\WebProfile::class) {{-- 閲覧権限 --}}
                     <a href="{{ route('staff.front.profile.edit', $agencyAccount) }}">
                        <span class="material-icons">badge</span>プロフィール管理
                     </a>
                  @endcan
               </li>
               <li class="@if(strpos(\Route::current()->getName(), 'staff.front.company.') !== false) stay @endif">
                  @can('viewAny', App\Models\WebCompany::class) {{-- 閲覧権限 --}}
                     <a href="{{ route('staff.front.company.edit', $agencyAccount) }}">
                        <span class="material-icons">apartment</span>会社情報管理
                     </a>
                  @endcan
               </li>
            </ul>
         </li>
      @endif
   </ul>
</nav>