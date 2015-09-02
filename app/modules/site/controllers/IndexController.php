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
            if(!$this->request->has('name'))
                return $this->flashSession->error("Пожалуйста, укажите название новости");
            if(!$this->request->has('author_name'))
                return $this->flashSession->error("Пожалуйста, укажите Ваше имя");

            $name = $this->request->getPost('name');
            $author_name = $this->request->getPost('author_name');
            $text = $this->request->getPost('text');

            $disk = new DiskClient($this->config_server->api->yandex_disk->ya_token);
            $disk->setServiceScheme(DiskClient::HTTPS_SCHEME);

            $full_path = $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $name . '/';

            $dirs_create_if_not_exists = array(
                $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/',
                $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/' . date('m') . '/',
                $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/',
                $full_path
            );

            $dir_content = $disk->directoryContents($this->config_server->api->yandex_disk->base_dir);

            foreach($dirs_create_if_not_exists as $key=>$dir){
                $need_create = true;

                foreach($dir_content as $dir_existing){
                    if($dir_existing['resourceType'] === 'dir' && urldecode($dir_existing['href']) === $dir)
                        $need_create = false;
                }

                if($need_create)  // если надо создавать директорию
                    $disk->createDirectory($dir); // создаем

                if($key !== count($dirs_create_if_not_exists) - 1) // если не последняя
                    $dir_content = $disk->directoryContents($dir); // получаем список файлов в директории
            }

            $temp = base64_encode(json_encode(array(
                'name' => $name,
                'full_path' => $full_path
            )));
            $temp_dir_name = md5($temp);

            if(!file_exists('temp/'))
                mkdir('temp/');

            $temp_dir = 'temp/' . $temp_dir_name . '/';

            if(!file_exists($temp_dir) && ($text !== '' OR $author_name !== '')) {
                mkdir($temp_dir);

                if ($text !== '') {
                    file_put_contents($temp_dir . '/info.txt', $text);

                    $disk->uploadFile(
                        $full_path,
                        array(
                            'path' => $temp_dir . '/info.txt',
                            'size' => filesize($temp_dir . '/info.txt'),
                            'name' => 'info.txt'
                        )
                    );

                    unlink($temp_dir . '/info.txt');
                }

                if($author_name !== ''){
                    file_put_contents($temp_dir . '/data.json', json_encode(array(
                        'author_name' => $author_name
                    )));

                    $disk->uploadFile(
                        $full_path,
                        array(
                            'path' => $temp_dir . '/data.json',
                            'size' => filesize($temp_dir . '/data.json'),
                            'name' => 'data.json'
                        )
                    );

                    unlink($temp_dir . '/data.json');
                }

                rmdir($temp_dir);
            }

            return $this->response->redirect(array('for'=>'event', 'temp_name' => $temp));
        }
    }
    public function eventAction(){

    }
}