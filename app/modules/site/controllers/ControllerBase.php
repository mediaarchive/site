<?php
namespace App\Modules\Site\Controllers;

use Phalcon\Mvc\View;

class ControllerBase extends  \Phalcon\Mvc\Controller{
    public function initialize(){
        if ($this->request->isAjax() == true) {
            $this->view->setRenderLevel(View::LEVEL_NO_RENDER);
        }

        $this->assets
            ->addCss('http://yastatic.net/bootstrap/3.3.4/css/bootstrap.min.css', false)
            ->addCss('styles/site/css/style.min.css')
            ->addCss('http://static.clienddev.ru/font-awesome/4.4.0/font-awesome.min.css', false)
            ->addJs('http://yastatic.net/jquery/2.1.4/jquery.min.js', false)
            ->addJs('http://yastatic.net/bootstrap/3.3.4/js/bootstrap.min.js', false);
    }
}