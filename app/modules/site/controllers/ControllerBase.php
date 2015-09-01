<?php
namespace App\Modules\Site\Controllers;


class ControllerBase extends  \Phalcon\Mvc\Controller{
    public function initialize(){
        $this->assets
            ->addCss('http://yastatic.net/bootstrap/3.3.4/css/bootstrap.min.css', false)
            ->addJs('http://yastatic.net/jquery/2.1.4/jquery.min.js', false)
            ->addJs('http://yastatic.net/bootstrap/3.3.4/js/bootstrap.min.js', false);
    }
}