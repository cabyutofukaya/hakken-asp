@extends('layouts.staff.app')

@section('content')
<main>
  <div id="pageHead">
    <h1>
      <span class="material-icons">manage_accounts</span>ユーザー権限
    </h1>

    @can('create', App\Models\AgencyRole::class)
      <div class="rtBtn">
        <button onclick="location.href='{{ route('staff.system.role.create', $agencyAccount) }}'" class="addBtn"><span class="material-icons">manage_accounts</span>権限追加</button>
      </div>
    @endcan

  </div>

  @include('staff.common.success_message')
  @include('staff.common.error_message')
  
  @can('viewAny', App\Models\Staff::class)
    <div id="agencyRoleList" class="tableWrap dragTable" agencyAccount='{{$agencyAccount}}'></div>
  @endcan

</main>

<script src="{{ mix('/staff/js/agency_role-index.js') }}"></script>
@endsection
