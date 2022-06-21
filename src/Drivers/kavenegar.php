<?php

namespace Jaby\Sms\Drivers;

use Illuminate\Support\Facades\Http;
use Jaby\Sms\SmsInterface;

class kavenegar implements SmsInterface
{
    protected $drive = 'kavenegar';

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

    public $token;


    /**
     * @var \Illuminate\Config\Repository|\Illuminate\Contracts\Foundation\Application|mixed
     */

    /**
     * farazsms constructor.
     */
    public function __construct()
    {
        $this->username = config('sms.drivers.'.$this->drive.'.username');
        $this->password = config('sms.drivers.'.$this->drive.'.password');
        $this->token    = config('sms.drivers.'.$this->drive.'.token');
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
        $token = $this->token ?? $this->password;
        $url = $this->url.'/'.$token.'/verify/lookup.json';
        $url .= '?receptor='.$this->numbers[0];
        foreach ($this->data as $patternKey => $patternValue ){
            $url .= '&'.$patternKey.'='.$patternValue;
        }
        $url .= '&template='.$this->pattern_code;
        $response = Http::get($url)->body();
        return $response;
    }

    /**
     * @param $text
     * @return mixed
     */
    public function message($text)
    {
        $token = $this->token ?? $this->password;
        $url = $this->url.'/'.$token.'/sms/send.json';
        $url .= '?receptor='.implode(",",$this->numbers);
        $url .= '&sender='.$this->from;
        $url .= '&message='.$text;
        $response = Http::get($url)->body();
        return $response;
    }
}

