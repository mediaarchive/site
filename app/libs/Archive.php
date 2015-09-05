<?php

namespace App\Libs;

use Yandex\Disk\DiskClient;

class Archive
{
    private static $disk_instance = false;

    public static function disk(){
        if (self::$disk_instance === false) {
            self::$disk_instance = new DiskClient(\Phalcon\DI::getDefault()->getShared('config_server')->api->yandex_disk->ya_token);
            self::$disk_instance->setServiceScheme(DiskClient::HTTPS_SCHEME);
        }
        return self::$disk_instance;
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
//        var_dump($temp_data['name'] . $temp_data['full_path']);
        return Functions::genhash(20);
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

    public static function disk_if_exists($path){
        $last_index_of_slash = strrpos($path, '/');

        if($last_index_of_slash == strlen($path) - 1)
            $path = substr($path, 0, $last_index_of_slash);

        $last_index_of_slash = strrpos($path, '/');

        $dir = substr($path, 0, $last_index_of_slash);
        $filename = basename($path);

        $disk = self::disk();

        foreach($disk->directoryContents($dir) as $in_dir){
            if($in_dir['displayName'] == $filename)
                return true;
        }

        return false;
    }
}