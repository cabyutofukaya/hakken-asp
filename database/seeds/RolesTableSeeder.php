<?php

use Illuminate\Database\Seeder;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('roles')->insert([
            ['name' => 'システム管理者', 'name_en' => 'administrator', 
                'authority' => json_encode(
                    [
                        'users' => array_values(config("consts.roles.ACTIONS_LIST")),
                        'staffs' => array_values(config("consts.roles.ACTIONS_LIST")),
                        'reserves' => array_values(config("consts.roles.ACTIONS_LIST")),
                        'user_custom_items' => array_values(config("consts.roles.ACTIONS_LIST")),
                    ]
                )
            ],
            ['name' => 'オペレーター', 'name_en' => 'operator', 
                'authority' => json_encode(
                    [
                        'users' => array_values(array_diff(array_values(config("consts.roles.ACTIONS_LIST")), [
                            config("consts.roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'staffs' => array_values(array_diff(array_values(config("consts.roles.ACTIONS_LIST")), [
                            config("consts.roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'reserves' => array_values(array_diff(array_values(config("consts.roles.ACTIONS_LIST")), [
                            config("consts.roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                    ]
                )
            ],
            ['name' => '経理', 'name_en' => 'accounting', 
                'authority' => json_encode(
                    [
                        'users' => array_values(array_diff(array_values(config("consts.roles.ACTIONS_LIST")), [
                            config("consts.roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'staffs' => array_values(array_diff(array_values(config("consts.roles.ACTIONS_LIST")), [
                            config("consts.roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'reserves' => array_values(array_diff(array_values(config("consts.roles.ACTIONS_LIST")), [
                            config("consts.roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                    ]
                )
            ],
            ['name' => '一般', 'name_en' => 'general', 
                'authority' => json_encode(
                    [
                        'users' => array_values(array_diff(array_values(config("consts.roles.ACTIONS_LIST")), [
                            config("consts.roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                        'reserves' => array_values(array_diff(array_values(config("consts.roles.ACTIONS_LIST")), [
                            config("consts.roles.DELETE"), // 許可しない権限を配列要素に渡す
                        ])),
                    ]
                )
            ],
        ]);
    }
}
