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

        $this->app->register('Jaby\Sms\SmsServiceProvider');
        $loader = \Illuminate\Foundation\AliasLoader::getInstance();
        $loader->alias('Sms', 'Jaby\Sms\Sms');

    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/sms.php' => config_path('sms.php')
        ]);
    }

}
