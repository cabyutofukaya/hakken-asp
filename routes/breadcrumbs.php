<?php

dd('breeadcrums.php');
// Home
Breadcrumbs::for('admin.home.index', function ($trail) {
    $trail->push('Dashboard', route('admin.home.index'));
});

// Home > Agency
Breadcrumbs::register('admin.agencies.index', function ($breadcrumbs) {
  $breadcrumbs->parent('admin.home.index');
  $breadcrumbs->push('旅行会社管理', route('admin.agencies.index'));
});

// Home > Agency > 新規
Breadcrumbs::register('admin.agencies.create', function ($breadcrumbs) {
  $breadcrumbs->parent('admin.agencies.index');
  $breadcrumbs->push('新規登録', route('admin.agencies.create'));
});

// Home > Agency > 詳細
Breadcrumbs::register('admin.agencies.show', function ($breadcrumbs, $agency) {
  $breadcrumbs->parent('admin.agencies.index');
  $breadcrumbs->push("詳細", route('admin.agencies.show', $agency->id));
});

// Home > Agency > 編集
Breadcrumbs::register('admin.agencies.edit', function ($breadcrumbs, $agency) {
  $breadcrumbs->parent('admin.agencies.index');
  $breadcrumbs->push("編集", route('admin.agencies.edit', $agency->id));
});

// // Home > Inflow
// Breadcrumbs::register('admin.inflows.index', function ($breadcrumbs) {
//   $breadcrumbs->parent('admin.home.index');
//   $breadcrumbs->push('流入マスタ', route('admin.inflows.index'));
// });

// // Home > Inflow > 新規
// Breadcrumbs::register('admin.inflows.create', function ($breadcrumbs) {
//   $breadcrumbs->parent('admin.inflows.index');
//   $breadcrumbs->push('新規登録', route('admin.inflows.create'));
// });

// // Home > Inflow > 編集
// Breadcrumbs::register('admin.inflows.edit', function ($breadcrumbs, $inflow) {
//   $breadcrumbs->parent('admin.inflows.index');
//   $breadcrumbs->push("編集", route('admin.inflows.edit', $inflow->id));
// });


// Home > User
Breadcrumbs::register('admin.users.index', function ($breadcrumbs) {
  $breadcrumbs->parent('admin.home.index');
  $breadcrumbs->push('顧客一覧', route('admin.users.index'));
});

// Home > User > 新規
Breadcrumbs::register('admin.users.create', function ($breadcrumbs) {
  $breadcrumbs->parent('admin.users.index');
  $breadcrumbs->push('新規登録', route('admin.users.create'));
});

// Home > User > 編集
Breadcrumbs::register('admin.users.edit', function ($breadcrumbs, $user) {
  $breadcrumbs->parent('admin.users.index');
  $breadcrumbs->push("編集", route('admin.users.edit', $user->id));
});

// Home > User > 詳細
Breadcrumbs::register('admin.users.show', function ($breadcrumbs, $user) {
  $breadcrumbs->parent('admin.users.index');
  $breadcrumbs->push("詳細", route('admin.users.show', $user->id));
});

// Home > ModelLog
Breadcrumbs::register('admin.model_logs.index', function ($breadcrumbs) {
  $breadcrumbs->parent('admin.home.index');
  $breadcrumbs->push('操作ログ', route('admin.model_logs.index'));
});

// Home > ActLog
Breadcrumbs::register('admin.act_logs.index', function ($breadcrumbs) {
  $breadcrumbs->parent('admin.home.index');
  $breadcrumbs->push('操作ログ', route('admin.act_logs.index'));
});

// Home > Role
Breadcrumbs::register('admin.roles.index', function ($breadcrumbs) {
  $breadcrumbs->parent('admin.home.index');
  $breadcrumbs->push('権限一覧', route('admin.roles.index'));
});

// Home > Role > 新規
Breadcrumbs::register('admin.roles.create', function ($breadcrumbs) {
  $breadcrumbs->parent('admin.roles.index');
  $breadcrumbs->push('新規登録', route('admin.roles.create'));
});

// Home > Role > 編集
Breadcrumbs::register('admin.roles.edit', function ($breadcrumbs, $role) {
  $breadcrumbs->parent('admin.roles.index');
  $breadcrumbs->push("編集", route('admin.roles.edit', $role->id));
});
