<?php
namespace App\Libs\Validators\Mvc;

use Phalcon\Mvc\Model\Validator,
    Phalcon\Mvc\Model\ValidatorInterface;

class MaxMinValidator extends Validator implements ValidatorInterface
{

    public function validate(\Phalcon\Mvc\ModelInterface $model)
    {
        $field = $this->getOption('field');
        $allowZero = $this->getOption('allowZero');

        $min = $this->getOption('min');
        $max = $this->getOption('max');

        $value = $model->$field;
        $len = mb_strlen($value, 'utf-8');

        if ($len > $max || $len < $min) {
            if(!empty($value)) {
                $this->appendMessage(
                    "Длина поля \"{$model->getLabel($field)}\" должна быть от $min до $max",
                    $field,
                    "MaxMinValidator"
                );
                return false;
            }
            elseif($allowZero) return true;
        }
        return true;
    }

}