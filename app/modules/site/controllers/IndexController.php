<?php
namespace App\Modules\Site\Controllers;

use Phalcon\Validation;
use Phalcon\Mvc\View;

use App\Libs\Archive;

use App\Libs\Validators\UploadImage;
use App\Libs\Validators\UploadSize;
use App\Libs\Validators\UploadType;
use App\Libs\Validators\UploadValid;

class IndexController extends ControllerBase
{
    public function initialize()
    {
        parent::initialize();
        if ($this->request->has('livereload')) {
            $this->assets->addJs('//' . $_SERVER['HTTP_HOST'] . ':35729/livereload.js', false);
        }
    }

    public function indexAction()
    {
        if ($this->request->isPost()) {
            if (!$this->request->has('name')) {
                return $this->flashSession->error("Пожалуйста, укажите название новости");
            }
            if (!$this->request->has('author_name')) {
                return $this->flashSession->error("Пожалуйста, укажите Ваше имя");
            }

            $name = $this->request->getPost('name');
            $author_name = $this->request->getPost('author_name');
            $text = $this->request->getPost('text');

            $disk = Archive::disk();

            $full_path = $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/' . $name;

            $dirs_create_if_not_exists = array(
                $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/',
                $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/' . date('m') . '/',
                $this->config_server->api->yandex_disk->base_dir . '/' . date('Y') . '/' . date('m') . '/' . date('d') . '/',
                $full_path
            );

            $dir_content = $disk->directoryContents($this->config_server->api->yandex_disk->base_dir);

            foreach ($dirs_create_if_not_exists as $key => $dir) {
                $need_create = true;

                foreach ($dir_content as $dir_existing) {
                    if ($dir_existing['resourceType'] === 'dir' && urldecode($dir_existing['href']) === $dir) {
                        $need_create = false;
                    }
                }

                if ($need_create)  // если надо создавать директорию
                {
                    try {
                        $disk->createDirectory($dir . '/');
                    }
                    catch(\Exception $e){
                        if($dir == $full_path){
                            $extra = ' ('.date('H.i.s').')';
                            $full_path .= $extra;

                            $disk->createDirectory($full_path);
                        }
                    }
                } // создаем

                if ($key !== count($dirs_create_if_not_exists) - 1) // если не последняя
                {
                    $dir_content = $disk->directoryContents($dir);
                } // получаем список файлов в директории
            }


            $full_path .= '/';

            $disk->createDirectory($full_path . 'фото/');

            $temp_dir_name = Archive::get_temp_dir_from_temp_data(Archive::get_temp_data($temp));

            if (!file_exists('temp/')) {
                mkdir('temp/');
            }

            $temp_dir = 'temp/' . $temp_dir_name . '/';
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

            if ($author_name !== '') {
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

            $this->view->name = $name;
            $this->view->full_path = $full_path;
            $this->view->temp_dir_name = $temp_dir_name;

            $this->view->pick('index/event');

            $this->assets
                ->addJs('http://static.clienddev.ru/handlebars/3.0.3/handlebars.min.js', false)
                ->addJs('libs/jquery-filedrop/jquery.filedrop.js')
                ->addJs('js/site/index/event.js');
        }
    }

    public function uploadAction()
    {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);

        if (!$this->request->isPost() OR !$this->request->hasPost('full_path') OR !$this->request->hasPost('temp_dir_name') OR !$this->request->hasFiles()){
            $this->response->setStatusCode(400, 'Bad request');
            return;
        }
        else{
            $temp_dir = 'temp/' . $this->request->getPost('temp_dir_name') . '/';
            $full_path = urldecode($this->request->getPost('full_path'));

            $files = $this->request->getUploadedFiles();

            foreach($files as $file){
                $validation = new Validation();
                $validation->add('file', new UploadValid());
                $messages = $validation->validate($_FILES);

                if(count($messages)) {
                    $messages = array();

                    foreach ($validation->getMessages() as $message)
                        $messages[] = $message->getMessage();

                    $this->response->setStatusCode(400);
                    return $this->response->setJsonContent(array('error' => 'file', 'messages' => $messages));
                }

                $file_path = $temp_dir . md5($file->getName());

                $yadisk_dir = '';

                switch(strtolower($file->getExtension())){
                    case 'jpg':case 'jpeg':case 'png':case 'gif':
                            $yadisk_dir = 'фото/';
                        break;
                }

                $file->moveTo($file_path);

                $disk = Archive::disk();

                $dir_content = $disk->directoryContents($full_path . $yadisk_dir);

                $file_name = $file->getName();

                foreach($dir_content as $dir_file){
                    var_dump($file->getName());
                    var_dump($dir_file);
                    if($dir_file['resourceType'] == 'file' && $file->getName() == $dir_file['displayName']){
                        $file_name = basename($file->getName()) . " (".date('H:i:s').")." . $file->getExtension();
                    }
                }

                $disk->uploadFile(
                    $full_path . $yadisk_dir,
                    array(
                        'path' => $file_path,
                        'size' => filesize($file_path),
                        'name' => $file_name
                    )
                );

                unlink($file_path);
            }
        }
    }
}