<?php

namespace App\Libs\Validators;

class Uniqueness extends \Phalcon\Validation\Validator implements \Phalcon\Validation\ValidatorInterface
{

    public function validate(\Phalcon\Validation $validation, $field)
    {
        $value = $validation->getValue($field);
        $model = $this->getOption("model");
        $attribute = $this->getOption("attribute");

        if (empty($model)) {
            throw new \Phalcon\Validation\Exception("Model must be set");
        }

        if (empty($attribute)) {
            $attribute = $field;
        }

        $number = $model::count(array($attribute . "=:value:", "bind" => array("value" => $value)));

        if ($number) {
            $label = $this->getOption("label");

            if (empty($label)) {
                $label = $validation->getLabel($field);

                if (empty($label)) {
                    $label = $field;
                }
            }

            $message = $this->getOption("message");
            $replacePairs = array(":field" => $label);

            if (empty($message)) {
                $message = $validation->getDefaultMessage("Uniqueness");
            }

            $validation->appendMessage(new \Phalcon\Validation\Message(strtr($message, $replacePairs), $field, "Uniqueness"));
            return false;
        }
        return true;
    }

}