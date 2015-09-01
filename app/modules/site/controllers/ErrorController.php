<?
namespace App\Modules\Site\Controllers;

class ErrorController extends ControllerBase {
    public function notFoundAction(){
        $this->response->setStatusCode(404, 'Not Found');
    }
}