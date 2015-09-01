<?php
namespace App\Libs\Validators;

use Phalcon\Validation\Validator,
    Phalcon\Validation\ValidatorInterface,
    Phalcon\Validation\Message;

class UploadType extends Validator implements ValidatorInterface
{
    public function validate(\Phalcon\Validation $validator, $attribute)
    {
        $value = $validator->getValue($attribute);
        $allowed = $this->getOption('allowed');
        $ext = strtolower(pathinfo($value['name'], PATHINFO_EXTENSION));
        if(in_array($ext, $allowed)) {
            return true;
        }
        $validator->appendMessage(new Message('Запрещённый формат файла. Разрешённые форматы: '.implode(', ', $allowed).'.', $attribute, 'upload_type'));
        return false;
    }
}