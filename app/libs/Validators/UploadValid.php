<?php
namespace App\Libs\Validators;

use Phalcon\Validation\Validator,
    Phalcon\Validation\ValidatorInterface,
    Phalcon\Validation\Message;

class UploadValid extends Validator implements ValidatorInterface
{
    public function validate(\Phalcon\Validation $validator, $attribute)
    {
        $value = $validator->getValue($attribute);

        if( isset($value['error'])
        AND isset($value['name'])
        AND isset($value['type'])
        AND isset($value['tmp_name'])
        AND isset($value['size'])) {
            return true;
        }

        $validator->appendMessage(new Message('Некорректный файл.', $attribute, 'upload_valid'));
        return false;
    }

}