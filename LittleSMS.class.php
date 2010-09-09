<?php

/**
 * Класс для работы с сервисом LittleSMS.ru
 *
 * Функции:
 *  - отправка SMS
 *  - запрос баланса
 *
 * @author Рустам Миниахметов <pycmam@gmail.com>
 */
class LittleSMS
{
    protected
        $user = null,
        $key  = null,
        $testMode = 0,
        $url = 'http://littlesms.ru/api/';

    /**
     * Конструктор
     *
     * @param string $user
     * @param string $key
     * @param integer $testMode
     */
    public function __construct($user, $key, $testMode = 0)
    {
        $this->user = $user;
        $this->key = $key;
        $this->testMode = $testMode;
    }

    /**
     * Отправить SMS
     *
     * @param string|array $recipients
     * @param string $message
     * @return boolean
     */
    public function sendSMS($recipients, $message)
    {
        $response = $this->makeRequest('send', array(
            'recipients' => is_array($recipients) ? join(', ', $recipients) : $recipients,
            'message' => $message,
            'test' => $this->testMode,
        ));

        return $response->status == 'success';
    }

    /**
     * Запросить баланс
     * @return boolean|float
     */
    public function getBalance()
    {
        $response = $this->makeRequest('balance');

        return $response->status == 'success' ? (float) $response->balance : false;
    }

    /**
     * Отправить запрос
     *
     * @param string $function
     * @param array $params
     * @return stdClass
     */
    protected function makeRequest($function, array $params = array())
    {
        $params = array_merge(array('user' => $this->user), $params);
        $sign = $this->generateSign($params);

        $ch = curl_init($this->url . $function);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array_merge($params, array('sign' => $sign))));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        return json_decode(curl_exec($ch));
    }

    /**
     * Сгенерировать подпись
     *
     * @param array $params
     * @return string
     */
    protected function generateSign(array $params)
    {
        return md5(sha1(join('', $params) . $this->key));
    }
}
