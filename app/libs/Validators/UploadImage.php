<?php
namespace App\Libs\Validators;

use Phalcon\Validation\Validator,
    Phalcon\Validation\ValidatorInterface,
    Phalcon\Validation\Message;

class UploadImage extends Validator implements ValidatorInterface
{
    public function validate(\Phalcon\Validation $validator, $attribute)
    {
        $value = $validator->getValue($attribute);
        if((bool) getimagesize($value['tmp_name']))
            return true;
        else {
            $validator->appendMessage(new Message('Файл не является изображением. Для загрузки разрешены только изображения.', $attribute, 'upload_image'));
            return false;
        }
    }
}