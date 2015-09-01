<?php

namespace App\Models;

abstract class ModelBase extends \Phalcon\Mvc\Model {
    public $labels = array();

    public function getLabel($field) {
        if(isset($this->labels[$field]))
            return $this->labels[$field];
        else
            return $field;
    }

    public function setLabel($field, $label) {
        $this->labels[$field] = $label;
    }

    public function getMessages($filter = null){
        foreach (parent::getMessages() as $message) {
            switch ($message->getType()) {
                case 'Email':
                    $message->setMessage('Указан не действительный E-mail.');
                    break;
                case 'PresenceOf':
                    $message->setMessage('Поле "'.$this->getLabel($message->getField()).'" должно быть заполнено');
                    break;

            }
        }
        return parent::getMessages();
    }

    public function initialize(){
        //$this->useDynamicUpdate(true);
    }
}