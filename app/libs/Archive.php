<?php

namespace App\Libs;

use Yandex\Disk\DiskClient;

class Archive
{
    public static function disk(){
        $disk = new DiskClient(\Phalcon\DI::getDefault()->getShared('config_server')->api->yandex_disk->ya_token);
        $disk->setServiceScheme(DiskClient::HTTPS_SCHEME);
        return $disk;
    }

    public static function generate_temp_data($name, $full_path){
        return base64_encode(json_encode(array(
            'name' => $name,
            'full_path' => $full_path
        )));
    }

    public static function get_temp_data($temp_name){
        return json_decode(base64_decode($temp_name), true);
    }

    public static function get_temp_dir_from_temp_data($temp_data){
        return md5($temp_data);
    }

    public static function check_temp_dir($temp_name){
        if($temp_name == '' OR $temp_name == null)
            return false;

        $temp_data = Archive::get_temp_data($temp_name);

        $disk = self::disk();

        try {
            $dir_content = $disk->directoryContents($temp_data['full_path']);
        }
        catch(\Exception $e){}

        if(!is_array($dir_content) OR count($dir_content) === 0)
            return false;

        return true;
    }
}