<?php
namespace App\Modules\Site\Controllers;

class IndexController extends \Phalcon\Mvc\Controller {
    public function initialize(){
        if($this->request->has('livereload'))
            $this->assets->addJs('//'.$_SERVER['HTTP_HOST'].':35729/livereload.js', false);
    }
    
    public function indexAction(){}
}