<?php


namespace Jaby\Sms;


interface SmsInterface
{
    /**
     * @return mixed
     */
    public function send();

    /**
     * @param $text
     * @return mixed
     */
    public function text($text);

    /**
     * @return mixed
     */
    public function pattern();

    /**
     * @param array $data
     * @return mixed
     */
    public function data(array $data);

    /**
     * @param $from
     * @return mixed
     */
    public function from($from);

    /**
     * @param array $numbers
     * @return mixed
     */
    public function to(array $numbers);

    /**
     * @return mixed
     */
    public function sendPattern();

    /**
     * @param $text
     * @return mixed
     */
    public function message($text);
}
