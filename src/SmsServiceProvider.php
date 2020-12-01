<?php


namespace Jaby\Sms;


use Illuminate\Support\ServiceProvider;

class SmsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('sms',function (){
            return new SmsService();
        });

        $this->mergeConfigFrom(__DIR__.'/config/sms.php','sms');

    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/sms.php' => config_path('sms.php')
        ]);
    }

}
