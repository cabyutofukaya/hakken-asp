@if(optional($webReserveExt->web_consult)->is_reach_consult_max())
  <div id="errorMessage">
    <p>受付上限数に達したため受付できません。</p>
  </div>
@endif

@if(optional($webReserveExt)->rejection_at)
  <div id="errorMessage">
    <p>辞退済み相談案件です({{ $webReserveExt->rejection_at }})。</p>
  </div>
@endif

@if(optional($webReserveExt->web_consult)->cancel_at)
  <div id="errorMessage">
    <p>ユーザーにより取り消しされました({{ $webReserveExt->web_consult->cancel_at }})。</p>
  </div>
@endif