<?php
namespace App\Modules\Site\Controllers;

use App\Libs\Archive;

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

            $disk = Archive::disk();

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

            $temp = Archive::generate_temp_data($name, $full_path);
            $temp_dir_name = Archive::get_temp_dir_from_temp_data($temp);

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
            }

            return $this->response->redirect(array('for'=>'event', 'temp_data' => $temp));
        }
    }

    public function eventAction(){
        $temp_name = str_replace('/', '', $this->dispatcher->getParam('temp_data'));

        if(!Archive::check_temp_dir($temp_name))
            return $this->response->redirect(array('for'=>'main'));

        $temp_data = Archive::get_temp_data($temp_name);

        $this->view->name = $temp_data['name'];

        $this->assets
            ->addJs('http://static.clienddev.ru/handlebars/3.0.3/handlebars.min.js', false)
            ->addJs('libs/jquery-filedrop/jquery.filedrop.js')
            ->addJs('js/site/index/event.js');
    }

    public function uploadAction(){
        if(!$this->request->isPost() OR !$this->request->has('temp'))
            return $this->response->redirect(array('for'=>'main'));
        else{


            $post = $this->request->getPost();

            $item = new TkaniItems();
            $item->collection_id = $collection->id;

            if($this->view->role !== 'superadmin') // номер материала изменяет только суперадмин
                unset($post['material_number']);

            if($this->request->getPost('if_active') == 'on' OR $this->request->getPost('if_active') == 1)
                $item->if_active = true;
            else
                $item->if_active = false;

            unset($post['if_active']);
            unset($post['image_small_src']);
            unset($post['image_full_src']);

            if ($this->request->hasFiles()) {
                $files = $this->request->getUploadedFiles();

                foreach($files as $file){
                    switch($file->getKey()){
                        case 'image_small_src':
                            $image_small_src_obj = $item->image_small_src_prepare($file);
                            if($image_small_src_obj === false)
                                return false;
                            break;
                        case 'image_full_src':
                            $image_full_src_obj = $item->image_full_src_prepare($file);
                            if($image_full_src_obj === false)
                                return false;
                            break;
                    }
                }
            }

            if ($item->create($post) === true) {
                if($image_full_src_obj){
                    if(!$item->image_full_src_save($image_full_src_obj)){
                        $item->image_cleanup(pathinfo($image_full_src_obj->getName(), PATHINFO_EXTENSION));
                        return false;
                    }
                }

                if($image_small_src_obj){
                    if(!$item->image_small_src_save($image_small_src_obj))
                        return false;
                }
                else{ // если маленькая картинка не была указан, генерируем автоматически
                    $item->image_generate_small_from_full();
                    $item->save();
                }

                if($this->request->isAjax())
                    return $this->response->setJsonContent(array(
                        'status'=>'ok',
                        'id' => $item->id
                    ));
                else{
                    $this->flashSession->success('Ткань успешно добавлена');
                    return $this->response->redirect(array('for'=>'admin-part-controller', 'module'=>'Admin', 'namespace'=>'provider', 'controller'=>'tkani'));
                }
            }
            else {
                if($this->request->isAjax()){
                    $errors = array();

                    foreach($item->getMessages() as $mes)
                        $errors[] = $mes->getMessage();

                    return $this->response->setJsonContent(array('error_code'=>4, 'error_text'=>'update data error', 'flash_sessions_errors'=>$errors));
                }
                else{
                    foreach($item->getMessages() as $mes){
                        $this->flashSession->error("Ошибка при создании ткани: " . $mes);
                    }
                }
            }
        }
    }
}