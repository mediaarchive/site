<?php
namespace App\Modules\Site\Controllers;

use Yandex\Disk\DiskClient;

class IndexController extends ControllerBase{
    public function initialize(){
        parent::initialize();
        if($this->request->has('livereload'))
            $this->assets->addJs('//'.$_SERVER['HTTP_HOST'].':35729/livereload.js', false);
    }
    
    public function indexAction(){
        if($this->request->isPost()) {
            $name = $this->request->getPost('name');

            $full_path = $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $name . '/';
echo $this->config_server->api->yandex_disk->ya_token;
            $disk = new DiskClient($this->config_server->api->yandex_disk->ya_token);
            $disk->setServiceScheme(DiskClient::HTTPS_SCHEME);

            $dirs_create_if_not_exists = array(
                $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/',
                $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/' . date('m') . '/',
                $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/',
                $full_path
            );

            $dir_content = $disk->directoryContents($this->config_server->api->yandex_disk->base_dir);
var_dump($dir_content);
            foreach($dirs_create_if_not_exists as $key=>$dir){
                $need_create = true;

                foreach($dir_content as $dir_existing){
                    var_dump(urldecode($dir_existing['href'])=== $dir);
                    var_dump(urldecode($dir_existing['href']));
                    var_dump($dir);
                    if($dir_existing['resourceType'] === 'dir' && urldecode($dir_existing['href']) === $dir)
                        $need_create = false;
                }
//                exit;
//                var_dump()
                if($need_create) { // если надо создавать директорию
                    var_dump('creation');
                    var_dump($dir);

                    var_dump($disk->createDirectory($dir)); // создаем
                }

                if($key !== count($dirs_create_if_not_exists) - 1) // если не последняя
                    $dir_content = $disk->directoryContents($dir); // получаем список файлов в директории
            }

            echo 1;
            exit;
        }
    }
}