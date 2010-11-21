<?php

/**
 * Класс для работы с сервисом LittleSMS.ru
 *
 * Функции:
 *  - отправка SMS
 *  - проверка статуса доставки сообщений
 *  - запрос баланса
 *
 * @author Рустам Миниахметов <pycmam@gmail.com>
 */
class LittleSMS
{
    const REQUEST_SUCCESS = 'success';
    const REQUEST_ERROR = 'error';

    protected
        $user       = null,
        $key        = null,
        $testMode   = false,
        $url        = 'littlesms.ru/api',
        $useSSL     = false,
        $response   = null;

    /**
     * Конструктор
     *
     * @param string $user
     * @param string $key
     * @param integer $testMode
     */
    public function __construct($user, $key, $useSSL = false, $testMode = false)
    {
        $this->user = $user;
        $this->key = $key;
        $this->useSSL = $useSSL;
        $this->testMode = $testMode;
    }

    /**
     * Отправить SMS
     *
     * @param string|array $recipients
     * @param string $message
     * @param string $sender
     * @param boolean $flash
     *
     * @return boolean|integer
     */
    public function sendSMS($recipients, $message, $sender = null)
    {
        $response = $this->makeRequest('send', array(
            'recipients'    => is_array($recipients) ? join(',', $recipients) : $recipients,
            'message'       => $message,
            'sender'        => $sender,
            'test'          => (int) $this->testMode,
        ));

        return $response['status'] == self::REQUEST_SUCCESS;
    }

    /**
     * Проверить статус доставки сообщений
     *
     * @param string|array $messagesId
     *
     * @return boolean|array
     */
    public function checkStatus($messagesId)
    {
        if (! is_array($messagesId)) {
            $messagesId = array($messagesId);
        }

        $response = $this->makeRequest('status', array(
            'messages_id' => join(',', $messagesId),
        ));

        return $response['status'] == self::REQUEST_SUCCESS ? $response['messages'] : false;
    }

    /**
     * Запросить баланс
     *
     * @return boolean|float
     */
    public function getBalance()
    {
        $response = $this->makeRequest('balance');

        return $response['status'] == self::REQUEST_SUCCESS ? (float) $response['balance'] : false;
    }

    /**
     * Отправить запрос
     *
     * @param string $function
     * @param array $params
     *
     * @return stdClass
     */
    protected function makeRequest($function, array $params = array())
    {
        $params = array_merge(array('user' => $this->user), $params);
        $sign = $this->generateSign($params);

        $url = ($this->useSSL ? 'https://' : 'http://') . $this->url .'/'. $function;
        $post = http_build_query(array_merge($params, array('sign' => $sign)), '', '&');

        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            if ($this->useSSL) {
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            }
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

            $response = curl_exec($ch);
            curl_close($ch);
        } else {
            $context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $post,
                    'timeout' => 10,
                ),
            ));
            $response = file_get_contents($url, false, $context);
        }

        return $this->response = json_decode($response, true);
    }

    /**
     * Возвращает ответ сервера последнего запроса
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->response;
    }


    /**
     * Установить адрес шлюза
     *
     * @param string $url
     * @return void
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }


    /**
     * Получить адрес сервера
     *
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
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