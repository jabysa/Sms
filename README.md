![alt text](resources/images/icon.png)

# Laravel Sms Services


[![Software License][ico-license]](LICENSE.md)
[![Latest Version on Packagist][ico-version]][link-packagist]
[![Total Downloads on Packagist][ico-download]][link-packagist]
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/jabysa/Sms/badges/quality-score.png?b=main)](https://scrutinizer-ci.com/g/jabysa/Sms/?branch=main)

This is a Laravel Package for Sms Services Integration. This package supports `Laravel 5.8+`.

[Donate me](https://sourcecity.ir/banners/) if you like this package <3

For PHP integration you can use [jaby/sms](https://github.com/jaby/sms) package.

> This packages works with multiple drivers, and you can create custom drivers if you can't find them in the [current drivers list](#list-of-available-drivers) (below list).



# List of available drivers
- [farazsms](https://farazsms.com/) :heavy_check_mark:

- Others are under way.

**Help me to add the services below by creating `pull requests`**

- sms.ir
- mellipayamak.com
- farapayamak.ir
- ...


> All services that work with ippanel can used default service `farazsms`


> you can create your own custom drivers if it's not  exists in the list, read the `Create custom drivers` section.

## Install

Via Composer

``` bash
$ composer require jaby/sms
```

## Configure

If you are using `Laravel 5.5` or higher then you don't need to add the provider and alias. (Skip to b)

a. In your `config/app.php` file add these two lines.

```php
// In your providers array.
'providers' => [
    ...
    Jaby\Sms\SmsServiceProvider::class,
],

// In your aliases array.
'aliases' => [
    ...
    'Sms' => Jaby\Sms\Sms::class,
],
```

b. then run `php artisan vendor:publish` to publish `config/sms.php` file in your config directory.

In the config file you can set the `default driver` to use for all your sender. But you can also change the driver at runtime.

Choose what service you would like to use in your application. Then make that as default driver so that you don't have to specify that everywhere. But, you can also use multiple services in a project.

```php
// Eg. if you want to use zarinpal.
'default' => 'farazsms',
```

Then fill the credentials for that service in the drivers array.

```php
'drivers' => [
    'farazsms' => [
        'username'    => 'username',
        'password'    => 'password',
        'urlPattern'  => 'https://ippanel.com/patterns/pattern',
        'urlNormal'   => 'https://ippanel.com/services.jspd',
        'from'        => '+983000505',
    ],
    ...
]
```

## How to use


available methods:

- `driver`: set the driver 
- `text`: set the message to send without pattern
- `patten`: set your pattern code
- `data`: set array of data  pattern
- `to`: set array of numbers receivers
- `from`: set sender number 
- `send`: send your sms



#### Examples:
```php
Sms::text('Hello')->to(['numbers'])->send();

Sms::driver('your driver')->text('Hello')->to(['numbers'])->send();

Sms::pattern('your pattent code')->data([
    'name' => $name ,
    'code' => $code
])->to(['numbers'])->send();

Sms::...->from('sender number')-> ...->send();
```

#### Create custom drivers:

First you have to add the name of your driver, in the drivers array and also you can specify any config parameters you want.

```php
'drivers' => [
    'farazsms' => [...],
    'my_driver' => [
        ... // Your Config Params here.
    ]
]
```

Now you have to create a Driver Map Class that will be used to send sms.

Eg. You created a class: `App\Packages\Sms\MyDriver`.

```php
namespace App\Packages\Sms;

use Jaby\Sms\SmsInterface;

class MyDriver extends SmsInterface
{
    protected $drive = 'farazsms';

    protected $method;

    protected $username;

    protected $password;

    protected $from;

    protected $pattern_code;

    protected $to;

    protected $input_data;

    protected $url;

    protected $numbers;

    protected $data;

    protected $text;

    /**
     * farazsms constructor.
     */
    public function __construct()
    {
        $this->username = config('sms.drivers.'.$this->drive.'.username');
        $this->password = config('sms.drivers.'.$this->drive.'.password');
        $this->from     = config('sms.drivers.'.$this->drive.'.from');
        $this->url      = config('sms.drivers.'.$this->drive.'.urlPattern');
    }

    /**
     * @return bool|mixed|string
     */
    public function send()
    {
        if ($this->method == 'pattern')
            $res = $this->sendPattern();
        else
            $res = $this->message($this->text);
        return $res;
    }

    /**
     * @param $text
     * @return $this|mixed
     */
    public function text($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @param null $pattern_code
     * @return $this|mixed
     */
    public function pattern($pattern_code = null)
    {
        $this->method = 'pattern';
        if ($pattern_code)
            $this->pattern_code = $pattern_code;
        return $this;
    }

    /**
     * @param array $data
     * @return $this|mixed
     */
    public function data(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param $from
     * @return $this|mixed
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @param array $numbers
     * @return $this|mixed
     */
    public function to(array $numbers)
    {
        $this->numbers = $numbers;

        return $this;
    }

    /**
     * @return bool|mixed|string
     */
    public function sendPattern()
    {
        $numbers       = $this->numbers;
        $pattern_code  = $this->pattern_code;
        $username      = $this->username;
        $password      = $this->password;
        $from          = $this->from;
        $to            = $numbers;
        $input_data    = $this->data;
        $url = $this->url."?username=" . $username . "&password=" . urlencode($password) . "&from=$from&to=" . json_encode($to) . "&input_data=" . urlencode(json_encode($input_data)) . "&pattern_code=$pattern_code";
        $handler = curl_init($url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $input_data);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($handler);
        return $response;
    }

    /**
     * @param $text
     * @return mixed
     */
    public function message($text)
    {

        $this->url   = config('sms.drivers.'.$this->drive.'.urlNormal');

        $rcpt_nm = $this->numbers;
        $param = array
        (
            'uname'=> $this->username ,
            'pass'=> $this->password,
            'from'=>$this->from,
            'message'=>$text,
            'to'=>json_encode($rcpt_nm),
            'op'=>'send'
        );

        $handler = curl_init($this->url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        $response2 = curl_exec($handler);

        $response2 = json_decode($response2);
        $res_code = $response2[0];
        $res_data = $response2[1];

        return $res_data;
    }
}
```

Once you create that class you have to specify it in the `sms.php` config file `map` section.

```php
'map' => [
    ...
    'my_driver' => App\Packages\Sms\MyDriver::class,
]
```

**Note:-** You have to make sure that the key of the `map` array is identical to the key of the `drivers` array.


## Security

If you discover any security related issues, please email jaber_sabzali@yahoo.com instead of using the issue tracker.

## Credits

- [jaber_sabzali][link-author]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/jaby/sms.svg?style=flat-square
[ico-download]: https://img.shields.io/packagist/dt/jaby/sms.svg?color=%23F18&style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/jaby/sms
[link-author]: https://github.com/jabysa
[link-contributors]: ../../contributors
