<?php
$router = new Phalcon\Mvc\Router(false);

$router->setDefaultModule("Site");

$router->notFound(array(
    //"namespace" => 'App\Modules\Site\Controllers',
    "module" => "Site",
    "controller" => "error",
    "action" => "notFound"
));

$router->add('/:controller/:action/:int{params}', array(
    'module' => 'Site',
    'controller' => 1,
    'action' => 2,
    'id' => 3,
    'params' => 4
))->setName('site-action-extra-params');

$router->add('/:controller/:action/:int', array(
    'module' => 'Site',
    'controller' => 1,
    'action' => 2,
    'id' => 3
))->setName('site-action-extra');

$router->add('/:controller/:action', array(
    'module' => 'Site',
    'controller' => 1,
    'action' => 2
))->setName('site-action');

$router->add('/:controller', array(
    'module' => 'Site',
    'controller' => 1,
    'action' => 'index'
))->setName('site-controller');

$router->add('/event/:params', array(
    'module' => 'Site',
    'controller' => 'index',
    'action' => 'event',
    'temp_name' => 1
))->setName('event');


$router->add("/", array(
    //'namespace'=>'App\Modules\Site\Controllers',
    'module' => 'Site',
    'controller' => 'index',
    'action' => 'index'
))->setName('main');