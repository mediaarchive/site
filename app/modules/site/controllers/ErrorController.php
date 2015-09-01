<?
namespace App\Modules\Site\Controllers;

class ErrorController extends \Phalcon\Mvc\Controller {
    public function notFoundAction(){
        $this->response->setStatusCode(404, 'Not Found');
    }
}