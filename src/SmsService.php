<?php

namespace Jaby\Sms;

class SmsService
{

    protected $driver ;

    protected $mode ;

    protected $class ;


    /**
     * SmsService constructor.
     */
    public function __construct()
    {
        $this->driver = config('sms.default');

        $this->class = config('sms.map.'.$this->driver);

    }

    /**
     * @param $key
     * @return $this
     */
    public function driver($key)
    {
        $this->driver = $key ;

        $this->class = config('sms.map.'.$this->driver);

        return $this;
    }

    /**
     * @param $text
     * @return mixed
     */
    public function text($text)
    {
        $drive = $this->drive('text',[$text]);

        return $drive;
    }

    /**
     * @param null $code
     * @return mixed
     */
    public function pattern($code = null)
    {
        $drive = $this->drive('pattern',[$code]);

        return $drive;
    }

    /**
     * @param $key
     * @param array $params
     * @return mixed
     */
    public function drive($key,$params = [])
    {
        $class = new $this->class;

       return call_user_func_array(array($class,$key),$params);
    }

}
