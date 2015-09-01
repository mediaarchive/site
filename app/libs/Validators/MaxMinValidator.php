<?php
namespace App\Libs\Validators;

use Phalcon\Validation\Validator,
    Phalcon\Validation\ValidatorInterface,
    Phalcon\Validation\Message;


class MaxMinValidator extends Validator implements ValidatorInterface
{

    public function validate(\Phalcon\Validation $validator, $attribute)
    {
        $allowZero = $this->getOption('allowZero');

        $min = $this->getOption('min');
        $max = $this->getOption('max');
        $field = $this->getOption('field');

        $value = $validator->getValue($attribute);
        $len = mb_strlen($value, 'utf-8');

        if ($len > $max || $len < $min) {
            if(!empty($value)) {
                $validator->appendMessage(new Message(
                    "Длина поля \"{$field}\" должна быть от $min до $max",
                    $field,
                    "MaxMinValidator"
                ));
                return false;
            }
            elseif($allowZero) return true;
        }
        return true;
    }

}