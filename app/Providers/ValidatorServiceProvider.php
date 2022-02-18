<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class ValidatorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /**
         * 自然数
         *
         * @return bool
         */
        \Validator::extend(
            'natural_number',
            function ($attribute, $value, $parameters, $validator) {
            if (preg_match("/^[0-9]+$/", $value)) {
                return ($value > 0);
            }
            return false;
        }
        );

        /**
         * 全角カナ
         *
         * @return bool
         */
        \Validator::extend(
            'kana',
            function ($attribute, $value, $parameters, $validator) {
            return preg_match("/^[ァ-ヶー]+$/u", $value);
        }
        );

    }
}
