<?php

namespace App\Libs;

use Phalcon\DI;

class TelegramNotifier {
    const URL_PREFIX = "https://api.telegram.org/bot";

    private $recipients = [];

    public static function sendMessage($chat_id, $text) {
        $query_string = http_build_query(['chat_id' => $chat_id, 'text' => $text]);
        file_get_contents(self::URL_PREFIX . DI::getDefault()->getShared('config_server')->api->telegram->api_key . "/sendMessage?" . $query_string);
    }

    public function notify($text) {
        foreach ($this->recipients as $recipient) {
            $this->sendMessage($recipient, $text);
        }
    }

    public function addRecipient($chat_id) {
        if (!in_array($chat_id, $this->recipients)) {
            $this->recipients[] = $chat_id;
        }
    }

    public function removeRecipient($chat_id) {
        if (in_array($chat_id, $this->recipients)) {
            unset($this->recipients[array_search($chat_id, $this->recipients)]);
        }
    }

    public function getMe() {
        $response = file_get_contents(self::URL_PREFIX . TELEGRAM_API_KEY . "/getMe");
        return json_decode($response);
    }
}