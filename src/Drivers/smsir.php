<?php


namespace Jaby\Sms\Drivers;


use Jaby\Sms\SmsInterface;

class smsir implements SmsInterface
{
    protected $drive = 'smsir';

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

    protected $templateId;

    /**
     * farazsms constructor.
     */
    public function __construct()
    {
        $this->username = config('sms.drivers.' . $this->drive . '.username');
        $this->password = config('sms.drivers.' . $this->drive . '.password');
        $this->from = config('sms.drivers.' . $this->drive . '.from');
        $this->url = config('sms.drivers.' . $this->drive . '.urlPattern');
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
     * @param int|null $templateId
     * @return $this|mixed
     */
    public function templateId($templateId = null)
    {
        $this->templateId = $templateId;

        return $this;
    }

    /**
     * @return bool|mixed|string
     */
    public function sendPattern()
    {
        $inputs = $this->setPatternExceptions();
        $this->setOTP($inputs);

        $url = $this->url;
        $body = [
            "mobile"     => $inputs['mobile'][0],
            "templateId" => $inputs['templateId'],
            "parameters" => $inputs['parameters']
        ];

        $handler = curl_init($url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $body);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        return curl_exec($handler);
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
        if (count($parameters) > 1)
            throw new \Exception('The data must have just one OTP code');
    }

    /**
     * @param $mobile
     * @return void
     * @throws \Exception
     */
    private function validateMobile($mobile)
    {
        if (empty($mobile))
            throw new \Exception('The mobile number must be set');
        if (count($mobile) > 1)
            throw new \Exception('The OTP code must send to just one mobile number');
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

    /**
     * @param $inputs
     */
    private function setOTP(&$inputs)
    {
        $pattern_code = $this->pattern_code;
        if (is_null($pattern_code)) {
            if (is_null($inputs['parameters'][0]['value'])) {
                $inputs['parameters'][0]['value'] = rand(100000, 999999);
            }
        } else
            $inputs['parameters'][0]['value'] = $pattern_code;

    }

    /**
     * @param $text
     * @return mixed
     */
    public function message($text)
    {
        $inputs = $this->setMessageExceptions();

        $param = array
        (
            'lineNumber'  => $this->from,
            'MessageText' => $text,
            'Mobiles'     => json_encode($inputs['numbers']),
        );

        $handler = curl_init($this->url);
        curl_setopt($handler, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($handler, CURLOPT_POSTFIELDS, $param);
        curl_setopt($handler, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handler, CURLOPT_HTTPHEADER, array(
            "X-API-KEY: {$inputs['apiKey']}",
        ));
        $response = curl_exec($handler);
        $response2 = json_decode($response);

        return $response2;
    }

    private function setMessageExceptions()
    {
        $apiKey = config('sms.drivers.' . $this->drive . '.apiKey');
        if ($apiKey == '')
            throw new \Exception('The apiKey of SMS.ir muse be set in config');

        $this->url = config('sms.drivers.' . $this->drive . '.urlNormal');
        if ($this->url == '')
            throw new \Exception('The url of SMS.ir muse be set in config');

        $numbers = $this->numbers;
        if (count($numbers) < 1)
            throw new \Exception('The numbers of mobiles must be set');

        if ($this->from == '')
            throw new \Exception('The lineNumber of SMS.ir muse be set in config (set it in `from` key)');

        return compact('apiKey', 'numbers');
    }
}
