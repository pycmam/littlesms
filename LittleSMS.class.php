<?php

/**
 * Класс для работы с сервисом LittleSMS.ru
 *
 * Описание функций и параметров: http://littlesms.ru/doc
 *
 * @author Рустам Миниахметов <pycmam@gmail.com>
 */
class LittleSMS
{
  const
    REQUEST_SUCCESS = 'success',
    REQUEST_ERROR = 'error',

    TYPE_DEFAULT = 0,
    TYPE_FLASH = 1,
    TYPE_PING = 2;

  protected
    $user       = null,
    $key        = null,
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
  public function __construct($user = null, $key = null, $useSSL = false)
  {
      $this->user = $user;
      $this->key = $key;
      $this->useSSL = $useSSL;
  }

  /**
   * Стоп-лист: добавить номера
   *
   * @param string|array $phones
   * @param string $description
   * @return int|bool
   */
  public function blacklistAppend($phones, $description = null)
  {
    $this->makeRequest('blacklist/append', array(
      'phones' => $phones,
      'description' => $description,
    ));
    return $this->getIfSuccess('id');
  }

  /**
   * Стоп-лист: удалить номера
   *
   * @param string|array $phones
   * @return int|bool
   */
  public function blacklistDelete($phones)
  {
    $this->makeRequest('blacklist/delete', array('phones' => $phones));
    return $this->getIfSuccess('count');
  }

  /**
   * Стоп-лист: список номеров
   *
   * @param array $params
   * @return array|bool
   */
  public function blacklistList($params = array())
  {
    $this->makeRequest('blacklist/list', $params);
    return $this->getIfSuccess('list');
  }

  /**
   * Добавить имя отправителя
   *
   * @param array $params
   * @return bool|null
   */
  public function senderCreate(array $params)
  {
    $this->makeRequest('sender/create', $params);
    return $this->getIfSuccess();
  }

  /**
   * Подтвердить имя отправителя кодом из СМС
   *
   * @param $id
   * @param $code
   * @return bool|null
   */
  public function senderConfirm($id, $code)
  {
    $this->makeRequest('sender/confirm', array(
      'id' => $id,
      'code' => $code,
    ));
    return $this->getIfSuccess('id');
  }

  /**
   * Использовать имя отправителя по-умолчанию
   *
   * @param $id
   * @return bool|null
   */
  public function senderDefault($id)
  {
    $this->makeRequest('sender/default', array(
      'id' => $id,
    ));
    return $this->getIfSuccess('id');
  }

  /**
   * Удалить имя отправителя
   *
   * @param $id
   * @return bool|null
   */
  public function senderDelete($id)
  {
    $this->makeRequest('sender/delete', array(
      'id' => $id,
    ));
    return $this->getIfSuccess('count');
  }

  /**
   * Список имен отправителей
   *
   * @param array $params
   * @return bool|null
   */
  public function senderList($params = array())
  {
    $this->makeRequest('sender/list', $params);
    return $this->getIfSuccess('list');
  }

  /**
   * Список доступных платежных систем
   *
   * @return bool|null
   */
  public function paymentSystems()
  {
    $this->makeRequest('payment/systems');
    return $this->getIfSuccess();
  }

  /**
   * Создать счет на оплату
   *
   * @param array $params
   * @return bool|null
   */
  public function paymentCreate(array $params)
  {
    $this->makeRequest('payment/create', $params);
    return $this->getIfSuccess();
  }

  /**
   * Получить URL платежного шлюза по счету
   *
   * @param $id
   * @return bool|null
   */
  public function paymentUrl($id)
  {
    $this->makeRequest('payment/url', array('id' => $id));
    return $this->getIfSuccess('url');
  }

  /**
   * Удалить неоплаченный счет
   *
   * @param $id
   * @return bool|null
   */
  public function paymentDelete($id)
  {
    $this->makeRequest('payment/delete', array('id' => $id));
    return $this->getIfSuccess('count');
  }

  /**
   * Список платежей
   *
   * @param array $params
   * @return bool|null
   */
  public function paymentList($params = array())
  {
    $this->makeRequest('payment/list', $params);
    return $this->getIfSuccess('list');
  }

  /**
   * Предварительный запрос регистрации
   *
   * @param array $params
   * @return bool|null
   */
  public function signupRequest(array $params)
  {
    $this->makeAnonymousRequest('signup/request', $params);
    return $this->getIfSuccess();
  }

  /**
   * Отправка кода капчи
   *
   * @param $key
   * @param $code
   * @return bool|null
   */
  public function signupConfirm($key, $code)
  {
    $this->makeAnonymousRequest('signup/confirm', array('key' => $key, 'code' => $code));
    return $this->getIfSuccess();
  }

  /**
   * Завершение регистрации отправкой кода из СМС
   *
   * @param $key
   * @param $code
   * @return bool|null
   */
  public function signupFinish($key, $code)
  {
    $this->makeAnonymousRequest('signup/finish', array('key' => $key, 'code' => $code));
    return $this->getIfSuccess();
  }


  /**
   * Рассылки: список рассылок
   *
   * @param array $params
   *
   * @return bool|array
   */
  public function bulkList($params = array())
  {
    $this->makeRequest('bulk/list', $params);
    return $this->getIfSuccess('bulks');
  }

  /**
   * Рассылки: создать
   *
   * @param array $params
   *
   * @return boolean|integer
   */
  public function bulkCreate($params)
  {
    $this->makeRequest('bulk/create', $params);
    return $this->getIfSuccess('id');
  }

  /**
   * Рассылки: обновить
   *
   * @param integer $id
   * @param array $params
   *
   * @return boolean|integer
   */
  public function bulkUpdate($id, array $params)
  {
    $this->makeRequest('bulk/update',  array_merge($params, array('id' => $id)));
    return $this->getIfSuccess('id');
  }

  /**
   * Рассылки: удалить
   *
   * @param array|integer $id
   * @param array $params
   *
   * @return boolean|integer
   */
  public function bulkDelete($id)
  {
    $this->makeRequest('bulk/delete', array('id' => $id,));
    return $this->getIfSuccess('count');
  }

  /**
   * Рассылки: отправить
   *
   * @param integer $id
   *
   * @return boolean|integer
   */
  public function bulkSend($id)
  {
    $this->makeRequest('bulk/send', array('id' => $id));
    return $this->getIfSuccess('id');
  }

  /**
   * Рассылки: остановить
   *
   * @param integer $historyId
   *
   * @return boolean|integer
   */
  public function bulkStop($id)
  {
    $this->makeRequest('bulk/stop', array('id' => $id));
    return $this->getIfSuccess('id');
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
  public function messageSend($recipients, $message, $sender = null, $otherParams = array())
  {
    $params = array_merge(array(
      'recipients'    => $recipients,
      'message'       => $message,
      'sender'        => $sender,
      'type'          => self::TYPE_DEFAULT,
    ), $otherParams);

    $this->makeRequest('message/send', $params);
    return $this->getIfSuccess('messages_id');
  }

  /**
   * Проверить статус доставки сообщений
   *
   * @param string|array $messagesId
   *
   * @return boolean|array
   */
  public function messageStatus($id)
  {
    $this->makeRequest('message/status', array('messages_id' => $id));
    return $this->getIfSuccess('messages');
  }

  /**
   * Запрос стоимости сообщения
   *
   * @param string|array $recipients
   * @param string $message
   *
   * @return boolean|decimal
   */
  public function messagePrice($recipients, $message)
  {
    $this->makeRequest('message/price', array(
      'recipients'    => $recipients,
      'message'       => $message,
    ));
    return $this->getIfSuccess('price');
  }

  /**
   * История сообщений
   *
   * @param array $params
   *
   * @return boolean|array
   * @deprecated
   */
  public function messageHistory($params = array())
  {
    return $this->messageList($params);
  }

  /**
   * История сообщений
   *
   * @param array $params
   *
   * @return boolean|array
   */
  public function messageList($params = array())
  {
    $this->makeRequest('message/list', $params);
    return $this->getIfSuccess('list');
  }


  /**
   * Запросить баланс
   *
   * @return boolean|float
   */
  public function userBalance()
  {
    $this->makeRequest('user/balance');
    return $this->getIfSuccess('balance');
  }

  /**
   * Контакты: список контактов
   *
   * @param array $params
   *
   * @return boolean|array
   */
  public function contactList($params = array())
  {
    $this->makeRequest('contact/list', $params);
    return $this->getIfSuccess('contacts');
  }

  /**
   * Контакты: создать
   *
   * @param array $params
   *
   * @return boolean|integer
   */
  public function contactCreate(array $params) // $phone, $name = null, $description = null, $tags = array())
  {
    $this->makeRequest('contact/create', $params);
    return $this->getIfSuccess('id');
  }

  /**
   * Контакты: обновить
   *
   * @param integer $id
   * @param array $params
   *
   * @return boolean|integer
   */
  public function contactUpdate($id, array $params)
  {
    $this->makeRequest('contact/update', array_merge($params, array('id' => $id)));
    return $this->getIfSuccess('id');
  }

  /**
   * Контакты: удалить
   *
   * @param integer $id
   *
   * @return boolean|integer
   */
  public function contactDelete($id)
  {
    $this->makeRequest('contact/delete', array('id' => $id));
    return $this->getIfSuccess('count');
  }

  /**
   * Теги: список тегов
   *
   * @param array $params
   *
   * @return boolean|array
   */
  public function tagList($params = array())
  {
    $this->makeRequest('tag/list', $params);
    return $this->getIfSuccess('tags');
  }

  /**
   * Теги: создать
   *
   * @param array $params
   *
   * @return boolean|integer
   */
  public function tagCreate(array $params)
  {
    $this->makeRequest('tag/create', $params);
    return $this->getIfSuccess('id');
  }

  /**
   * Теги: обновить
   *
   * @param integer $id
   * @param array $params
   *
   * @return boolean|integer
   */
  public function tagUpdate($id, array $params)
  {
    $this->makeRequest('tag/update', array_merge($params, array('id' => $id)));
    return $this->getIfSuccess('id');
  }

  /**
   * Теги: удалить
   *
   * @param integer $id
   *
   * @return boolean|integer
   */
  public function tagDelete($id)
  {
    $this->makeRequest('tag/delete', array('id' => $id));
    return $this->getIfSuccess('count');
  }

  /**
   * Задания: список заданий
   *
   * @param array $params
   *
   * @return boolean|array
   */
  public function taskList($params = array())
  {
    $this->makeRequest('task/list', $params);
    return $this->getIfSuccess('tasks');
  }

  /**
   * Задания: создать
   *
   * @param array $params
   *
   * @return boolean|integer
   */
  public function taskCreate(array $params)
  {
    $this->makeRequest('task/create', $params);
    return $this->getIfSuccess('id');
  }

  /**
   * Задания: обновить
   *
   * @param integer $id
   * @param array $params
   *
   * @return boolean|integer
   */
  public function taskUpdate($id, array $params)
  {
    $this->makeRequest('task/update',  array_merge($params, array('id' => $id)));
    return $this->getIfSuccess('id');
  }

  /**
   * Задания: удалить
   *
   * @param integer $id
   *
   * @return boolean|integer
   */
  public function taskDelete($id)
  {
    $this->makeRequest('task/delete', array('id' => $id));
    return $this->getIfSuccess('count');
  }


  /**
   * Отправить запрос
   *
   * @param string $function
   * @param array $params
   * @return array
   */
  public function makeRequest($function, array $params = array())
  {
    $params = $this->joinArrayValues($params);
    $params = array_merge($params, array('user' => $this->user, 'apikey' => $this->key));
    return $this->response = $this->httpRequest($function, $params);
  }

  /**
   * Отправить анонимный запрос
   *
   * @param string $function
   * @param array $params
   * @return array
   */
  public function makeAnonymousRequest($function, array $params)
  {
    $params = $this->joinArrayValues($params);
    return $this->response = $this->httpRequest($function, $params);
  }

  /**
   * @param string $function
   * @param array $params
   * @return mixed
   */
  protected function httpRequest($function, array $params)
  {
    $url = ($this->useSSL ? 'https://' : 'http://') . $this->url .'/'. $function;
    $post = http_build_query($params, '', '&');

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
          'timeout' => 15,
        ),
      ));
      $response = file_get_contents($url, false, $context);
    }

    return json_decode($response, true);
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
   * @return bool|null
   */
  public function isSuccess()
  {
    return $this->response ? $this->response['status'] == self::REQUEST_SUCCESS : null;
  }

  public function getIfSuccess($param = null)
  {
    if ($this->isSuccess() && $this->response) {
      return $param ? $this->response[$param] : $this->response;
    }

    return false;
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

  protected function joinArrayValues($params)
  {
    $result = array();
    foreach ($params as $name => $value) {
      $result[$name] = is_array($value) ? join(',', $value) : $value;
    }
    return $result;
  }
}
