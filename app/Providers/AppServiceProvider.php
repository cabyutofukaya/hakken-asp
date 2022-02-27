<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // SQL Log。本番環境ではない場合は出力
        if (env('APP_ENV') !== 'production') {
            \DB::listen(function ($query) {
                $sql = $query->sql;
                for ($i = 0; $i < count($query->bindings); $i++) {
                    $sql = preg_replace("/\?/", $query->bindings[$i], $sql, 1);
                }
    
                \Log::debug("SQL", ["time" => sprintf("%.2f ms", $query->time), "sql" => $sql]);
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
