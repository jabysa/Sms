<?php

namespace Jaby\Sms\Drivers;

use Jaby\Sms\SmsInterface;

class melipayamak implements SmsInterface
{
    protected $drive = 'melipayamak';

    protected $method;

    protected $username;

    protected $password;

    protected $from;

    protected $pattern_code;

    protected $to;

    protected $numbers;

    protected $data;

    protected $text;

    protected $templateId;
    protected $urlٔNormal;
    protected $urlPattern;

    public function __construct()
    {
        $this->username   = config('sms.drivers.' . $this->drive . '.username');
        $this->password   = config('sms.drivers.' . $this->drive . '.password');
        $this->from       = config('sms.drivers.' . $this->drive . '.from');
        $this->urlٔNormal  = config('sms.drivers.'.$this->drive.'.urlNormal');
        $this->urlPattern = config('sms.drivers.'.$this->drive.'.urlPattern');
    }

    public function send()
    {
        if ($this->method === 'pattern') {
            return $this->sendPattern();
        }
        return $this->message($this->text);
    }

    public function text($text)
    {
        $this->text = $text;
        return $this;
    }

    public function pattern($pattern_code = null)
    {
        $this->method = 'pattern';
        if ($pattern_code)
            $this->pattern_code = $pattern_code;
        return $this;
    }

    public function data(array $data)
    {
        $this->data = $data;
        return $this;
    }

    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

    public function to(array $numbers)
    {
        $this->numbers = $numbers;
        return $this;
    }

    public function templateId($templateId = null)
    {
        $this->templateId = $templateId;
        return $this;
    }

    public function sendPattern()
    {
        $inputs = $this->setPatternExceptions();
        $this->setOTP($inputs);

        $mobile = $inputs['mobile'][0];
        $bodyId = $inputs['templateId'];
        $parameters = $inputs['parameters'];

        $values = [];
        foreach ($parameters as $param) {
            $values[] = $param['value'];
        }

        $text = implode(';', $values);

        $data = [
            'username' => $this->username,
            'password' => $this->password,
            'to'       => $mobile,
            'bodyId'   => $bodyId,
            'text'     => $text
        ];

        return $this->post($this->urlPattern, $data);
    }

    public function message($text)
    {
        $inputs = $this->setMessageExceptions();

        $data = [
            'username' => $this->username,
            'password' => $this->password,
            'from'     => $this->from,
            'to'       => implode(',', $inputs['numbers']),
            'text'     => $text
        ];

        return $this->post($this->urlٔNormal, $data);
    }

    private function setPatternExceptions()
    {
        $parameters = $this->data;
        $this->validateData($parameters);

        $mobile = $this->numbers;
        $this->validateMobile($mobile);

        $templateId = $this->templateId;
        $this->validateTemplateId($templateId);

        $checkParameter = (gettype($parameters[0]) != 'array') ? $parameters[0] : $parameters;
        $this->validateParameterStructure($checkParameter);

        return compact('parameters', 'mobile', 'templateId');
    }

    private function validateData($parameters)
    {
        if (is_null($parameters))
            throw new \Exception('The data must be set');
        if (count($parameters) < 1)
            throw new \Exception('The data must have at least one item');
    }

    private function validateMobile($mobile)
    {
        if (empty($mobile))
            throw new \Exception('The mobile number must be set');
        if (count($mobile) > 1)
            throw new \Exception('Only one number is allowed for OTP');
    }

    private function validateTemplateId($templateId)
    {
        if (is_null($templateId))
            throw new \Exception('The templateId must be set');
    }

    private function validateParameterStructure($param)
    {
        if (!isset($param['name']))
            throw new \Exception('The `name` parameter not defined in data');
        if (!isset($param['value']))
            throw new \Exception('The `value` parameter not defined in data');
    }

    private function setOTP(&$inputs)
    {
        if (is_null($this->pattern_code)) {
            if (is_null($inputs['parameters'][0]['value'])) {
                $inputs['parameters'][0]['value'] = rand(100000, 999999);
            }
        } else {
            $inputs['parameters'][0]['value'] = $this->pattern_code;
        }
    }

    private function setMessageExceptions()
    {
        $numbers = $this->numbers;
        if (empty($numbers))
            throw new \Exception('The numbers of mobiles must be set');

        if (empty($this->from))
            throw new \Exception('The lineNumber must be set in config (set it in `from` key)');

        return compact('numbers');
    }

    private function post($url, $data)
    {
        $post_data = http_build_query($data);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_HTTPHEADER, ['content-type: application/x-www-form-urlencoded']);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, $post_data);
        return curl_exec($handle);
    }
}
