<?php
namespace App\Libs\Validators;

use App\Libs\Functions;
use Phalcon\Validation\Validator,
    Phalcon\Validation\ValidatorInterface,
    Phalcon\Validation\Message;

class UploadSize extends Validator implements ValidatorInterface
{
    public function validate(\Phalcon\Validation $validator, $attribute)
    {
        $value = $validator->getValue($attribute);
        $size = $this->getOption('max');
        $max = Functions::bytes($size);
        if($max != false) {
            if($value['size'] > $max) {
                $validator->appendMessage(new Message('Превышен допустимый размер файла. Максимальный разрешённый размер - '.$size.'.', $attribute, 'upload_size'));
                return false;
            }
            return true;
        }
        else {
            $validator->appendMessage(new Message('Неверный формат размера файла', $attribute, 'improperly_formatted_size'));
            return false;
        }
    }
}